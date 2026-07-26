<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Swords, Trophy, Clock, CheckCircle2, XCircle, ArrowRight, RotateCw, Coins, Sparkles } from 'lucide-vue-next';

const props = defineProps({
    questions: Array,
    opponent: Object,
    user: Object,
});

const currentQuestionIndex = ref(0);
const userScore = ref(0);
const opponentScore = ref(0);
const selectedOption = ref(null);
const isQuestionAnswered = ref(false);
const isBattleCompleted = ref(false);
const battleResult = ref(null);

const timerSeconds = ref(15);
let timerInterval = null;

const startTimer = () => {
    timerSeconds.value = 15;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (timerSeconds.value > 0) {
            timerSeconds.value--;
        } else {
            // Time out auto next
            handleAnswer(-1);
        }
    }, 1000);
};

const handleAnswer = (optionIdx) => {
    if (isQuestionAnswered.value) return;
    isQuestionAnswered.value = true;
    selectedOption.value = optionIdx;
    if (timerInterval) clearInterval(timerInterval);

    const currentQ = props.questions[currentQuestionIndex.value];
    const isCorrect = optionIdx === Number(currentQ?.correct_option_index);

    if (isCorrect) {
        userScore.value++;
    }

    // AI opponent simulated answer logic (70% chance of correct)
    const opponentCorrect = Math.random() < 0.7;
    if (opponentCorrect) {
        opponentScore.value++;
    }

    setTimeout(() => {
        if (currentQuestionIndex.value + 1 < props.questions.length) {
            currentQuestionIndex.value++;
            selectedOption.value = null;
            isQuestionAnswered.value = false;
            startTimer();
        } else {
            finishBattle();
        }
    }, 2000);
};

const finishBattle = async () => {
    isBattleCompleted.value = true;
    try {
        const res = await axios.post('/battle/submit', {
            user_score: userScore.value,
            opponent_score: opponentScore.value,
        });
        battleResult.value = res.data;
    } catch (e) {
        battleResult.value = {
            is_winner: userScore.value >= opponentScore.value,
            earned_coins: userScore.value >= opponentScore.value ? 30 : 5,
        };
    }
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Battle Header Arena Bar -->
            <div class="p-6 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl flex items-center justify-between gap-4">
                <!-- Player Profile -->
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg border-2 border-indigo-400">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div>
                        <span class="text-xs text-indigo-300 font-bold block">আপনি</span>
                        <h3 class="font-extrabold text-sm md:text-base">{{ user.name }}</h3>
                        <span class="px-2.5 py-0.5 bg-indigo-500/30 text-indigo-200 font-black text-xs rounded-full inline-block mt-0.5">
                            স্কোর: {{ userScore }}
                        </span>
                    </div>
                </div>

                <!-- VS Badge -->
                <div class="text-center shrink-0">
                    <div class="w-12 h-12 rounded-full bg-rose-600 text-white flex items-center justify-center font-black shadow-lg shadow-rose-600/40 animate-pulse">
                        <Swords class="w-6 h-6" />
                    </div>
                    <span class="text-[10px] uppercase font-black text-rose-300 tracking-wider block mt-1">1v1 ব্যাটল</span>
                </div>

                <!-- Opponent Profile -->
                <div class="flex items-center space-x-3 text-right">
                    <div>
                        <span class="text-xs text-rose-300 font-bold block">প্রতিদ্বন্দ্বী</span>
                        <h3 class="font-extrabold text-sm md:text-base">{{ opponent.name }}</h3>
                        <span class="px-2.5 py-0.5 bg-rose-500/30 text-rose-200 font-black text-xs rounded-full inline-block mt-0.5">
                            স্কোর: {{ opponentScore }}
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-900 text-white flex items-center justify-center text-2xl border-2 border-rose-500">
                        {{ opponent.avatar }}
                    </div>
                </div>
            </div>

            <!-- Question Progress & Timer Bar -->
            <div v-if="!isBattleCompleted && questions.length > 0" class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm text-xs font-bold">
                <span class="text-indigo-600 dark:text-indigo-400">প্রশ্ন {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                <div class="flex items-center space-x-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950 text-amber-600 border border-amber-200 rounded-full">
                    <Clock class="w-4 h-4 animate-spin-slow" />
                    <span>সময়: {{ timerSeconds }} সে</span>
                </div>
            </div>

            <!-- Question Arena Card -->
            <div v-if="!isBattleCompleted && questions.length > 0" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 dark:text-slate-100 font-bengali leading-relaxed">
                    {{ questions[currentQuestionIndex]?.question_text }}
                </h2>

                <!-- Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button
                        v-for="(opt, optIdx) in questions[currentQuestionIndex]?.options"
                        :key="optIdx"
                        @click="handleAnswer(optIdx)"
                        :disabled="isQuestionAnswered"
                        class="p-4 rounded-2xl border text-left font-bengali text-sm font-semibold transition-all duration-200 flex items-center justify-between"
                        :class="[
                            isQuestionAnswered
                                ? optIdx === Number(questions[currentQuestionIndex]?.correct_option_index)
                                    ? 'bg-emerald-500 text-white border-emerald-600 shadow-md font-bold'
                                    : selectedOption === optIdx
                                        ? 'bg-rose-500 text-white border-rose-600 font-bold'
                                        : 'bg-gray-100 dark:bg-slate-900 opacity-50 border-gray-200'
                                : 'bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 hover:border-indigo-500 hover:bg-indigo-50/50'
                        ]"
                    >
                        <span>{{ opt }}</span>
                        <span v-if="isQuestionAnswered && optIdx === Number(questions[currentQuestionIndex]?.correct_option_index)" class="text-xs bg-white text-emerald-700 px-2 py-0.5 rounded-md font-bold">✓ সঠিক</span>
                    </button>
                </div>
            </div>

            <!-- Victory / Defeat Modal -->
            <div v-if="isBattleCompleted" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-2xl text-center space-y-6">
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto text-4xl shadow-xl" :class="battleResult?.is_winner ? 'bg-amber-100 text-amber-500 border-4 border-amber-300 animate-bounce' : 'bg-gray-100 text-gray-400'">
                    {{ battleResult?.is_winner ? '🏆' : '🤝' }}
                </div>

                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-600">১ বনাম ১ কুইজ ব্যাটল সমাপ্ত</span>
                    <h2 class="text-3xl font-black mt-1" :class="battleResult?.is_winner ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-800 dark:text-slate-200'">
                        {{ battleResult?.is_winner ? '🎉 অভিনন্দন! আপনি বিজয়ী হয়েছেন!' : 'চমৎকার লড়াই করেছেন!' }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">আপনার ফাইনাল স্কোর: {{ userScore }}/৫ • {{ opponent.name }}: {{ opponentScore }}/৫</p>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900 rounded-2xl inline-flex items-center space-x-2 text-amber-700 dark:text-amber-300 font-bold text-sm">
                    <Coins class="w-5 h-5 fill-amber-400 text-amber-500" />
                    <span>+{{ battleResult?.earned_coins || 30 }} কাস্টম কয়েন বোনাস অর্জিত!</span>
                </div>

                <div class="flex items-center justify-center space-x-4 pt-4">
                    <Link href="/dashboard" class="px-6 py-3 bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-xl text-xs font-bold">
                        ড্যাশবোর্ড
                    </Link>
                    <Link href="/battle" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/20">
                        পুনরায় ব্যাটল খেলুন ⚔️
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
