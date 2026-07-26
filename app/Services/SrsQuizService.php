<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAnswer;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Collection;

class SrsQuizService
{
    /**
     * Generate or fetch Daily Weakness Quiz for user
     */
    public function getOrCreateDailyWeaknessQuiz(User $user): ?Exam
    {
        // Get incorrect user answers from past 7 days
        $wrongQuestionIds = UserAnswer::whereHas('attempt', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('started_at', '>=', now()->subDays(7));
        })
        ->where('is_correct', false)
        ->pluck('question_id')
        ->unique();

        if ($wrongQuestionIds->isEmpty()) {
            return null;
        }

        $title = 'দৈনিক দুর্বলতা কাটিয়ে ওঠার কুইজ (SRS) - ' . now()->format('Y-m-d');

        $exam = Exam::firstOrCreate(
            ['title' => $title],
            [
                'is_live' => false,
                'duration_minutes' => 15,
                'negative_mark_value' => 0.00,
                'sudden_death_mode' => false,
            ]
        );

        // Mark questions as SRS type
        Question::whereIn('id', $wrongQuestionIds)->update(['type' => 'srs']);

        return $exam;
    }
}
