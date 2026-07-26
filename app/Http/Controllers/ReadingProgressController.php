<?php

namespace App\Http\Controllers;

use App\Models\CourseProgress;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingProgressController extends Controller
{
    /**
     * Handle periodic 60s ping from reader page
     */
    public function ping(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'seconds' => 'integer|min:1|max:300',
        ]);

        $user = Auth::user();
        $chapter = Chapter::findOrFail($request->chapter_id);
        $addedSeconds = $request->input('seconds', 60);

        $progress = CourseProgress::firstOrCreate(
            ['user_id' => $user->id, 'chapter_id' => $chapter->id],
            ['is_unlocked' => true, 'time_spent_reading_seconds' => 0]
        );

        $newTime = $progress->time_spent_reading_seconds + $addedSeconds;
        $progress->update(['time_spent_reading_seconds' => $newTime]);

        $minRequired = $chapter->min_reading_time_seconds ?? 300;
        $isExamUnlocked = $newTime >= $minRequired;

        return response()->json([
            'time_spent' => $newTime,
            'min_required' => $minRequired,
            'is_exam_unlocked' => $isExamUnlocked,
        ]);
    }
}
