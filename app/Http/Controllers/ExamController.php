<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\CourseProgress;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboardService
    ) {}

    /**
     * Display live exam view
     */
    public function show(Exam $exam): Response
    {
        $user = Auth::user();
        $chapter = $exam->chapter;

        $questions = Question::where('chapter_id', $chapter?->id)
            ->inRandomOrder()
            ->get(['id', 'chapter_id', 'question_text', 'options', 'correct_option_index', 'explanation']);

        // Check if there is an existing Redis draft
        $draftAnswers = [];
        try {
            $rawDraft = Redis::get("exam_draft:{$user->id}:{$exam->id}");
            if ($rawDraft) {
                $draftAnswers = json_decode($rawDraft, true) ?: [];
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Exam/TakeExam', [
            'exam' => $exam,
            'chapter' => $chapter,
            'questions' => $questions,
            'draftAnswers' => $draftAnswers,
            'serverTime' => now()->timestamp * 1000,
        ]);
    }

    /**
     * Async Auto-save candidate draft answers into Redis
     */
    public function autoSave(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'answers' => 'required|array',
        ]);

        $userId = Auth::id();
        $examId = $request->exam_id;

        try {
            Redis::set("exam_draft:{$userId}:{$examId}", json_encode($request->answers));
            return response()->json(['status' => 'success', 'saved_at' => now()->toTimeString()]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'fallback', 'message' => 'Local state saved']);
        }
    }

    /**
     * Submit exam attempt, calculate score, apply negative marking, sudden death check
     */
    public function submit(Request $request, Exam $exam)
    {
        $request->validate([
            'answers' => 'array', // [question_id => selected_index]
        ]);

        $user = Auth::user();
        $answers = $request->input('answers', []);

        $questions = Question::where('chapter_id', $exam->chapter_id)->get();
        $correctCount = 0;
        $wrongCount = 0;

        $negativeMark = $exam->negative_mark_value ?? 0.25;

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => now()->subMinutes($exam->duration_minutes),
            'submitted_at' => now(),
            'is_completed' => true,
        ]);

        foreach ($questions as $q) {
            $selectedIndex = $answers[$q->id] ?? null;
            $isCorrect = false;

            if ($selectedIndex !== null) {
                if ((int)$selectedIndex === (int)$q->correct_option_index) {
                    $correctCount++;
                    $isCorrect = true;
                } else {
                    $wrongCount++;
                }
            }

            UserAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $q->id,
                'selected_option_index' => $selectedIndex,
                'is_correct' => $isCorrect,
            ]);
        }

        $totalScore = max(0, ($correctCount * 1.0) - ($wrongCount * $negativeMark));
        $totalQuestions = max(1, $questions->count());
        $scorePercentage = ($totalScore / $totalQuestions) * 100;

        $attempt->update([
            'score' => $totalScore,
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
        ]);

        // Record in Redis Leaderboard
        $this->leaderboardService->recordScore($exam->id, $user->id, $totalScore);

        // Update Course Progress
        if ($exam->chapter_id) {
            $passingScore = $exam->chapter->passing_score_percentage ?? 70;
            $progress = CourseProgress::firstOrCreate(
                ['user_id' => $user->id, 'chapter_id' => $exam->chapter_id],
                ['is_unlocked' => true]
            );

            $highest = max($progress->highest_score, $scorePercentage);
            $isPassed = $highest >= $passingScore;

            if ($isPassed) {
                $progress->update([
                    'highest_score' => $highest,
                    'consecutive_failures' => 0,
                ]);

                // Unlock next chapter
                $nextChapter = \App\Models\Chapter::where('course_id', $exam->chapter->course_id)
                    ->where('order_index', $exam->chapter->order_index + 1)
                    ->first();

                if ($nextChapter) {
                    CourseProgress::firstOrCreate(
                        ['user_id' => $user->id, 'chapter_id' => $nextChapter->id],
                        ['is_unlocked' => true]
                    );
                }
            } else {
                $fails = $progress->consecutive_failures + 1;
                $progress->update(['consecutive_failures' => $fails]);

                // Failure Cooldown Penalty: 3 consecutive fails = 2 hours lockout
                if ($fails >= 3) {
                    $user->update(['locked_cooldown_until' => now()->addHours(2)]);
                }
            }
        }

        // Award coins
        $earnedCoins = 10;
        if ($scorePercentage >= 100) {
            $earnedCoins += 50;
        }
        $user->increment('coins', $earnedCoins);

        // Clear Redis draft
        try {
            Redis::del("exam_draft:{$user->id}:{$exam->id}");
        } catch (\Throwable $e) {}

        return redirect()->route('exam.result', $attempt->id);
    }

    /**
     * Display Exam Result, Smart Performance Diagnosis, and Leaderboard
     */
    public function result(ExamAttempt $attempt): Response
    {
        $attempt->load(['exam.chapter', 'answers.question.chapter']);
        $leaderboard = $this->leaderboardService->getTopRanks($attempt->exam_id);
        $userRank = $this->leaderboardService->getUserRank($attempt->exam_id, $attempt->user_id);

        $totalQuestions = max(1, $attempt->answers->count());
        $scorePercentage = (int) round(($attempt->score / $totalQuestions) * 100);

        // Analyze wrong answers to find weak chapters & diagnostic reasons
        $wrongAnswers = $attempt->answers->where('is_correct', false);
        $weakChapters = [];
        $diagnosticReasons = [];

        if ($attempt->wrong_count > 0) {
            $diagnosticReasons[] = "নেগেটিভ মার্কিংয়ের কারণে " . ($attempt->wrong_count * ($attempt->exam?->negative_mark_value ?? 0.25)) . " নম্বর কাটা গেছে।";
        }

        foreach ($wrongAnswers as $wa) {
            if ($wa->question?->chapter) {
                $chId = $wa->question->chapter->id;
                if (!isset($weakChapters[$chId])) {
                    $weakChapters[$chId] = [
                        'id' => $chId,
                        'title' => $wa->question->chapter->title,
                        'wrong_count' => 0,
                    ];
                }
                $weakChapters[$chId]['wrong_count']++;
            }
        }

        if (count($weakChapters) > 0) {
            $topWeakChapter = collect($weakChapters)->sortByDesc('wrong_count')->first();
            $diagnosticReasons[] = "'{$topWeakChapter['title']}' টপিকে মূল তথ্যের ধারণার ঘাটতি রয়েছে।";
        }

        if ($scorePercentage < 70) {
            $diagnosticReasons[] = "বিসিএস পাসমার্ক (৭০%) এর চেয়ে নম্বর কম পাওয়া গেছে। রিভিশন প্রয়োজন।";
        }

        return Inertia::render('Exam/Result', [
            'attempt' => $attempt,
            'leaderboard' => $leaderboard,
            'userRank' => $userRank,
            'scorePercentage' => $scorePercentage,
            'diagnosticReasons' => $diagnosticReasons,
            'recommendedChapters' => array_values($weakChapters),
        ]);
    }
}
