<?php

namespace App\Http\Middleware;

use App\Models\Chapter;
use App\Models\CourseProgress;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckChapterUnlock
{
    public function handle(Request $request, Closure $next): Response
    {
        $chapterId = $request->route('chapter')?->id ?? $request->route('chapter');

        if (!$chapterId || !Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user is under cooldown penalty
        if ($user->locked_cooldown_until && $user->locked_cooldown_until->isFuture()) {
            $minutesLeft = now()->diffInMinutes($user->locked_cooldown_until);
            return redirect()->route('dashboard')->with('error', "পরপর ৩ বার ফেল করায় আপনার অ্যাকাউন্টটি বর্তমানে কুলডাউনে আছে। আগামী {$minutesLeft} মিনিট পর পুনরায় চেষ্টা করুন।");
        }

        $chapter = Chapter::find($chapterId);
        if (!$chapter || $chapter->order_index <= 1) {
            return $next($request);
        }

        // Find previous chapter in course
        $prevChapter = Chapter::where('course_id', $chapter->course_id)
            ->where('order_index', $chapter->order_index - 1)
            ->first();

        if ($prevChapter) {
            $prevProgress = CourseProgress::where('user_id', $user->id)
                ->where('chapter_id', $prevChapter->id)
                ->first();

            $passingPercentage = $prevChapter->passing_score_percentage ?? 70;

            if (!$prevProgress || !$prevProgress->is_unlocked || $prevProgress->highest_score < $passingPercentage) {
                return redirect()->route('dashboard')->with('error', "অধ্যায় '{$chapter->title}' টি আনলক করতে পূর্ববর্তী '{$prevChapter->title}' অধ্যায়ে অন্তত {$passingPercentage}% নম্বর অর্জন করতে হবে।");
            }
        }

        return $next($request);
    }
}
