<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function show(Course $course): Response
    {
        $user = Auth::user();
        $course->load('chapters');

        $chapterIds = $course->chapters->pluck('id');
        $passedCount = CourseProgress::where('user_id', $user->id)
            ->whereIn('chapter_id', $chapterIds)
            ->where('highest_score', '>=', 70)
            ->count();

        $totalChapters = max(1, $course->chapters->count());
        $isCompleted = $passedCount >= $totalChapters;

        return Inertia::render('Certificate', [
            'course' => $course,
            'user' => $user,
            'isCompleted' => $isCompleted,
            'passedCount' => $passedCount,
            'totalChapters' => $totalChapters,
            'completedDate' => now()->format('d M, Y'),
        ]);
    }
}
