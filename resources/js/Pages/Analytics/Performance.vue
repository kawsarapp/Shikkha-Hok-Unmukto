<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { BarChart2, TrendingUp, AlertTriangle, CheckCircle, HelpCircle, Sparkles, BookOpen } from 'lucide-vue-next';

const props = defineProps({
    subjectStats: Object,
    totalAttemptsCount: Number,
    user: Object,
});
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-8">
            <!-- Header Banner -->
            <div class="p-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-indigo-500/30 border border-indigo-400/30 rounded-full text-indigo-200 text-xs font-semibold flex items-center space-x-1 w-fit">
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>এআই বিষায়ক দুর্বলতা ট্র্যাকার</span>
                    </span>
                    <h1 class="text-3xl font-extrabold mt-2">Subject Performance & Weakness Analytics</h1>
                    <p class="text-xs text-indigo-200 mt-1">আপনার প্রতি বিষয়ের সঠিক উত্তরের হার, স্ট্রেন্থ ও দুর্বলতা বিশ্লেষণ।</p>
                </div>

                <div class="bg-white/10 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20 text-center shrink-0">
                    <span class="text-xs text-indigo-200 font-bold block">মোট সম্পন্ন মডেল টেস্ট</span>
                    <span class="text-3xl font-black text-emerald-400">{{ totalAttemptsCount }}টি</span>
                </div>
            </div>

            <!-- Subject Analytics Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="(stat, subjName) in subjectStats"
                    :key="subjName"
                    class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-4"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 flex items-center space-x-2">
                            <BookOpen class="w-5 h-5 text-indigo-600" />
                            <span>{{ subjName }}</span>
                        </h3>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-extrabold"
                            :class="[
                                stat.percentage >= 80 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                                stat.percentage >= 60 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                                'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                            ]"
                        >
                            {{ stat.status }}
                        </span>
                    </div>

                    <!-- Progress Bar & Percentage -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs font-bold">
                            <span class="text-gray-500">নির্ভুলতার হার (Accuracy Rate):</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ stat.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-slate-700 h-3 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="[
                                    stat.percentage >= 80 ? 'bg-emerald-500' :
                                    stat.percentage >= 60 ? 'bg-amber-500' :
                                    'bg-rose-500'
                                ]"
                                :style="{ width: stat.percentage + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- Total Q Answers Breakdown -->
                    <div class="p-3 bg-gray-50/80 dark:bg-slate-900/50 rounded-2xl flex items-center justify-between text-xs font-semibold text-gray-600 dark:text-slate-300">
                        <span>মোট উত্তর দেওয়া প্রশ্ন: {{ stat.total }}টি</span>
                        <span class="text-emerald-600 font-bold">সঠিক: {{ stat.correct }}টি</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
