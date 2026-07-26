<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { BookOpen, CheckCircle, Lock, Play, Sparkles, Award, ArrowRight, Layers, Flame, Coins, Clock, Filter, ChevronDown, ChevronRight, HelpCircle } from 'lucide-vue-next';

const props = defineProps({
    courses: Array,
    progressMap: Object,
    srsQuiz: Object,
    adSlots: Object,
});

const activeSubjectFilter = ref('all');
const expandedSubjects = ref({}); // Track expanded subjects

const toggleSubjectAccordion = (subjName) => {
    expandedSubjects.value[subjName] = !expandedSubjects.value[subjName];
};

const isSubjectExpanded = (subjName) => {
    return expandedSubjects.value[subjName] !== false; // Default expanded
};

const getGroupedChapters = (chapters) => {
    if (!chapters) return {};
    return chapters.reduce((acc, curr) => {
        const subj = curr.subject || 'বাংলা';
        if (activeSubjectFilter.value !== 'all' && subj !== activeSubjectFilter.value) {
            return acc;
        }
        if (!acc[subj]) acc[subj] = [];
        acc[subj].push(curr);
        return acc;
    }, {});
};

// Calculate total course completion percentage
const getCourseCompletionPercentage = (course) => {
    if (!course?.chapters?.length) return 0;
    const completedCount = course.chapters.filter(ch => props.progressMap[ch.id]?.highest_score >= (ch.passing_score_percentage || 70)).length;
    return Math.round((completedCount / course.chapters.length) * 100);
};
</script>

