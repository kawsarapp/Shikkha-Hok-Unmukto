<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useExamAntiCheat } from '@/Composables/useExamAntiCheat';
import WatermarkOverlay from '@/Components/WatermarkOverlay.vue';
import ContentProtection from '@/Components/ContentProtection.vue';
import axios from 'axios';
import { Clock, ShieldAlert, CheckCircle2, AlertTriangle, HelpCircle, Check, X, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    exam: Object,
    chapter: Object,
    questions: Array,
    draftAnswers: Object,
    serverTime: Number,
});

const selectedAnswers = ref({ ...props.draftAnswers });
const isSubmitting = ref(false);
const isPracticeMode = ref(true); // Instant learning feedback mode enabled by default!

// Countdown Timer Sync
const durationSeconds = (props.exam?.duration_minutes || 15) * 60;
const remainingSeconds = ref(durationSeconds);
let timerInterval = null;

const formatTime = (secs) => {
    const m = Math.floor(secs / 60);
    const s = secs % 60;
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

const selectOption = (questionId, optionIndex) => {
    selectedAnswers.value[questionId] = optionIndex;
};

// Auto Save to Redis
let autoSaveInterval = null;

const autoSaveDraft = async () => {
    try {
        await axios.post('/api/exam/autosave', {
            exam_id: props.exam.id,
            answers: selectedAnswers.value,
        });
    } catch (e) {}
};

// Submit Exam
const submitExam = (reason = '') => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(`/exam/${props.exam.id}/submit`, {
        answers: selectedAnswers.value,
        reason: reason,
    });
};

// Anti Cheat Composable Listener
const { warningCount, maxWarnings, isWarningModalOpen, closeWarningModal } = useExamAntiCheat((reason) => {
    submitExam(reason);
});

onMounted(() => {
    timerInterval = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--;
        } else {
            clearInterval(timerInterval);
            submitExam('সমাপ্তির সময় পার হয়ে গেছে।');
        }
    }, 1000);

    autoSaveInterval = setInterval(autoSaveDraft, 30000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (autoSaveInterval) clearInterval(autoSaveInterval);
});
</script>

