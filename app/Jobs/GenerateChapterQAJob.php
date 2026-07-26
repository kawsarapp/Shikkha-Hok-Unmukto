<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Models\Question;

use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateChapterQAJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Chapter $chapter,
        public int $questionCount = 5
    ) {}

    public function handle(GeminiService $geminiService): void
    {
        $material = $this->chapter->studyMaterial;
        if (!$material || !$material->content) {
            return;
        }

        $questionsData = $geminiService->generateChapterQA($material->content, $this->questionCount);

        foreach ($questionsData as $item) {
            Question::create([
                'chapter_id' => $this->chapter->id,
                'type' => 'normal',
                'question_text' => $item['question_text'] ?? 'নমুনা প্রশ্ন',
                'options' => $item['options'] ?? ['অপশন ১', 'অপশন ২', 'অপশন ৩', 'অপশন ৪'],
                'correct_option_index' => $item['correct_option_index'] ?? 0,
                'explanation' => $item['explanation'] ?? '',
            ]);
        }

        Log::info("Successfully generated {$this->questionCount} AI questions for Chapter ID: {$this->chapter->id}");
    }
}
