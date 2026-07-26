<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Fetch user attempts with exams and chapters
        $attempts = ExamAttempt::with(['exam.chapter', 'answers.question'])
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->get();

        $subjectStats = [
            'বাংলা' => ['total' => 0, 'correct' => 0, 'percentage' => 0, 'status' => 'গুড'],
            'ইংরেজি' => ['total' => 0, 'correct' => 0, 'percentage' => 0, 'status' => 'গুড'],
            'গণিত' => ['total' => 0, 'correct' => 0, 'percentage' => 0, 'status' => 'গুড'],
            'সাধারণ জ্ঞান' => ['total' => 0, 'correct' => 0, 'percentage' => 0, 'status' => 'গুড'],
        ];

        foreach ($attempts as $att) {
            $subj = $att->exam->chapter->subject ?? 'বাংলা';
            if (!isset($subjectStats[$subj])) {
                $subjectStats[$subj] = ['total' => 0, 'correct' => 0, 'percentage' => 0, 'status' => 'গুড'];
            }

            foreach ($att->answers as $ans) {
                $subjectStats[$subj]['total']++;
                if ($ans->is_correct) {
                    $subjectStats[$subj]['correct']++;
                }
            }
        }

        foreach ($subjectStats as $sName => &$stat) {
            if ($stat['total'] > 0) {
                $stat['percentage'] = Math_round(($stat['correct'] / $stat['total']) * 100);
            } else {
                $stat['percentage'] = 0;
            }

            if ($stat['percentage'] >= 80) {
                $stat['status'] = 'উৎকৃষ্ট 🟢';
            } elseif ($stat['percentage'] >= 60) {
                $stat['status'] = 'সন্তোষজনক 🟡';
            } else {
                $stat['status'] = 'দুর্বল (রিভিশন প্রয়োজন) 🔴';
            }
        }

        return Inertia::render('Analytics/Performance', [
            'subjectStats' => $subjectStats,
            'totalAttemptsCount' => $attempts->count(),
            'user' => $user,
        ]);
    }
}

function Math_round($val) {
    return (int)round($val);
}
