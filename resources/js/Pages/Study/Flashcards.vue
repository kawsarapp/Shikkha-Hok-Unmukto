<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Sparkles, ArrowLeft, ArrowRight, RotateCw, CheckCircle, HelpCircle } from 'lucide-vue-next';

const props = defineProps({
    chapter: Object,
    questions: Array,
});

const currentIndex = ref(0);
const isFlipped = ref(false);

const nextCard = () => {
    if (currentIndex.value + 1 < props.questions.length) {
        currentIndex.value++;
        isFlipped.value = false;
    }
};

const prevCard = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
        isFlipped.value = false;
    }
};

const toggleFlip = () => {
    isFlipped.value = !isFlipped.value;
};
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header Navigation -->
            <div class="flex items-center justify-between">
                <Link :href="`/chapter/${chapter.id}`" class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center space-x-1">
                    <ArrowLeft class="w-4 h-4" />
                    <span>পড়া পৃষ্ঠায় ফিরুন</span>
                </Link>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                    কার্ড {{ currentIndex + 1 }} / {{ questions.length }}
                </span>
            </div>

            <!-- Clean Interactive Flip Card -->
            <div v-if="questions.length > 0">
                <div
                    @click="toggleFlip"
                    class="w-full min-h-[340px] border-2 rounded-3xl p-8 shadow-xl cursor-pointer transition-all duration-300 transform hover:scale-[1.01] flex flex-col justify-between select-none"
                    :class="[
                        isFlipped
                            ? 'bg-gradient-to-br from-emerald-500/10 via-white to-teal-500/10 dark:from-emerald-950/40 dark:via-slate-800 dark:to-slate-900 border-emerald-300 dark:border-emerald-700'
                            : 'bg-gradient-to-br from-indigo-500/10 via-white to-purple-500/10 dark:from-indigo-950/40 dark:via-slate-800 dark:to-slate-900 border-indigo-300 dark:border-indigo-700'
                    ]"
                >
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                        <span :class="isFlipped ? 'text-emerald-600 dark:text-emerald-400 flex items-center space-x-1' : 'text-indigo-600 dark:text-indigo-400 flex items-center space-x-1'">
                            <CheckCircle v-if="isFlipped" class="w-4 h-4" />
                            <HelpCircle v-else class="w-4 h-4" />
                            <span>{{ isFlipped ? '💡 সঠিক উত্তর ও বিস্তারিত ব্যাখ্যা' : '❓ প্রশ্ন (ক্লিক করে উত্তর দেখুন)' }}</span>
                        </span>
                        <div class="flex items-center space-x-1 text-gray-400 text-[11px]">
                            <RotateCw class="w-3.5 h-3.5" />
                            <span>চাপ দিন</span>
                        </div>
                    </div>

                    <!-- Front Side (Question) -->
                    <div v-if="!isFlipped" class="my-auto text-center space-y-4 py-8">
                        <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-slate-100 font-bengali leading-relaxed">
                            {{ questions[currentIndex]?.question_text }}
                        </h2>
                    </div>

                    <!-- Back Side (Answer & Explanation - Unmirrored, Clear Text) -->
                    <div v-else class="my-auto text-center space-y-5 py-6">
                        <div>
                            <span class="text-xs font-bold text-gray-400 block mb-1">সঠিক উত্তর:</span>
                            <div class="px-5 py-2.5 bg-emerald-600 text-white rounded-2xl inline-block font-extrabold text-base md:text-lg shadow-md shadow-emerald-600/20 font-bengali">
                                {{ questions[currentIndex]?.options[questions[currentIndex]?.correct_option_index] }}
                            </div>
                        </div>

                        <div class="p-4 bg-white/80 dark:bg-slate-900/80 rounded-2xl border border-emerald-200 dark:border-emerald-900 max-w-lg mx-auto">
                            <p class="text-xs text-gray-700 dark:text-slate-300 font-bengali leading-relaxed">
                                💡 <strong>ব্যাখ্যা:</strong> {{ questions[currentIndex]?.explanation || 'এই প্রশ্নের জন্য কোনো বিশেষ ব্যাখ্যা দেওয়া হয়নি।' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-center text-[11px] text-gray-400 font-semibold">
                        {{ isFlipped ? 'ক্লিক করে আবার প্রশ্ন দেখুন' : 'উত্তর ও কারণ দেখতে এখানে চাপ দিন' }}
                    </div>
                </div>
            </div>

            <div v-else class="p-12 text-center bg-white dark:bg-slate-800 rounded-3xl border">
                <p class="text-sm text-gray-500">এই অধ্যায়ে এখনও কোনো প্রশ্ন তৈরি করা হয়নি।</p>
            </div>

            <!-- Controls -->
            <div class="flex items-center justify-between">
                <button
                    @click="prevCard"
                    :disabled="currentIndex === 0"
                    class="px-6 py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-2 shadow-sm"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>আগের কার্টি</span>
                </button>

                <button
                    @click="nextCard"
                    :disabled="currentIndex + 1 >= questions.length"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-2 shadow-lg shadow-indigo-600/20"
                >
                    <span>পরের কার্টি</span>
                    <ArrowRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>
