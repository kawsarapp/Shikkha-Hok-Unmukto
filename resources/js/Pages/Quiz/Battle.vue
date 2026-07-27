<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Swords, Trophy, Clock, CheckCircle2, XCircle, ArrowRight, RotateCw, Coins, Sparkles, Zap, Lightbulb, Filter, Layers } from 'lucide-vue-next';

const props = defineProps({
    questions: Array,
    opponent: Object,
    user: Object,
    selectedCount: Number,
    selectedSubject: String,
});

const currentQuestionIndex = ref(0);
const userScore = ref(0);
const opponentScore = ref(0);
const selectedOption = ref(null);
const isQuestionAnswered = ref(false);
const isBattleCompleted = ref(false);
const battleResult = ref(null);

// Lifeline / Power-up states
const is5050Used = ref(false);
const hiddenOptions = ref([]);
const isHintUsed = ref(false);
const showHint = ref(false);

const timerSeconds = ref(15);
let timerInterval = null;

const startTimer = () => {
    timerSeconds.value = 15;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (timerSeconds.value > 0) {
            timerSeconds.value--;
        } else {
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

    // Simulated opponent answer logic
    const opponentCorrect = Math.random() < 0.7;
    if (opponentCorrect) {
        opponentScore.value++;
    }

    setTimeout(() => {
        if (currentQuestionIndex.value + 1 < props.questions.length) {
            currentQuestionIndex.value++;
            selectedOption.value = null;
            isQuestionAnswered.value = false;
            hiddenOptions.value = [];
            showHint.value = false;
            startTimer();
        } else {
            finishBattle();
        }
    }, 2000);
};

const use5050 = () => {
    if (is5050Used.value || isQuestionAnswered.value) return;
    is5050Used.value = true;
    const currentQ = props.questions[currentQuestionIndex.value];
    const correctIdx = Number(currentQ?.correct_option_index);
    const wrongIndices = [];
    currentQ.options.forEach((_, idx) => {
        if (idx !== correctIdx) wrongIndices.push(idx);
    });
    // Shuffle and pick 2 wrong indices to hide
    wrongIndices.sort(() => Math.random() - 0.5);
    hiddenOptions.value = wrongIndices.slice(0, 2);
};

const useHint = () => {
    if (isHintUsed.value) return;
    isHintUsed.value = true;
    showHint.value = true;
};

const changeBattleSettings = (count, subject) => {
    router.get('/battle', {
        count: count || props.selectedCount || 5,
        subject: subject || props.selectedSubject || 'all',
    }, { preserveState: false });
};

const finishBattle = async () => {
    isBattleCompleted.value = true;
    try {
        const res = await axios.post('/battle/submit', {
            user_score: userScore.value,
            opponent_score: opponentScore.value,
            total_questions: props.questions.length,
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
            <!-- Custom Question Count & Subject Selection Settings Bar -->
            <div class="p-4 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl shadow-sm space-y-3 font-bengali text-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-slate-700 pb-2">
                    <span class="font-extrabold text-indigo-700 dark:text-indigo-400 flex items-center space-x-1">
                        <Filter class="w-4 h-4" />
                        <span>কুইজ প্রশ্ন সংখ্যা নির্বাচন করুন:</span>
                    </span>
                    <div class="flex items-center space-x-1.5">
                        <button
                            v-for="c in [5, 10, 15, 20, 25]"
                            :key="c"
                            @click="changeBattleSettings(c, selectedSubject)"
                            type="button"
                            class="px-3 py-1 rounded-xl font-black transition"
                            :class="selectedCount === c ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-200'"
                        >
                            {{ c }}টি
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-extrabold text-purple-700 dark:text-purple-400 flex items-center space-x-1">
                        <Layers class="w-4 h-4" />
                        <span>বিষয় নির্বাচন:</span>
                    </span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="subj in [
                                { id: 'all', label: 'সকল বিষয়' },
                                { id: 'বাংলা সাহিত্য ও ভাষা', label: 'বাংলা' },
                                { id: 'English Language & Literature', label: 'ইংরেজি' },
                                { id: 'গাণিতিক যুক্তি ও মানসিক দক্ষতা', label: 'গণিত' },
                                { id: 'বাংলাদেশ বিষয়াবলি', label: 'বাংলাদেশ' },
                                { id: 'সাধারণ বিজ্ঞান ও তথ্যপ্রযুক্তি', label: 'বিজ্ঞান' },
                            ]"
                            :key="subj.id"
                            @click="changeBattleSettings(selectedCount, subj.id)"
                            type="button"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition"
                            :class="selectedSubject === subj.id ? 'bg-purple-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-200'"
                        >
                            {{ subj.label }}
                        </button>
                    </div>
                </div>
            </div>

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

            <!-- Question Progress, Lifelines & Timer Bar -->
            <div v-if="!isBattleCompleted && questions.length > 0" class="flex flex-wrap items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm text-xs font-bold gap-2">
                <span class="text-indigo-600 dark:text-indigo-400">প্রশ্ন {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>

                <!-- Lifelines / Power-ups Buttons -->
                <div class="flex items-center space-x-2">
                    <button
                        @click="use5050"
                        :disabled="is5050Used || isQuestionAnswered"
                        type="button"
                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl font-extrabold flex items-center space-x-1 shadow-sm disabled:opacity-40"
                    >
                        <Zap class="w-3.5 h-3.5 fill-slate-950" />
                        <span>50:50 {{ is5050Used ? '(ব্যবহৃত)' : '' }}</span>
                    </button>

                    <button
                        @click="useHint"
                        :disabled="isHintUsed"
                        type="button"
                        class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-extrabold flex items-center space-x-1 shadow-sm disabled:opacity-40"
                    >
                        <Lightbulb class="w-3.5 h-3.5 fill-amber-300" />
                        <span>AI ইঙ্গিত</span>
                    </button>
                </div>

                <!-- Timer -->
                <div class="flex items-center space-x-1.5 px-3 py-1 bg-rose-50 dark:bg-rose-950 text-rose-600 border border-rose-200 rounded-full">
                    <Clock class="w-4 h-4 animate-spin-slow" />
                    <span>সময়: {{ timerSeconds }} সে</span>
                </div>
            </div>

            <!-- AI Hint Callout Box -->
            <div v-if="showHint && questions[currentQuestionIndex]?.explanation" class="p-4 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-900 rounded-2xl text-purple-900 dark:text-purple-200 text-xs space-y-1 animate-in fade-in">
                <span class="font-extrabold flex items-center space-x-1 text-purple-700 dark:text-purple-300">
                    <Lightbulb class="w-4 h-4 text-amber-400 fill-amber-400" />
                    <span>AI লার্নিং ইঙ্গিত:</span>
                </span>
                <p class="font-bengali">{{ questions[currentQuestionIndex].explanation }}</p>
            </div>

            <!-- Question Arena Card -->
            <div v-if="!isBattleCompleted && questions.length > 0" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 dark:text-slate-100 font-bengali leading-relaxed">
                    {{ questions[currentQuestionIndex]?.question_text }}
                </h2>

                <!-- Options Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button
                        v-for="(opt, optIdx) in questions[currentQuestionIndex]?.options"
                        :key="optIdx"
                        @click="handleAnswer(optIdx)"
                        :disabled="isQuestionAnswered || hiddenOptions.includes(optIdx)"
                        class="p-4 rounded-2xl border text-left font-bengali text-sm font-semibold transition-all duration-200 flex items-center justify-between"
                        :class="[
                            hiddenOptions.includes(optIdx)
                                ? 'opacity-20 bg-gray-100 border-gray-200 pointer-events-none line-through'
                                : isQuestionAnswered
                                ? optIdx === Number(questions[currentQuestionIndex]?.correct_option_index)
                                    ? 'bg-emerald-500 text-white border-emerald-600 shadow-md font-bold'
                                    : selectedOption === optIdx
                                        ? 'bg-rose-500 text-white border-rose-600 font-bold'
                                        : 'bg-gray-100 dark:bg-slate-900 opacity-50 border-gray-200'
                                : 'bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 hover:border-indigo-500 hover:bg-indigo-50/50'
                        ]"
                    >
                        <span>{{ hiddenOptions.includes(optIdx) ? '❌ অপশন বাতিল' : opt }}</span>
                        <span v-if="isQuestionAnswered && optIdx === Number(questions[currentQuestionIndex]?.correct_option_index)" class="text-xs bg-white text-emerald-700 px-2 py-0.5 rounded-md font-bold">✓ সঠিক</span>
                    </button>
                </div>
            </div>

            <!-- Victory / Defeat Modal -->
            <div v-if="isBattleCompleted" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-2xl text-center space-y-6 font-bengali">
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto text-4xl shadow-xl" :class="battleResult?.is_winner ? 'bg-amber-100 text-amber-500 border-4 border-amber-300 animate-bounce' : 'bg-gray-100 text-gray-400'">
                    {{ battleResult?.is_winner ? '🏆' : '🤝' }}
                </div>

                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-600 block">১ বনাম ১ কুইজ ব্যাটল সমাপ্ত</span>
                    <h2 class="text-3xl font-black mt-1" :class="battleResult?.is_winner ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-800 dark:text-slate-200'">
                        {{ battleResult?.is_winner ? '🎉 অভিনন্দন! আপনি বিজয়ী হয়েছেন!' : 'চমৎকার লড়াই করেছেন!' }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">আপনার ফাইনাল স্কোর: {{ userScore }}/{{ questions.length }} • {{ opponent.name }}: {{ opponentScore }}/{{ questions.length }}</p>
                </div>

                <div class="p-4 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900 rounded-2xl inline-flex items-center space-x-2 text-amber-700 dark:text-amber-300 font-bold text-sm">
                    <Coins class="w-5 h-5 fill-amber-400 text-amber-500" />
                    <span>+{{ battleResult?.earned_coins || 30 }} কয়েন বোনাস অর্জিত!</span>
                </div>

                <div class="flex items-center justify-center space-x-4 pt-4">
                    <Link href="/dashboard" class="px-6 py-3 bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200 rounded-xl text-xs font-bold">
                        ড্যাশবোর্ড
                    </Link>
                    <button @click="changeBattleSettings(selectedCount, selectedSubject)" type="button" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/20">
                        পুনরায় ব্যাটল খেলুন ⚔️
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
