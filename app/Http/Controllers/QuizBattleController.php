<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuizBattleController extends Controller
{
    public function show(Request $request): Response
    {
        $user = Auth::user();

        $count = (int) $request->input('count', 5);
        if (!in_array($count, [5, 10, 15, 20, 25])) {
            $count = 5;
        }

        $subject = $request->input('subject', null);

        $query = Question::query();

        if ($subject && $subject !== 'all') {
            $query->whereHas('chapter', function ($q) use ($subject) {
                $q->where('subject', $subject);
            });
        }

        $questions = $query->inRandomOrder()
            ->take($count)
            ->get(['id', 'question_text', 'options', 'correct_option_index', 'explanation']);

        // Fallback if requested subject questions are fewer than count
        if ($questions->count() < $count) {
            $questions = Question::inRandomOrder()
                ->take($count)
                ->get(['id', 'question_text', 'options', 'correct_option_index', 'explanation']);
        }

        // Generate dynamic AI rival opponent
        $opponents = [
            ['name' => 'তানভীর আহমেদ', 'title' => 'ঢাকা বিশ্ববিদ্যালয় • বিসিএস পরীক্ষার্থী', 'avatar' => '👨‍🎓', 'skill' => 'High'],
            ['name' => 'সাদিয়া তাসনিম', 'title' => 'রাজশাহী বিশ্ববিদ্যালয় • প্রাইমারি ক্যান্ডিডেট', 'avatar' => '👩‍🎓', 'skill' => 'Medium'],
            ['name' => 'আরিফুল ইসলাম', 'title' => 'চট্টগ্রাম বিশ্ববিদ্যালয় • ব্যাংক জব ক্যান্ডিডেট', 'avatar' => '👨‍💼', 'skill' => 'High'],
            ['name' => 'নাসরিন আক্তার', 'title' => 'জাহাঙ্গীরনগর বিশ্ববিদ্যালয় • শিক্ষক পরীক্ষার্থী', 'avatar' => '👩‍🏫', 'skill' => 'Medium'],
            ['name' => 'মাহমুদুল হাসান', 'title' => 'বুয়েট • ৯ম গ্রেড সরকারি চাকরিপ্রার্থী', 'avatar' => '👨‍💻', 'skill' => 'Pro'],
        ];

        $opponent = $opponents[array_rand($opponents)];

        return Inertia::render('Quiz/Battle', [
            'questions' => $questions,
            'opponent' => $opponent,
            'user' => $user,
            'selectedCount' => $count,
            'selectedSubject' => $subject ?: 'all',
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'user_score' => 'required|integer|min:0',
            'opponent_score' => 'required|integer|min:0',
            'total_questions' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $isWinner = $request->user_score >= $request->opponent_score;
        $earnedCoins = $isWinner ? 30 : 5;

        $user->increment('coins', $earnedCoins);

        return response()->json([
            'status' => 'success',
            'is_winner' => $isWinner,
            'earned_coins' => $earnedCoins,
            'total_coins' => $user->coins,
        ]);
    }
}
