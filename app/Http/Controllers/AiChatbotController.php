<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiChatbotController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    public function ask(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'message' => 'required|string|max:1000',
        ]);

        $chapter = Chapter::with('studyMaterial')->findOrFail($request->chapter_id);
        $content = $chapter->studyMaterial?->content ?? $chapter->title;

        $reply = $this->geminiService->chatWithChapterContext($content, $request->message);

        return response()->json([
            'reply' => $reply,
        ]);
    }

    /**
     * 24/7 Global AI BCS Doubt Solver Endpoint
     */
    public function askDoubt(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'chapter_id' => 'nullable|exists:chapters,id',
        ]);

        $context = "বিসিএস ও সরকারি চাকরির প্রস্তুতি বিষয়ক সাধারণ বিষয়সমূহ";
        if ($request->chapter_id) {
            $chapter = Chapter::with('studyMaterial')->find($request->chapter_id);
            if ($chapter) {
                $context = "অধ্যায়: {$chapter->title}. বিষয়বস্তু: " . ($chapter->studyMaterial?->content ?? '');
            }
        }

        $prompt = "তুমি একজন বিশেষজ্ঞ বিসিএস ও সরকারি চাকরি ক্যাডার গাইড শিক্ষাগুরু (AI Tutor)। শিক্ষার্থীর প্রশ্ন: '{$request->message}'. প্রসঙ্গের বিষয়: '{$context}'. শিক্ষার্থীকে খুব প্রাঞ্জল, সহজ বাংলা ভাষায় উদাহরণ ও ব্যাখ্যাসহ বুলেট পয়েন্টে উত্তর দাও। প্রয়োজনীয় গাণিতিক বা ব্যাকরণিক সূত্রের সহজ শর্টকাট টেকনিক উল্লেখ করো।";

        $reply = $this->geminiService->generateResponse($prompt);

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