<template>
    <ContentProtection>
        <div class="min-h-screen bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 flex flex-col font-sans">
            <WatermarkOverlay />

            <!-- Sticky Top Fullscreen Timer Navbar -->
            <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-gray-200 dark:border-slate-800 py-3 px-6 shadow-sm">
                <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">{{ chapter?.title || 'অনলাইন কুইজ ও পরীক্ষা' }}</span>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-slate-100 truncate">{{ exam.title }}</h2>
                    </div>

                    <!-- Mode Toggle & Countdown Clock -->
                    <div class="flex items-center space-x-4">
                        <button
                            @click="isPracticeMode = !isPracticeMode"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1 border"
                            :class="isPracticeMode ? 'bg-emerald-50 dark:bg-emerald-950 text-emerald-600 border-emerald-300' : 'bg-gray-100 text-gray-600'"
                        >
                            <Sparkles class="w-3.5 h-3.5" />
                            <span>{{ isPracticeMode ? 'ইনস্ট্যান্ট লার্নিং মোড: অন' : 'ইনস্ট্যান্ট লার্নিং মোড: অফ' }}</span>
                        </button>

                        <div class="flex items-center space-x-2 px-4 py-2 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 rounded-full text-rose-600 dark:text-rose-400 font-extrabold text-sm">
                            <Clock class="w-4 h-4 animate-pulse" />
                            <span>{{ formatTime(remainingSeconds) }}</span>
                        </div>

                        <button
                            @click="submitExam('ব্যবহারকারী সাবমিট করেছেন')"
                            :disabled="isSubmitting"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-emerald-600/20"
                        >
                            ফলাফল দেখুন
                        </button>
                    </div>
                </div>
            </header>

            <!-- Exam Questions Body -->
            <main class="flex-1 max-w-4xl w-full mx-auto p-6 md:p-8 space-y-6">
                <div v-if="!questions || questions.length === 0" class="p-12 text-center bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700">
                    <HelpCircle class="w-12 h-12 text-amber-500 mx-auto mb-3" />
                    <h3 class="text-xl font-bold">এই অধ্যায়ে এখনও প্রশ্ন যুক্ত করা হয়নি।</h3>
                    <p class="text-xs text-gray-500 mt-1">এডমিন ড্যাশবোর্ড থেকে প্রশ্ন যুক্ত করুন অথবা এআই প্রশ্ন জেনারেট করুন।</p>
                </div>

                <div
                    v-for="(q, index) in questions"
                    :key="q.id"
                    class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6"
                >
                    <div class="flex items-start space-x-3">
                        <span class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-sm flex items-center justify-center shrink-0">
                            {{ index + 1 }}
                        </span>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 leading-relaxed font-bengali">
                            {{ q.question_text }}
                        </h3>
                    </div>

                    <!-- Options Grid with Instant Learning Feedback & Click Handler -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div
                            v-for="(opt, optIdx) in q.options"
                            :key="optIdx"
                            @click="selectOption(q.id, optIdx)"
                            class="p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between select-none"
                            :class="[
                                isPracticeMode && selectedAnswers[q.id] !== undefined
                                    ? optIdx === Number(q.correct_option_index)
                                        ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-500 text-emerald-700 dark:text-emerald-300 font-bold shadow-sm'
                                        : selectedAnswers[q.id] === optIdx
                                            ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-500 text-rose-700 dark:text-rose-300 font-bold'
                                            : 'bg-gray-50/50 dark:bg-slate-900/40 border-gray-200 dark:border-slate-700 opacity-60'
                                    : selectedAnswers[q.id] === optIdx
                                        ? 'bg-indigo-50 dark:bg-indigo-950/60 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-bold shadow-sm'
                                        : 'bg-gray-50/50 dark:bg-slate-900/40 border-gray-200 dark:border-slate-700 hover:border-indigo-400'
                            ]"
                        >
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-xs shrink-0 font-bold"
                                    :class="[
                                        isPracticeMode && selectedAnswers[q.id] !== undefined
                                            ? optIdx === Number(q.correct_option_index)
                                                ? 'border-emerald-600 bg-emerald-600 text-white'
                                                : selectedAnswers[q.id] === optIdx
                                                    ? 'border-rose-600 bg-rose-600 text-white'
                                                    : 'border-gray-300 dark:border-slate-600'
                                            : selectedAnswers[q.id] === optIdx ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-slate-600'
                                    ]"
                                >
                                    <span v-if="selectedAnswers[q.id] === optIdx || (isPracticeMode && selectedAnswers[q.id] !== undefined && optIdx === Number(q.correct_option_index))">✓</span>
                                </div>
                                <span class="text-sm font-bengali">{{ opt }}</span>
                            </div>

                            <!-- Indicator Badge for Instant Learning -->
                            <div v-if="isPracticeMode && selectedAnswers[q.id] !== undefined" class="text-xs font-bold shrink-0">
                                <span v-if="optIdx === Number(q.correct_option_index)" class="text-emerald-600 flex items-center space-x-1">
                                    <Check class="w-4 h-4" />
                                    <span>সঠিক উত্তর</span>
                                </span>
                                <span v-else-if="selectedAnswers[q.id] === optIdx" class="text-rose-600 flex items-center space-x-1">
                                    <X class="w-4 h-4" />
                                    <span>ভুল উত্তর</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Detailed Explanation Box -->
                    <div
                        v-if="isPracticeMode && selectedAnswers[q.id] !== undefined"
                        class="p-5 bg-gradient-to-r from-indigo-50/80 to-purple-50/80 dark:from-indigo-950/40 dark:to-purple-950/40 border border-indigo-200 dark:border-indigo-900 rounded-2xl space-y-1.5"
                    >
                        <div class="flex items-center space-x-2 text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                            <HelpCircle class="w-4 h-4 text-indigo-500" />
                            <span>💡 উত্তর সম্পর্কিত বিস্তারিত ব্যাখ্যা (Explanation):</span>
                        </div>
                        <p class="text-xs text-gray-700 dark:text-slate-300 font-bengali leading-relaxed">
                            {{ q.explanation || 'এই প্রশ্নের জন্য কোনো বিশেষ ব্যাখ্যা প্রয়োজন নেই।' }}
                        </p>
                    </div>
                </div>
            </main>

            <!-- Anti-Cheat Tab Switch Warning Modal -->
            <div v-if="isWarningModalOpen" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl max-w-md w-full border border-rose-200 dark:border-rose-900 text-center shadow-2xl space-y-4">
                    <div class="w-16 h-16 bg-rose-100 dark:bg-rose-950 text-rose-600 rounded-full flex items-center justify-center mx-auto">
                        <AlertTriangle class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100">সতর্কবার্তা! উইন্ডো পরিবর্তন শনাক্ত হয়েছে</h3>
                    <p class="text-sm text-gray-600 dark:text-slate-300">
                        পরীক্ষা চলাকালীন ট্যাব পরিবর্তন বা ব্রাউজার মিনিমাইজ করা নিষেধ। আপনি <strong>{{ warningCount }} / {{ maxWarnings }}</strong> বার নিয়ম ভঙ্গ করেছেন। ৩ বারে পরীক্ষা অটো-সাবমিট হয়ে যাবে।
                    </p>
                    <button
                        @click="closeWarningModal"
                        class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-sm transition"
                    >
                        বুঝেছি, পরীক্ষায় ফিরে যান
                    </button>
                </div>
            </div>
        </div>
    </ContentProtection>
</template>
