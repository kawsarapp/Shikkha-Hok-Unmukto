<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\AdSlot;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\StudyMaterial;
use App\Models\Question;
use App\Models\Exam;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    public function index(): Response
    {
        $settings = Setting::all();
        $adSlots = AdSlot::all();
        $courses = Course::with([
            'chapters' => function($query) {
                $query->whereNull('parent_id')->orderBy('order_position', 'asc');
            },
            'chapters.studyMaterial',
            'chapters.questions' => function($query) {
                $query->orderBy('order_position', 'asc');
            },
            'chapters.subChapters',
            'chapters.subChapters.studyMaterial',
            'chapters.subChapters.questions',
        ])->orderBy('order_position', 'asc')->get();

        // Auto ensure every chapter has an exam record
        foreach ($courses as $c) {
            foreach ($c->chapters as $ch) {
                if ($ch->exams->isEmpty()) {
                    Exam::create([
                        'chapter_id' => $ch->id,
                        'title' => "অধ্যায় পরীক্ষা: {$ch->title}",
                        'is_live' => true,
                        'duration_minutes' => 15,
                        'negative_mark_value' => 0.25,
                    ]);
                }
            }
        }

        return Inertia::render('Admin/Dashboard', [
            'settings' => $settings,
            'adSlots' => $adSlots,
            'courses' => $courses,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settingsData = $request->input('settings', []);

        foreach ($settingsData as $key => $value) {
            Setting::setByKey($key, $value);
        }

        return redirect()->back()->with('success', 'ডাইনামিক কনফিগারেশন সফলভাবে আপডেট করা হয়েছে।');
    }

    public function updateAdSlot(Request $request, AdSlot $adSlot)
    {
        $request->validate([
            'ad_code' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $adSlot->update([
            'ad_code' => $request->ad_code,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', 'এড স্লট সফলভাবে আপডেট করা হয়েছে।');
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(4),
            'description' => $request->description,
            'is_published' => true,
        ]);

        return redirect()->back()->with('success', "নতুন কোর্স '{$course->title}' সফলভাবে তৈরি হয়েছে।");
    }

    public function storeChapter(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'subject' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'min_reading_time_seconds' => 'required|integer|min:30',
            'passing_score_percentage' => 'required|integer|min:40|max:100',
        ]);

        $orderIndex = Chapter::where('course_id', $request->course_id)->count() + 1;

        $chapter = Chapter::create([
            'course_id' => $request->course_id,
            'subject' => $request->subject,
            'order_index' => $orderIndex,
            'title' => $request->title,
            'min_reading_time_seconds' => $request->min_reading_time_seconds,
            'passing_score_percentage' => $request->passing_score_percentage,
        ]);

        // Auto Create Default Exam for Chapter
        Exam::create([
            'chapter_id' => $chapter->id,
            'title' => "অধ্যায় পরীক্ষা: {$chapter->title}",
            'is_live' => true,
            'duration_minutes' => 15,
            'negative_mark_value' => 0.25,
        ]);

        return redirect()->back()->with('success', "নতুন অধ্যায় '{$chapter->title}' যুক্ত করা হয়েছে।");
    }

    public function storeStudyMaterial(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'content' => 'required|string',
            'pdf_file_path' => 'nullable|string',
        ]);

        $aiSummary = $this->geminiService->summarizeContent($request->content);

        StudyMaterial::updateOrCreate(
            ['chapter_id' => $request->chapter_id],
            [
                'content' => $request->content,
                'ai_summary' => $aiSummary,
                'pdf_file_path' => $request->pdf_file_path,
                'audio_flag' => true,
            ]
        );

        return redirect()->back()->with('success', 'পঠনসামগ্রী ও এআই সামারি সফলভাবে সেভ করা হয়েছে।');
    }

    public function storeQuestion(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'question_text' => 'required|string',
            'options' => 'required|array|min:4|max:4',
            'correct_option_index' => 'required|integer|min:0|max:3',
            'explanation' => 'nullable|string',
        ]);

        Question::create([
            'chapter_id' => $request->chapter_id,
            'type' => 'normal',
            'created_by_type' => 'human',
            'question_text' => $request->question_text,
            'options' => $request->options,
            'correct_option_index' => $request->correct_option_index,
            'explanation' => $request->explanation,
        ]);

        return redirect()->back()->with('success', 'নতুন প্রশ্ন সফলভাবে যুক্ত করা হয়েছে (Human Created)।');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'options' => 'required|array|min:4|max:4',
            'correct_option_index' => 'required|integer|min:0|max:3',
            'explanation' => 'nullable|string',
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'options' => $request->options,
            'correct_option_index' => $request->correct_option_index,
            'explanation' => $request->explanation,
        ]);

        return redirect()->back()->with('success', 'প্রশ্ন সফলভাবে এডিট ও আপডেট করা হয়েছে।');
    }

    public function deleteQuestion(Question $question)
    {
        $question->delete();
        return redirect()->back()->with('success', 'প্রশ্নটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function generateAiQuestions(Request $request, Chapter $chapter)
    {
        $material = $chapter->studyMaterial;
        if (!$material || !$material->content) {
            return redirect()->back()->with('error', 'প্রশ্ন তৈরির জন্য প্রথমে অধ্যায়ের পঠনসামগ্রী (Text Content) যুক্ত করুন।');
        }

        $questionsData = $this->geminiService->generateChapterQA($material->content, 5);

        foreach ($questionsData as $item) {
            Question::create([
                'chapter_id' => $chapter->id,
                'type' => 'normal',
                'created_by_type' => 'ai',
                'question_text' => $item['question_text'] ?? 'নমুনা প্রশ্ন',
                'options' => $item['options'] ?? ['অপশন ১', 'অপশন ২', 'অপশন ৩', 'অপশন ৪'],
                'correct_option_index' => $item['correct_option_index'] ?? 0,
                'explanation' => $item['explanation'] ?? '',
            ]);
        }

        return redirect()->back()->with('success', "Gemini AI সফলভাবে অধ্যায় '{$chapter->title}' এর জন্য ৫টি বহুলাইভ প্রশ্ন জেনারেট করেছে (AI Generated)।");
    }

    public function storeSubChapter(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'parent_id' => 'required|exists:chapters,id',
            'title' => 'required|string|max:255',
            'importance_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $parent = Chapter::findOrFail($request->parent_id);

        $sub = Chapter::create([
            'course_id' => $request->course_id,
            'parent_id' => $request->parent_id,
            'subject' => $parent->subject,
            'title' => $request->title,
            'importance_percentage' => $request->importance_percentage ?? 85,
            'min_reading_time_seconds' => 300,
            'passing_score_percentage' => 70,
            'is_published' => true,
        ]);

        return redirect()->back()->with('success', "উপ-অধ্যায় (Sub-chapter) '{$sub->title}' সফলভাবে তৈরি করা হয়েছে।");
    }

    public function togglePublish(Chapter $chapter)
    {
        $chapter->update(['is_published' => !$chapter->is_published]);
        $status = $chapter->is_published ? 'পাবলিশ (দৃশ্যমান)' : 'হাইড (অদৃশ্য)';
        return redirect()->back()->with('success', "অধ্যায় '{$chapter->title}' সফলভাবে {$status} করা হয়েছে।");
    }

    public function updateImportance(Request $request, Chapter $chapter)
    {
        $request->validate([
            'importance_percentage' => 'required|integer|min:0|max:100',
        ]);

        $chapter->update(['importance_percentage' => $request->importance_percentage]);
        return redirect()->back()->with('success', "অধ্যায় '{$chapter->title}' এর গুরুত্ব {$request->importance_percentage}% হিসেবে আপডেট করা হয়েছে।");
    }

    public function updateQuestionImportance(Request $request, Question $question)
    {
        $request->validate([
            'importance_percentage' => 'required|integer|min:0|max:100',
        ]);

        $question->update(['importance_percentage' => $request->importance_percentage]);
        return redirect()->back()->with('success', "প্রশ্নের গুরুত্ব {$request->importance_percentage}% হিসেবে আপডেট করা হয়েছে।");
    }

    public function deleteChapter(Chapter $chapter)
    {
        $qCount = $chapter->questions()->count();
        $subCount = $chapter->subChapters()->count();

        if ($qCount > 0 || $subCount > 0) {
            $reasons = [];
            if ($subCount > 0) $reasons[] = "{$subCount}টি সাব-অধ্যায়";
            if ($qCount > 0) $reasons[] = "{$qCount}টি প্রশ্ন";
            $reasonStr = implode(' ও ', $reasons);

            return redirect()->back()->with('error', "⚠️ ডিলিট করা সম্ভব নয়: '{$chapter->title}' এর মধ্যে {$reasonStr} যুক্ত আছে! ডিলিট করতে হলে আগে এগুলো মুছে ফেলুন।");
        }

        if ($chapter->studyMaterial) {
            $chapter->studyMaterial->delete();
        }

        $chapter->exams()->delete();
        $chapter->delete();

        return redirect()->back()->with('success', "অধ্যায় '{$chapter->title}' সফলভাবে মুছে ফেলা হয়েছে।");
    }

    public function deleteCourse(Course $course)
    {
        $chCount = $course->chapters()->count();

        if ($chCount > 0) {
            return redirect()->back()->with('error', "⚠️ ডিলিট করা সম্ভব নয়: '{$course->title}' কোর্সের অধীনে {$chCount}টি অধ্যায় যুক্ত আছে! ডিলিট করতে হলে আগে অধ্যায়গুলো ডিলিট করুন, তারপর কোর্সটি ডিলিট করতে পারবেন।");
        }

        $course->delete();

        return redirect()->back()->with('success', "কোর্স '{$course->title}' সফলভাবে মুছে ফেলা হয়েছে।");
    }
}
