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
}
