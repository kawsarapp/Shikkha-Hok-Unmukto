<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CoinStoreController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $items = [
            [
                'id' => 'gold_badge',
                'title' => '🥇 গোল্ডেন প্রাইমারি স্কলার ব্যাজ',
                'description' => 'আপনার প্রোফাইলে গোল্ডেন স্কলার ব্যাজ প্রদর্শন করবে যা আপনার অলরাউন্ড শ্রেষ্ঠত্ব প্রকাশ করে।',
                'cost' => 50,
                'category' => 'Badge',
                'icon' => '🏅',
            ],
            [
                'id' => 'model_test_pass',
                'title' => '⚡ অল-ইন-ওয়ান স্পেশাল মডেল টেস্ট পাস',
                'description' => 'বিসিএস ও প্রাইমারি পরীক্ষার জন্য সকল প্রিমিয়াম স্পেশাল মডেল টেস্ট ফ্রীতে দিন।',
                'cost' => 100,
                'category' => 'Pass',
                'icon' => '🎫',
            ],
            [
                'id' => 'vip_title',
                'title' => '👑 ভিআইপি স্কলার টাইটেল (VIP Status)',
                'description' => 'মেধা তালিকা ও ড্যাশবোর্ডে আপনার নামের পাশে ভিআইপি ব্যাজ ও বিশেষ টাইটেল দেখাবে।',
                'cost' => 150,
                'category' => 'Title',
                'icon' => '👑',
            ],
        ];

        return Inertia::render('Store/Index', [
            'items' => $items,
            'user' => $user,
        ]);
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|string',
            'cost' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if ($user->coins < $request->cost) {
            return redirect()->back()->with('error', 'আপনার পর্যাপ্ত কয়েন ব্যালেন্স নেই!');
        }

        // Deduct coins & append badge
        $user->decrement('coins', $request->cost);

        $currentBadges = $user->badges ?? [];
        if (!in_array($request->item_id, $currentBadges)) {
            $currentBadges[] = $request->item_id;
            $user->update(['badges' => $currentBadges]);
        }

        return redirect()->back()->with('success', 'অভিনন্দন! আপনি সফলভাবে আইটেমটি রিডিম করেছেন!');
    }
}
