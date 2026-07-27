<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useAudioStore } from '@/Stores/useAudioStore';
import { Trophy, CheckCircle, XCircle, Volume2, ArrowLeft, Award, Flame, Coins, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    attempt: Object,
    leaderboard: Array,
    userRank: Number,
});

const audioStore = useAudioStore();

const shareText = computed(() => {
    return encodeURIComponent(`আমি '${props.attempt?.exam?.title || 'বিসিএস কুইজ'}' এ ${props.attempt?.score} নম্বর পেয়েছি! তুমি কি আমাকে হারাতে পারবে? 🥊 চ্যালেঞ্জ নিতে লিংকে চাপ দাও:`);
});

const shareUrl = computed(() => {
    return encodeURIComponent(window.location.href);
});

const shareToWhatsApp = () => {
    window.open(`https://api.whatsapp.com/send?text=${shareText.value}%20${shareUrl.value}`, '_blank');
};

const shareToFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${shareUrl.value}`, '_blank');
};

const copyChallengeLink = () => {
    navigator.clipboard.writeText(window.location.href);
    alert('🎯 আপনার ইউনিক চ্যালেঞ্জ লিংক কপি হয়েছে! বন্ধুদের যেকোনো চ্যাটে সেন্ড করুন।');
};

const playExplanationAudio = (questionText, explanation) => {
    audioStore.play('ব্যাখ্যা শুনুন', `${questionText}। সঠিক উত্তর ও কারণ: ${explanation}`);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Result Summary & Celebration Card -->
            <div class="p-8 bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-800 text-white rounded-3xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <div class="space-y-3 z-10">
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 bg-indigo-500/30 border border-indigo-400/30 rounded-full text-indigo-200 text-xs font-semibold flex items-center space-x-1">
                            <Sparkles class="w-3.5 h-3.5 text-amber-300" />
                            <span>পরীক্ষার রেজাল্ট ও মেধা তালিকা</span>
                        </span>
                        <span class="px-3 py-1 bg-amber-500/30 border border-amber-400/30 rounded-full text-amber-300 text-xs font-bold flex items-center space-x-1">
                            <Coins class="w-3.5 h-3.5 fill-amber-400" />
                            <span>+১০ কয়েন অর্জিত!</span>
                        </span>
                    </div>

                    <h1 class="text-3xl font-extrabold">{{ attempt.exam?.title }}</h1>
                    
                    <!-- Rank Highlight Badge -->
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-amber-400 text-slate-950 font-black rounded-2xl text-sm shadow-lg">
                        <Trophy class="w-5 h-5 fill-slate-950" />
                        <span>আপনার মেধা অবস্থান: {{ userRank === 1 ? '🥇 মেধা তালিকায় ১ম স্থান!' : userRank === 2 ? '🥈 ২য় স্থান' : userRank === 3 ? '🥉 ৩য় স্থান' : 'র‍্যাংক #' + userRank }}</span>
                    </div>

                    <p class="text-xs text-indigo-200 block">জমা দেওয়া হয়েছে: {{ new Date(attempt.submitted_at).toLocaleString() }}</p>
                </div>

                <!-- Score & Accuracy Breakdown Pill -->
                <div class="flex flex-col items-center gap-4 bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 shrink-0 z-10">
                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <span class="block text-3xl font-black text-emerald-400">{{ attempt.score }}</span>
                            <span class="text-xs text-gray-300">অর্জিত নম্বর</span>
                        </div>
                        <div class="w-px h-10 bg-white/20"></div>
                        <div class="text-center">
                            <span class="block text-xl font-bold text-emerald-400">{{ attempt.correct_count }}টি</span>
                            <span class="text-xs text-gray-300">সঠিক উত্তর</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-xl font-bold text-rose-400">{{ attempt.wrong_count }}টি</span>
                            <span class="text-xs text-gray-300">ভুল উত্তর</span>
                        </div>
                    </div>

                    <!-- 1-Click Viral Challenge Friends Buttons -->
                    <div class="w-full pt-3 border-t border-white/10 flex flex-wrap items-center justify-center gap-2">
                        <span class="text-xs font-bold text-amber-300 w-full text-center">🥊 বন্ধুদের বিসিএস চ্যালেঞ্জ পাঠান:</span>
                        <button @click="shareToWhatsApp" type="button" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-xl flex items-center space-x-1 shadow-md">
                            <span>💬 WhatsApp শেয়ার</span>
                        </button>
                        <button @click="shareToFacebook" type="button" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl flex items-center space-x-1 shadow-md">
                            <span>📘 Facebook শেয়ার</span>
                        </button>
                        <button @click="copyChallengeLink" type="button" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white font-bold text-xs rounded-xl flex items-center space-x-1">
                            <span>🔗 লিংক কপি</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Question Review & Audio Explanations -->
            <div class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100 flex items-center space-x-2">
                    <CheckCircle class="w-5 h-5 text-indigo-600" />
                    <span>প্রশ্নোত্তর সমাধান ও সঠিক ব্যাখ্যামালা</span>
                </h3>

                <div v-for="(ans, i) in attempt.answers" :key="ans.id" class="p-5 rounded-2xl border border-gray-100 dark:border-slate-700/60 bg-gray-50/50 dark:bg-slate-900/40 space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                {{ i + 1 }}
                            </span>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-slate-100 font-bengali">{{ ans.question?.question_text }}</h4>
                                <div class="mt-2 text-xs space-y-1">
                                    <p :class="ans.is_correct ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold'">
                                        আপনার সিলেক্ট করা উত্তর: {{ ans.question?.options[ans.selected_option_index] || 'উত্তর দেওয়া হয়নি' }} {{ ans.is_correct ? '✓' : '✗' }}
                                    </p>
                                    <p class="text-emerald-600 dark:text-emerald-400 font-bold">
                                        সঠিক উত্তর: {{ ans.question?.options[ans.question.correct_option_index] }} ✓
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Audio Explanation Button -->
                        <button
                            v-if="ans.question?.explanation"
                            @click="playExplanationAudio(ans.question.question_text, ans.question.explanation)"
                            class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold flex items-center space-x-1 border border-indigo-200 dark:border-indigo-900 hover:bg-indigo-100 shrink-0"
                        >
                            <Volume2 class="w-3.5 h-3.5" />
                            <span>ব্যাখ্যা শুনুন</span>
                        </button>
                    </div>

                    <div v-if="ans.question?.explanation" class="p-4 bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/60 rounded-xl text-xs text-gray-700 dark:text-slate-300 font-bengali leading-relaxed">
                        💡 <strong>ব্যাখ্যা:</strong> {{ ans.question.explanation }}
                    </div>
                </div>
            </div>

            <!-- Instant Redis Live Leaderboard -->
            <div class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm">
                <div class="flex items-center space-x-2 mb-6">
                    <Trophy class="w-6 h-6 text-amber-500" />
                    <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100">মেধা তালিকা (Live Leaderboard Ranks)</h3>
                </div>

                <div v-if="leaderboard.length === 0" class="text-center py-6 text-sm text-gray-400">
                    এখনও কোনো র‍্যাংক রেকর্ড করা হয়নি।
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-slate-700 text-gray-400 uppercase tracking-wider font-bold">
                                <th class="py-3 px-4">মেধা স্থান</th>
                                <th class="py-3 px-4">শিক্ষার্থী</th>
                                <th class="py-3 px-4">স্ট্রিক</th>
                                <th class="py-3 px-4 text-right">স্কোর</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                            <tr
                                v-for="user in leaderboard"
                                :key="user.user_id"
                                class="hover:bg-gray-50/50 dark:hover:bg-slate-900/50"
                                :class="{ 'bg-amber-500/10 font-bold': user.user_id === attempt.user_id }"
                            >
                                <td class="py-3 px-4 font-black text-indigo-600 dark:text-indigo-400">
                                    <span v-if="user.rank === 1">🥇 ১ম স্থান</span>
                                    <span v-else-if="user.rank === 2">🥈 ২য় স্থান</span>
                                    <span v-else-if="user.rank === 3">🥉 ৩য় স্থান</span>
                                    <span v-else>#{{ user.rank }}</span>
                                </td>
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-slate-100">
                                    {{ user.name }}
                                    <span v-if="user.user_id === attempt.user_id" class="ml-2 text-[10px] bg-amber-500 text-white px-2 py-0.5 rounded-full font-bold">আপনি</span>
                                </td>
                                <td class="py-3 px-4 text-amber-500 font-bold">🔥 {{ user.study_streak }} দিন</td>
                                <td class="py-3 px-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm">{{ user.score }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="text-center">
                <Link href="/dashboard" class="inline-flex items-center space-x-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-indigo-600/20">
                    <ArrowLeft class="w-4 h-4" />
                    <span>ড্যাশবোর্ডে ফিরে যান</span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
