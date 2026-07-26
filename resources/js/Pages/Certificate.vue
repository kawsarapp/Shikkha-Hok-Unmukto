<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Award, CheckCircle, Printer, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    course: Object,
    user: Object,
    isCompleted: Boolean,
    passedCount: Number,
    totalChapters: Number,
    completedDate: String,
});

const printCertificate = () => {
    window.print();
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between no-print">
                <Link href="/dashboard" class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center space-x-1">
                    <ArrowLeft class="w-4 h-4" />
                    <span>ড্যাশবোর্ড</span>
                </Link>
                <button v-if="isCompleted" @click="printCertificate" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center space-x-2 shadow-lg shadow-indigo-600/20">
                    <Printer class="w-4 h-4" />
                    <span>সার্টিফিকেট প্রিন্ট / ডাউনলোড (PDF)</span>
                </button>
            </div>

            <!-- Certificate Paper Card -->
            <div v-if="isCompleted" class="bg-gradient-to-b from-amber-50/60 via-white to-indigo-50/40 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900 border-8 border-double border-amber-300 dark:border-amber-700/60 p-12 rounded-3xl shadow-2xl text-center space-y-6 relative overflow-hidden print:border-4 print:p-8">
                <!-- Watermark Badge Icon -->
                <div class="w-20 h-20 bg-amber-500/10 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Award class="w-12 h-12" />
                </div>

                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">এডুকেশন অলওয়েজ ফ্রি • অর্জন সনদপত্র</span>

                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-slate-100 font-serif">কোর্স সফলতার সনদপত্র</h1>

                <p class="text-sm text-gray-600 dark:text-slate-300 max-w-xl mx-auto leading-relaxed">
                    এতদ্বারা সততার সাথে সত্যায়িত করা যাচ্ছে যে,
                </p>

                <h2 class="text-3xl font-black text-indigo-600 dark:text-indigo-400 underline decoration-amber-400 font-bengali">
                    {{ user.name }}
                </h2>

                <p class="text-sm text-gray-600 dark:text-slate-300 max-w-xl mx-auto leading-relaxed">
                    সফলভাবে <strong>EducationAlwaysFree</strong> প্ল্যাটফর্মে <strong>'{{ course.title }}'</strong> কোর্সের অন্তর্ভুক্ত সকল অধ্যায় ও কুইজ সফলতার সাথে সম্পন্ন করেছেন।
                </p>

                <div class="pt-8 flex items-center justify-between max-w-md mx-auto border-t border-amber-200 dark:border-amber-800 text-xs">
                    <div>
                        <span class="block font-bold text-gray-800 dark:text-slate-200">{{ completedDate }}</span>
                        <span class="text-gray-400">সম্পন্ন করার তারিখ</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                        ✓ Verified
                    </div>
                    <div>
                        <span class="block font-bold text-indigo-600 dark:text-indigo-400">EducationAlwaysFree</span>
                        <span class="text-gray-400">অনুমোদিত প্রতিষ্ঠান</span>
                    </div>
                </div>
            </div>

            <div v-else class="p-8 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 rounded-3xl text-center space-y-4">
                <Award class="w-12 h-12 text-amber-500 mx-auto" />
                <h3 class="text-xl font-bold text-amber-800 dark:text-amber-200">সার্টিফিকেট এখনও আনলক হয়নি</h3>
                <p class="text-xs text-amber-700 dark:text-amber-300 max-w-md mx-auto">
                    সার্টিফিকেট অর্জন করতে কোর্সের সবকটি অধ্যায় সফলভাবে পড়ে পরীক্ষায় ৭০%+ নম্বর পেতে হবে। আপনার বর্তমান অগ্রগতি: {{ passedCount }} / {{ totalChapters }} অধ্যায়।
                </p>
            </div>
        </div>
    </AppLayout>
</template>
