<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Get configured Gemini API key from settings or env
     */
    protected function getApiKey(): ?string
    {
        return Setting::getByKey('gemini_api_key') ?: config('services.gemini.api_key', env('GEMINI_API_KEY'));
    }

    /**
     * Get configured Gemini model name from settings (default gemini-1.5-flash)
     */
    protected function getModel(): string
    {
        return Setting::getByKey('gemini_model', 'gemini-1.5-flash');
    }

    /**
     * Generate Q&A array from chapter text in JSON format
     */
    public function generateChapterQA(string $chapterContent, int $count = 5): array
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            // Fallback mock output if API key is not configured yet
            return $this->getMockQuestions($chapterContent, $count);
        }

        $model = $this->getModel();
        $customPrompt = Setting::getByKey('ai_qa_prompt', 'Generate multiple-choice questions in JSON format based on the text.');

        $systemInstruction = "You are an expert educational question setter. Create {$count} multiple choice questions in Bengali based strictly on the provided text.
Return ONLY valid JSON matching this exact JSON schema:
[
  {
    \"question_text\": \"প্রশ্নটি এখানে লিখুন\",
    \"options\": [\"অপশন ১\", \"অপশন ২\", \"অপশন ৩\", \"অপশন ৪\"],
    \"correct_option_index\": 0,
    \"explanation\": \"সঠিক উত্তরের ব্যাখ্যা\"
  }
]";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "{$customPrompt}\n\nStudy Text:\n{$chapterContent}"]
                        ]
                    ]
                ],
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.4,
                ]
            ]);

            if ($response->successful()) {
                $rawJson = $response->json('candidates.0.content.parts.0.text');
                $parsed = json_decode($rawJson, true);
                if (is_array($parsed)) {
                    return $parsed;
                }
            } else {
                Log::error('Gemini API Error: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
        }

        return $this->getMockQuestions($chapterContent, $count);
    }

    /**
     * Summarize study text into Bengali bullet points
     */
    public function summarizeContent(string $content): string
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return "• " . implode("\n• ", array_slice(explode("\n", strip_tags($content)), 0, 4));
        }

        $model = $this->getModel();
        $prompt = Setting::getByKey('ai_summary_prompt', 'Summarize the text in 4 clear bullet points in Bengali.');

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "{$prompt}\n\nText:\n{$content}"]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? '';
            }
        } catch (\Throwable $e) {
            Log::error('Gemini Summarize Exception: ' . $e->getMessage());
        }

        return "• " . implode("\n• ", array_slice(explode("\n", strip_tags($content)), 0, 4));
    }

    /**
     * RAG lightweight AI chatbot response scoped strictly to chapter text
     */
    public function chatWithChapterContext(string $chapterContent, string $userMessage): string
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return "ধন্যবাদ আপনার প্রশ্নের জন্য! টেক্সট অনুযায়ী: " . mb_substr($chapterContent, 0, 150) . "...";
        }

        $model = $this->getModel();

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "You are a helpful study tutor. Answer the student's question in Bengali strictly based on the following chapter text. Keep answers concise and polite.\n\nChapter Context:\n{$chapterContent}\n\nStudent Question:\n{$userMessage}"]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? 'দুঃখিত, কোনো উত্তর পাওয়া যায়নি।';
            }
        } catch (\Throwable $e) {
            Log::error('Gemini Chat Exception: ' . $e->getMessage());
        }

        return "ক্ষমা করবেন, এআই সার্ভারে সংযোগ করা সম্ভব হয়নি। অনুগ্রহ করে পরবর্তীতে চেষ্টা করুন।";
    }

    /**
     * Fallback mock questions when API Key is not yet populated by user
     */
    protected function getMockQuestions(string $content, int $count): array
    {
        return [
            [
                'question_text' => 'পাঠ্যবই অনুযায়ী প্রধান আলোচ্য বিষয় কোনটি?',
                'options' => ['প্রাথমিক তথ্য', 'উচ্চতর তত্ত্ব', 'ঐতিহাসিক বিবরণ', 'সবকটি সঠিক'],
                'correct_option_index' => 0,
                'explanation' => 'পাঠ্যাংশের শুরুর অনুচ্ছেদে বিষয়টি স্পষ্টভাবে উল্লেখ রয়েছে।',
            ],
            [
                'question_text' => 'উপরোক্ত অধ্যায়ে গুরুত্বপূর্ণ তথ্য নির্দেশক চিহ্ন কোনটি?',
                'options' => ['তারিখ ও আবিষ্কারের নাম', 'কেবল ভৌগোলিক অবস্থান', 'কাল্পনিক রূপক', 'কোনোটিই নয়'],
                'correct_option_index' => 0,
                'explanation' => 'অধ্যায়টিতে বিভিন্ন আবিষ্কার ও আবিষ্কারকের নাম সম্পর্কিত সালগুলোই মূল প্রাধান্য পেয়েছে।',
            ],
        ];
    }
}