<template>
    <AppLayout>
        <div class="space-y-8">
            <!-- Banner Hero Section -->
            <div class="p-8 bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center space-x-1 px-3 py-1 bg-indigo-500/30 border border-indigo-400/30 rounded-full text-indigo-200 text-xs font-semibold mb-3">
                        <Sparkles class="w-3.5 h-3.5 text-indigo-300" />
                        <span>প্রাইমারি শিক্ষক নিয়োগ ও বিসিএস স্মার্ট প্রস্তুতি</span>
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3">
                        বাংলা, ইংরেজি, গণিত ও বিষয়ভিত্তিক ডিজিটাল সিলেবাস
                    </h1>
                    <p class="text-indigo-200 text-sm md:text-base leading-relaxed">
                        ভিডিওর পেছনে সময় নষ্ট না করে টেক্সট লেকচার, পেজ-বাই-পেজ পড়া, এআই অডিও এবং মেধা তালিকার লাইভ মডেল টেস্ট দিয়ে পূর্ণাঙ্গ প্রস্তুতি নিন।
                    </p>
                </div>
            </div>

            <!-- Student Quick Performance Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-5 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center shrink-0">
                        <Flame class="w-6 h-6 fill-amber-500" />
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-bold block">পড়ার স্ট্রিক</span>
                        <span class="text-xl font-black text-gray-900 dark:text-slate-100">{{ $page.props.auth.user?.study_streak || 0 }} দিন</span>
                    </div>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 flex items-center justify-center shrink-0">
                        <Coins class="w-6 h-6 fill-indigo-500" />
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-bold block">অর্জিত কয়েন</span>
                        <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $page.props.auth.user?.coins || 0 }}</span>
                    </div>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500 flex items-center justify-center shrink-0">
                        <CheckCircle class="w-6 h-6" />
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-bold block">সম্পন্ন অধ্যায়</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                            {{ Object.values(progressMap).filter(p => p.highest_score >= 70).length }}টি
                        </span>
                    </div>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl shadow-sm flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-500 flex items-center justify-center shrink-0">
                        <Award class="w-6 h-6" />
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-bold block">সার্টিফিকেট স্ট্যাটাস</span>
                        <span class="text-xs font-black text-purple-600 dark:text-purple-400">অনলাইন যাচাইকৃত</span>
                    </div>
                </div>
            </div>

            <!-- SRS Weakness Quiz Banner (If Available) -->
            <div v-if="srsQuiz" class="p-6 bg-gradient-to-r from-rose-500 to-amber-500 text-white rounded-3xl shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white/20 backdrop-blur-md rounded-xl">
                        <Sparkles class="w-8 h-8 text-white animate-spin-slow" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">দৈনিক দুর্বলতা কুইজ (SRS) প্রস্তুত!</h3>
                        <p class="text-xs text-rose-100">বিগত পরীক্ষার ভুল উত্তরগুলোর ওপর ভিত্তি করে এআই কুইজ তৈরি করা হয়েছে।</p>
                    </div>
                </div>
                <Link
                    :href="`/exam/${srsQuiz.id}`"
                    class="px-6 py-3 bg-white text-rose-600 hover:bg-rose-50 rounded-xl font-bold text-sm shadow-md transition transform hover:scale-105"
                >
                    কুইজ দিন
                </Link>
            </div>

            <!-- In-Content Ad Slot -->
            <div v-if="adSlots?.in_content_ad?.is_active" v-html="adSlots.in_content_ad.ad_code"></div>

            <!-- Subject Filter Bar -->
            <div class="flex items-center space-x-2 bg-white dark:bg-slate-800 p-2 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-x-auto">
                <span class="text-xs font-bold text-gray-400 px-3 flex items-center space-x-1 shrink-0">
                    <Filter class="w-3.5 h-3.5" />
                    <span>বিষয় ফিল্টার:</span>
                </span>

                <button
                    @click="activeSubjectFilter = 'all'"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                    :class="activeSubjectFilter === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    সকল বিষয়
                </button>
                <button
                    @click="activeSubjectFilter = 'বাংলা'"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                    :class="activeSubjectFilter === 'বাংলা' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    📚 বাংলা
                </button>
                <button
                    @click="activeSubjectFilter = 'ইংরেজি'"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                    :class="activeSubjectFilter === 'ইংরেজি' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    🔤 ইংরেজি
                </button>
                <button
                    @click="activeSubjectFilter = 'গণিত'"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                    :class="activeSubjectFilter === 'গণিত' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    🔢 গণিত
                </button>
                <button
                    @click="activeSubjectFilter = 'সাধারণ জ্ঞান'"
                    class="px-4 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                    :class="activeSubjectFilter === 'সাধারণ জ্ঞান' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
                >
                    🌐 সাধারণ জ্ঞান
                </button>
            </div>

            <!-- Courses & Subjects Section -->
            <div v-for="course in courses" :key="course.id" class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 p-6 shadow-sm space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-100 dark:border-slate-700 gap-4">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">কোর্স</span>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ course.title }}</h2>
                        
                        <!-- Course Progress Bar -->
                        <div class="w-64 bg-gray-200 dark:bg-slate-700 h-2 rounded-full mt-2 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-indigo-500 to-emerald-500 h-full rounded-full transition-all duration-500"
                                :style="{ width: getCourseCompletionPercentage(course) + '%' }"
                            ></div>
                        </div>
                        <span class="text-[11px] font-bold text-gray-400 mt-1 block">অগ্রগতি: {{ getCourseCompletionPercentage(course) }}% সম্পন্ন</span>
                    </div>

                    <!-- Certificate Link Button -->
                    <Link
                        :href="`/certificate/${course.id}`"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-md shadow-amber-500/20"
                    >
                        <Award class="w-4 h-4" />
                        <span>কোর্স সার্টিফিকেট দেখুন</span>
                    </Link>
                </div>

                <!-- Subject-Wise Chapter Accordion Grouping -->
                <div v-for="(subjectChapters, subjectName) in getGroupedChapters(course.chapters)" :key="subjectName" class="space-y-4 border border-gray-100 dark:border-slate-700/60 p-4 rounded-2xl bg-gray-50/50 dark:bg-slate-900/40">
                    <div
                        @click="toggleSubjectAccordion(subjectName)"
                        class="flex items-center justify-between cursor-pointer select-none p-2 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition"
                    >
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 bg-indigo-600 text-white font-extrabold text-sm rounded-xl shadow-sm">
                                📚 বিষয়: {{ subjectName }}
                            </span>
                            <span class="text-xs text-gray-500 font-bold">({{ subjectChapters.length }}টি অধ্যায় অন্তর্ভুক্ত)</span>
                        </div>

                        <div class="flex items-center space-x-1 text-indigo-600 font-bold text-xs">
                            <span>{{ isSubjectExpanded(subjectName) ? 'সংকুচিত করুন' : 'অধ্যায়সমূহ দেখুন' }}</span>
                            <ChevronDown v-if="isSubjectExpanded(subjectName)" class="w-4 h-4" />
                            <ChevronRight v-else class="w-4 h-4" />
                        </div>
                    </div>

                    <!-- Chapters Grid (Accordion Body) -->
                    <div v-if="isSubjectExpanded(subjectName)" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div
                            v-for="(chapter, idx) in subjectChapters"
                            :key="chapter.id"
                            class="p-5 rounded-2xl border transition-all duration-200"
                            :class="[
                                progressMap[chapter.id]?.is_unlocked || idx === 0
                                    ? 'bg-gray-50/80 dark:bg-slate-900/50 border-gray-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500'
                                    : 'bg-gray-100/50 dark:bg-slate-900/20 border-gray-200/40 dark:border-slate-800 opacity-60'
                            ]"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm"
                                        :class="[
                                            progressMap[chapter.id]?.highest_score >= (chapter.passing_score_percentage || 70)
                                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400'
                                                : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400'
                                        ]"
                                    >
                                        {{ idx + 1 }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-base text-gray-900 dark:text-slate-100">{{ chapter.title }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                            সর্বনিম্ন পড়া: {{ Math.round(chapter.min_reading_time_seconds / 60) }} মিনিট • পাসমার্ক: {{ chapter.passing_score_percentage }}%
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Bar -->
                            <div class="mt-4 pt-3 border-t border-gray-200/60 dark:border-slate-800 flex items-center justify-between">
                                <div class="text-xs">
                                    <span v-if="progressMap[chapter.id]?.highest_score" class="font-semibold text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                                        <CheckCircle class="w-3.5 h-3.5 inline mr-1" />
                                        স্কোর: {{ progressMap[chapter.id].highest_score }}%
                                    </span>
                                    <span v-else class="text-gray-400">পরীক্ষা দেওয়া হয়নি</span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <!-- Flashcards Quick Link -->
                                    <Link
                                        v-if="progressMap[chapter.id]?.is_unlocked || idx === 0"
                                        :href="`/chapter/${chapter.id}/flashcards`"
                                        class="px-3 py-1.5 bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 rounded-xl text-xs font-bold border border-purple-200 dark:border-purple-900"
                                    >
                                        ফ্ল্যাশকার্ড
                                    </Link>

                                    <!-- Direct Take Exam Button -->
                                    <Link
                                        v-if="(progressMap[chapter.id]?.is_unlocked || idx === 0) && chapter.exams?.[0]"
                                        :href="`/exam/${chapter.exams[0].id}`"
                                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-md shadow-emerald-600/20"
                                    >
                                        <span>পরীক্ষা দিন</span>
                                    </Link>

                                    <!-- Read Chapter Action Button -->
                                    <Link
                                        v-if="progressMap[chapter.id]?.is_unlocked || idx === 0"
                                        :href="`/chapter/${chapter.id}`"
                                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1"
                                    >
                                        <span>পড়া শুরু করুন</span>
                                        <ArrowRight class="w-3.5 h-3.5" />
                                    </Link>
                                    <span v-else class="px-3 py-1.5 bg-gray-200 dark:bg-slate-800 text-gray-400 rounded-xl text-xs font-semibold flex items-center space-x-1">
                                        <Lock class="w-3.5 h-3.5" />
                                        <span>লকড</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
