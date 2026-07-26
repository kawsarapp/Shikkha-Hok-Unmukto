<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\AdSlot;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\StudyMaterial;
use App\Models\Question;
use App\Models\Exam;
use App\Models\CourseProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin & Test Student Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@educationfree.com'],
            [
                'name' => 'System Admin',
                'phone' => '01700000000',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@educationfree.com'],
            [
                'name' => 'নমুনা শিক্ষার্থী',
                'phone' => '01800000000',
                'role' => 'student',
                'study_streak' => 5,
                'coins' => 120,
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Dynamic Settings (Zero Hardcoding Policy)
        Setting::setByKey('gemini_api_key', '', 'string', 'ai', 'Google Gemini API Key');
        Setting::setByKey('gemini_model', 'gemini-1.5-flash', 'string', 'ai', 'Gemini AI Model');
        Setting::setByKey('ai_summary_prompt', 'Act as an expert teacher. Summarize the following chapter text in bullet points in clear Bengali.', 'text', 'ai', 'AI Summary Prompt');
        Setting::setByKey('ai_qa_prompt', 'Generate 5 high-quality multiple-choice questions in JSON format based on the text.', 'text', 'ai', 'AI Q&A Generation Prompt');

        Setting::setByKey('global_min_reading_seconds', '300', 'integer', 'progression', 'Minimum Reading Time (Seconds)');
        Setting::setByKey('global_passing_score_percentage', '70', 'integer', 'progression', 'Passing Score Threshold (%)');
        Setting::setByKey('failure_cooldown_minutes', '120', 'integer', 'progression', 'Failure Cooldown Penalty (Minutes)');
        Setting::setByKey('negative_mark_value', '0.25', 'string', 'exam', 'Negative Mark Value');

        Setting::setByKey('site_name', 'EducationAlwaysFree', 'string', 'branding', 'Platform Name');
        Setting::setByKey('primary_color_hex', '#4F46E5', 'string', 'branding', 'Primary Brand Color (Hex)');

        // 3. Dynamic Ad Slots
        AdSlot::firstOrCreate(['slot_name' => 'sidebar_ad'], ['title' => 'Sidebar Banner', 'ad_code' => '<div class="p-4 bg-indigo-50 dark:bg-slate-800 rounded-xl text-center text-sm text-indigo-600 dark:text-indigo-400 font-medium">📢 Sponsored Learning Partner Banner</div>', 'is_active' => true]);
        AdSlot::firstOrCreate(['slot_name' => 'in_content_ad'], ['title' => 'In-Content Banner', 'ad_code' => '<div class="my-6 p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-xl text-center text-xs text-amber-800 dark:text-amber-300">💡 এডুকেশন অলওয়েজ ফ্রি - বিনামূল্যে বিশ্বমানের শিক্ষা</div>', 'is_active' => true]);

        // 4. Primary Teacher Recruitment Exam Prep Course
        $primaryCourse = Course::firstOrCreate(
            ['slug' => 'primary-teacher-recruitment-exam'],
            [
                'title' => 'প্রাইমারি সহকারী শিক্ষক নিয়োগ পরীক্ষা প্রস্তুতি',
                'description' => 'বাংলা, ইংরেজি, গণিত ও সাধারণ জ্ঞানের বিষয়ভিত্তিক টেক্সট ও এআই চালিত বিশেষ মডেল টেস্ট।',
                'is_published' => true,
            ]
        );

        // Subject: বাংলা
        $banglaCh1 = Chapter::firstOrCreate(
            ['course_id' => $primaryCourse->id, 'title' => 'বাংলা: চর্যাপদ ও ব্যাকরণ অংশ'],
            [
                'subject' => 'বাংলা',
                'order_index' => 1,
                'min_reading_time_seconds' => 180,
                'passing_score_percentage' => 70,
            ]
        );

        // Subject: ইংরেজি
        $englishCh1 = Chapter::firstOrCreate(
            ['course_id' => $primaryCourse->id, 'title' => 'English: Parts of Speech & Tense'],
            [
                'subject' => 'ইংরেজি',
                'order_index' => 2,
                'min_reading_time_seconds' => 180,
                'passing_score_percentage' => 70,
            ]
        );

        // Subject: গণিত
        $mathCh1 = Chapter::firstOrCreate(
            ['course_id' => $primaryCourse->id, 'title' => 'গণিত: পাটিগণিত (ল.সা.গু ও গ.সা.গু)'],
            [
                'subject' => 'গণিত',
                'order_index' => 3,
                'min_reading_time_seconds' => 180,
                'passing_score_percentage' => 70,
            ]
        );

        // 5. Study Material for Bangla
        StudyMaterial::firstOrCreate(
            ['chapter_id' => $banglaCh1->id],
            [
                'content' => "# চর্যাপদ ও বাংলা ব্যাকরণ অংশ (প্রাইমারি নিয়োগ বিশেষ)\n\nবাংলা সাহিত্যের প্রাচীনতম নিদর্শন হলো **চর্যাপদ**। এটি মূলত বৌদ্ধ সহজিয়া সাধকদের রচিত গান ও দোহাবলি।\n\n### আবিষ্কার ও প্রকাশনা\n- **আবিষ্কারক:** মহামহোপাধ্যায় হরপ্রসাদ শাস্ত্রী।\n- **আবিষ্কারের সাল:** ১৯০৭ সন।\n- **আবিষ্কারের স্থান:** নেপালের রাজদরবারের গ্রন্থগার ('রয়েল লাইব্রেরি') থেকে।\n- **প্রকাশনা:** ১৯১৬ সালে বঙ্গীয় সাহিত্য পরিষদ থেকে 'হাজার বছরের পুরাণ বাঙ্গালা ভাষায় রচিত বৌদ্ধ গান ও দোহা' নামে প্রকাশিত হয়।\n\n### কবি ও পদ সংখ্যা\n- চর্যাপদের মোট পদের সংখ্যা সাড়ে ছেচল্লিশটি (৪৬.৫টি)।\n- পদকর্তার সংখ্যা ২৪ জন (মতান্তরে ২৩ জন)।\n- সর্বাধিক পদ রচয়িতা হলেন **কাহ্নপা** (১৩টি পদ)।\n- প্রাচীনতম পদকর্তা হলেন **লুইপা** (পদ ১ ও ২৯)।",
                'ai_summary' => "• চর্যাপদ বাংলা সাহিত্যের একমাত্র প্রাচীন যুগের নিদর্শন।\n• হরপ্রসাদ শাস্ত্রী ১৯০৭ সালে নেপাল থেকে এটি আবিষ্কার করেন।\n• সর্বাধিক পদ রচয়িতা কাহ্নপা (১৩টি পদ) এবং প্রথম পদকর্তা লুইপা।",
                'audio_flag' => true,
            ]
        );

        // Questions for Bangla Chapter
        Question::firstOrCreate(
            ['question_text' => 'চর্যাপদ কত সালে নেপালের রাজদরবার থেকে আবিষ্কৃত হয়?'],
            [
                'chapter_id' => $banglaCh1->id,
                'type' => 'normal',
                'options' => ['১৯০৫ সালে', '১৯০৭ সালে', '১৯১৬ সালে', '১৯২১ সালে'],
                'correct_option_index' => 1,
                'explanation' => 'হরপ্রসাদ শাস্ত্রী ১৯০৭ সালে নেপালের রয়্যাল লাইব্রেরি থেকে চর্যাপদ আবিষ্কার করেন।',
            ]
        );

        Question::firstOrCreate(
            ['question_text' => 'চর্যাপদে সর্বাধিক পদ রচনা করেছেন কোন কবি?'],
            [
                'chapter_id' => $banglaCh1->id,
                'type' => 'normal',
                'options' => ['লুইপা', 'ভূসুকুপা', 'কাহ্নপা', 'শবরপা'],
                'correct_option_index' => 2,
                'explanation' => 'কাহ্নপা চর্যাপদে সর্বাধিক ১৩টি পদ রচনা করেন।',
            ]
        );

        // Exams
        Exam::firstOrCreate(
            ['chapter_id' => $banglaCh1->id],
            [
                'title' => 'প্রাইমারি বাংলা মডেল টেস্ট: চর্যাপদ',
                'is_live' => true,
                'duration_minutes' => 10,
                'negative_mark_value' => 0.25,
            ]
        );

        // Unlock Bangla for Test Student
        CourseProgress::firstOrCreate(
            ['user_id' => $student->id, 'chapter_id' => $banglaCh1->id],
            [
                'is_unlocked' => true,
                'highest_score' => 0,
            ]
        );
    }
}
