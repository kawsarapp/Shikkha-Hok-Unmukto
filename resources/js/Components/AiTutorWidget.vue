<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Sparkles, Send, Bot, User, X, MessageSquare, Copy, Check, HelpCircle } from 'lucide-vue-next';

const isOpen = ref(false);
const inputQuery = ref('');
const isLoading = ref(false);
const copiedIndex = ref(null);

const messages = ref([
    {
        sender: 'ai',
        text: 'সালাম! আমি আপনার ২৪/৭ বিসিএস ও সরকারি চাকরি ক্যাডার প্রস্তুতি এআই টিউটর। বিসিএস প্রশ্ন, ম্যাথ শর্টকাট বা ব্যাকরণের যেকোনো সমস্যা আমাকে লিখে জানান!',
    },
]);

const suggestions = [
    '💡 বীজগণিতের শর্টকাট টেকনিক বলো',
    '📜 চর্যাপদ আবিষ্কারের ইতিহাস সংক্ষেপে বলো',
    '📝 Right form of verbs এর সহজ ৩টি নিয়ম',
    '📐 ল.সা.গু ও গ.সা.গু চেনার উপায় কী?',
];

const askQuery = async (customText = null) => {
    const textToAsk = customText || inputQuery.value;
    if (!textToAsk.trim() || isLoading.value) return;

    messages.value.push({ sender: 'user', text: textToAsk });
    if (!customText) inputQuery.value = '';
    isLoading.value = true;

    try {
        const res = await axios.post('/api/ai/doubt', {
            message: textToAsk,
        });
        messages.value.push({ sender: 'ai', text: res.data.reply });
    } catch (e) {
        messages.value.push({
            sender: 'ai',
            text: '⚠️ দুঃখিত, সাময়িক সংযোগ সমস্যা হচ্ছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
        });
    } finally {
        isLoading.value = false;
    }
};

const copyText = (text, idx) => {
    navigator.clipboard.writeText(text);
    copiedIndex.value = idx;
    setTimeout(() => {
        copiedIndex.value = null;
    }, 2000);
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-[9999]">
        <!-- Floating AI Tutor Trigger Button -->
        <button
            v-if="!isOpen"
            @click="isOpen = true"
            type="button"
            class="px-4 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-full shadow-2xl flex items-center space-x-2 transition-all duration-300 hover:scale-105 group border border-white/20"
        >
            <div class="relative">
                <Bot class="w-6 h-6 animate-pulse" />
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-slate-900"></span>
            </div>
            <span class="font-extrabold text-xs tracking-wide font-bengali hidden sm:inline">২৪/৭ AI বিসিএস টিউটর</span>
            <Sparkles class="w-4 h-4 text-amber-300 group-hover:rotate-12 transition-transform" />
        </button>

        <!-- Glassmorphic AI Tutor Chat Window -->
        <div
            v-else
            class="w-[360px] sm:w-[420px] h-[540px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-gray-200 dark:border-slate-700 rounded-3xl shadow-2xl flex flex-col overflow-hidden transition-all duration-300 animate-in fade-in slide-in-from-bottom-5"
        >
            <!-- Chat Window Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white flex items-center justify-between shadow-md">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20">
                        <Bot class="w-6 h-6 text-amber-300" />
                    </div>
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <h3 class="font-extrabold text-sm font-bengali">AI বিসিএস ডাউট সলভার</h3>
                            <span class="px-1.5 py-0.5 bg-emerald-500/30 text-emerald-200 text-[9px] font-bold rounded-md uppercase">২৪/৭ অনলাইন</span>
                        </div>
                        <p class="text-[11px] text-white/80 font-bengali">যেকোনো প্রশ্ন লিখে উত্তর বুঝে নিন</p>
                    </div>
                </div>

                <button @click="isOpen = false" class="p-1.5 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Chat Messages Scroll Container -->
            <div class="flex-1 p-4 overflow-y-auto space-y-4 font-bengali text-xs">
                <div
                    v-for="(msg, idx) in messages"
                    :key="idx"
                    class="flex items-start space-x-2"
                    :class="msg.sender === 'user' ? 'flex-row-reverse space-x-reverse' : ''"
                >
                    <!-- Avatar -->
                    <div
                        class="w-7 h-7 rounded-xl flex items-center justify-center font-bold text-white flex-shrink-0 shadow-sm"
                        :class="msg.sender === 'user' ? 'bg-indigo-600' : 'bg-purple-600'"
                    >
                        <User v-if="msg.sender === 'user'" class="w-4 h-4" />
                        <Bot v-else class="w-4 h-4 text-amber-300" />
                    </div>

                    <!-- Message Bubble -->
                    <div class="group relative max-w-[82%]">
                        <div
                            class="p-3.5 rounded-2xl shadow-sm leading-relaxed whitespace-pre-wrap"
                            :class="[
                                msg.sender === 'user'
                                    ? 'bg-indigo-600 text-white rounded-tr-none font-medium'
                                    : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 border border-gray-200/60 dark:border-slate-700 rounded-tl-none'
                            ]"
                        >
                            {{ msg.text }}
                        </div>

                        <!-- Copy Button for AI Answers -->
                        <button
                            v-if="msg.sender === 'ai' && idx > 0"
                            @click="copyText(msg.text, idx)"
                            class="mt-1 text-[10px] text-gray-400 hover:text-indigo-600 flex items-center space-x-1 font-semibold"
                        >
                            <Check v-if="copiedIndex === idx" class="w-3 h-3 text-emerald-500" />
                            <Copy v-else class="w-3 h-3" />
                            <span>{{ copiedIndex === idx ? 'কপি হয়েছে' : 'কপি করুন' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div v-if="isLoading" class="flex items-center space-x-2 text-purple-600 dark:text-purple-400 font-bold p-2 bg-purple-50 dark:bg-purple-950/40 rounded-2xl w-max border border-purple-200 dark:border-purple-900">
                    <Sparkles class="w-4 h-4 animate-spin" />
                    <span>এআই টিউটর উত্তর তৈরি করছে...</span>
                </div>
            </div>

            <!-- Quick Suggestion Pills -->
            <div class="px-3 py-2 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 flex items-center space-x-1.5 overflow-x-auto scrollbar-none font-bengali">
                <button
                    v-for="(sug, i) in suggestions"
                    :key="i"
                    @click="askQuery(sug)"
                    type="button"
                    class="px-2.5 py-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-300 rounded-xl text-[10px] font-bold whitespace-nowrap hover:bg-indigo-50 dark:hover:bg-slate-700 transition"
                >
                    {{ sug }}
                </button>
            </div>

            <!-- Input Form Box -->
            <form @submit.prevent="askQuery()" class="p-3 bg-white dark:bg-slate-900 border-t border-gray-200 dark:border-slate-800 flex items-center space-x-2 font-bengali">
                <input
                    v-model="inputQuery"
                    type="text"
                    placeholder="যেমন: সমাসের প্রকারভেদ সংক্ষেপে বলো..."
                    class="flex-1 px-3.5 py-2.5 bg-gray-100 dark:bg-slate-800 border-none rounded-xl text-xs focus:ring-2 focus:ring-purple-500 dark:text-slate-100"
                />
                <button
                    type="submit"
                    :disabled="isLoading || !inputQuery.trim()"
                    class="p-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl shadow-md transition disabled:opacity-50"
                >
                    <Send class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>
</template>
