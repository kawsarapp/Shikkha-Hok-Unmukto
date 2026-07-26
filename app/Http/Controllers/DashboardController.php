<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Chapter;
use App\Models\CourseProgress;
use App\Models\AdSlot;
use App\Services\SrsQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected SrsQuizService $srsQuizService
    ) {}

    /**
     * Main Dashboard Page
     */
    public function index(): Response
    {
        $user = Auth::user();
        $courses = Course::with([
            'chapters' => function($query) {
                $query->whereNull('parent_id')
                      ->where('is_published', true)
                      ->orderBy('order_position', 'asc');
            },
            'chapters.exams',
            'chapters.subChapters' => function($query) {
                $query->where('is_published', true)
                      ->orderBy('order_position', 'asc');
            },
            'chapters.subChapters.exams',
        ])->where('is_published', true)->orderBy('order_position', 'asc')->get();

        foreach ($courses as $c) {
            foreach ($c->chapters as $ch) {
                if ($ch->exams->isEmpty()) {
                    $exam = \App\Models\Exam::create([
                        'chapter_id' => $ch->id,
                        'title' => "অধ্যায় পরীক্ষা: {$ch->title}",
                        'is_live' => true,
                        'duration_minutes' => 15,
                        'negative_mark_value' => 0.25,
                    ]);
                    $ch->setRelation('exams', collect([$exam]));
                }
                foreach ($ch->subChapters as $subCh) {
                    if ($subCh->exams->isEmpty()) {
                        $exam = \App\Models\Exam::create([
                            'chapter_id' => $subCh->id,
                            'title' => "সাব-অধ্যায় পরীক্ষা: {$subCh->title}",
                            'is_live' => true,
                            'duration_minutes' => 15,
                            'negative_mark_value' => 0.25,
                        ]);
                        $subCh->setRelation('exams', collect([$exam]));
                    }
                }
            }
        }

        $progressMap = CourseProgress::where('user_id', $user?->id)
            ->get()
            ->keyBy('chapter_id');

        $srsQuiz = $user ? $this->srsQuizService->getOrCreateDailyWeaknessQuiz($user) : null;
        $adSlots = AdSlot::where('is_active', true)->get()->keyBy('slot_name');

        return Inertia::render('Dashboard', [
            'courses' => $courses,
            'progressMap' => $progressMap,
            'srsQuiz' => $srsQuiz,
            'adSlots' => $adSlots,
        ]);
    }

    /**
     * Study Material Reader Page
     */
    public function readChapter(Chapter $chapter): Response
    {
        $user = Auth::user();
        $chapter->load(['course', 'studyMaterial', 'exams']);

        $progress = CourseProgress::firstOrCreate(
            ['user_id' => $user->id, 'chapter_id' => $chapter->id],
            ['is_unlocked' => true, 'time_spent_reading_seconds' => 0]
        );

        $adSlots = AdSlot::where('is_active', true)->get()->keyBy('slot_name');

        return Inertia::render('Study/Reader', [
            'chapter' => $chapter,
            'progress' => $progress,
            'adSlots' => $adSlots,
        ]);
    }
}
