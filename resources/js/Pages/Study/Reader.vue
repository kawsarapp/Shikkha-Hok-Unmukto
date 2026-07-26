<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useAudioStore } from '@/Stores/useAudioStore';
import axios from 'axios';
import { Volume2, Sparkles, Clock, CheckCircle, Send, MessageSquare, BookOpen, ArrowRight, ArrowLeft, Lock, FileText, Download, ChevronLeft, ChevronRight, Layers, Sun, Moon } from 'lucide-vue-next';

const props = defineProps({
    chapter: Object,
    progress: Object,
    adSlots: Object,
});

const audioStore = useAudioStore();
const activeViewTab = ref('text'); // 'text' or 'pdf'
const currentPageIndex = ref(0);
const readerTheme = ref('default'); // 'default', 'sepia', 'dark'

const pages = computed(() => {
    return props.chapter?.study_material?.pages || [props.chapter?.study_material?.content || ''];
});

const totalPages = computed(() => pages.value.length);

const formatBookHtml = (rawText) => {
    if (!rawText) return '';
    let html = rawText
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/^\> (.*$)/gim, '<div class="callout-box">$1</div>')
        .replace(/^\- (.*$)/gim, '<li>$1</li>');

    html = html.replace(/(<li>.*<\/li>)/gms, '<ul>$1</ul>');
    html = html.replace(/\n\n/g, '<br><br>');
    return html;
};

const nextPage = () => {
    if (currentPageIndex.value + 1 < totalPages.value) {
        currentPageIndex.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevPage = () => {
    if (currentPageIndex.value > 0) {
        currentPageIndex.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const goToPage = (idx) => {
    currentPageIndex.value = idx;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Reading Timer State
const timeSpent = ref(props.progress?.time_spent_reading_seconds || 0);
const minRequired = computed(() => props.chapter?.min_reading_time_seconds || 300);
const isExamUnlocked = computed(() => timeSpent.value >= minRequired.value);

let pingInterval = null;

const sendReadingPing = async () => {
    try {
        const res = await axios.post('/api/reading-progress/ping', {
            chapter_id: props.chapter.id,
            seconds: 60,
        });
        timeSpent.value = res.data.time_spent;
    } catch (e) {
        timeSpent.value += 60;
    }
};

// AI Chatbot State
const isChatOpen = ref(false);
const chatInput = ref('');
const chatMessages = ref([
    { sender: 'ai', text: `সালাম! '${props.chapter.title}' অধ্যায়ের যেকোনো প্রশ্ন করতে পারেন।` }
]);
const isChatLoading = ref(false);

const sendMessage = async () => {
    if (!chatInput.value.trim() || isChatLoading.value) return;

    const userText = chatInput.value;
    chatMessages.value.push({ sender: 'user', text: userText });
    chatInput.value = '';
    isChatLoading.value = true;

    try {
        const res = await axios.post('/api/ai/chat', {
            chapter_id: props.chapter.id,
            message: userText,
        });
        chatMessages.value.push({ sender: 'ai', text: res.data.reply });
    } catch (e) {
        chatMessages.value.push({ sender: 'ai', text: 'উত্তর উৎপাদনে সাময়িক সমস্যা হচ্ছে।' });
    } finally {
        isChatLoading.value = false;
    }
};

onMounted(() => {
    pingInterval = setInterval(sendReadingPing, 60000);
});

onUnmounted(() => {
    if (pingInterval) clearInterval(pingInterval);
});
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Reader Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-6 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm">
                <div>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">{{ chapter.course?.title }}</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-slate-100 mt-1">{{ chapter.title }}</h1>
                </div>

                <!-- Action Buttons: Audio & Start Exam -->
                <div class="flex items-center space-x-3">
                    <button
                        @click="audioStore.play(chapter.title, pages[currentPageIndex])"
                        class="px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 rounded-xl font-bold text-xs flex items-center space-x-2 transition border border-indigo-200 dark:border-indigo-900"
                    >
                        <Volume2 class="w-4 h-4 animate-bounce" />
                        <span>শুনুন (TTS)</span>
                    </button>

                    <!-- Exam Button (Locked until reading time complete) -->
                    <Link
                        v-if="chapter.exams?.[0] && isExamUnlocked"
                        :href="`/exam/${chapter.exams[0].id}`"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center space-x-2 transition shadow-lg shadow-emerald-600/20"
                    >
                        <span>পরীক্ষা দিন</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                    <button
                        v-else
                        disabled
                        class="px-4 py-2.5 bg-gray-200 dark:bg-slate-800 text-gray-400 rounded-xl font-bold text-xs flex items-center space-x-2 cursor-not-allowed opacity-75"
                    >
                        <Lock class="w-4 h-4" />
                        <span>পড়ার সময় বাকি: {{ Math.max(0, Math.ceil((minRequired - timeSpent) / 60)) }} মিনিট</span>
                    </button>
                </div>
            </div>

            <!-- View & Theme Mode Selector Controls -->
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-200/80 dark:bg-slate-800 p-2 rounded-2xl">
                <!-- Mode Tabs -->
                <div v-if="chapter.study_material?.pdf_file_path" class="flex items-center space-x-1">
                    <button
                        @click="activeViewTab = 'text'"
                        class="px-4 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5"
                        :class="activeViewTab === 'text' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-slate-400'"
                    >
                        <FileText class="w-4 h-4" />
                        <span>বইয়ের ফরম্যাটেড পাঠ্য</span>
                    </button>
                    <button
                        @click="activeViewTab = 'pdf'"
                        class="px-4 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5"
                        :class="activeViewTab === 'pdf' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-slate-400'"
                    >
                        <BookOpen class="w-4 h-4" />
                        <span>বইয়ের PDF ভিউয়ার</span>
                    </button>
                </div>

                <!-- Reading Background Theme Options -->
                <div v-if="activeViewTab === 'text'" class="flex items-center space-x-1 ml-auto text-xs font-bold">
                    <span class="text-gray-400 mr-2 text-[11px]">পড়ার থিম:</span>
                    <button
                        @click="readerTheme = 'default'"
                        class="px-2.5 py-1 rounded-lg border border-gray-300 dark:border-slate-700 bg-white text-gray-800"
                        :class="{ 'ring-2 ring-indigo-500': readerTheme === 'default' }"
                    >
                        সাদা
                    </button>
                    <button
                        @click="readerTheme = 'sepia'"
                        class="px-2.5 py-1 rounded-lg border border-amber-300 bg-[#FDF6E3] text-[#433422]"
                        :class="{ 'ring-2 ring-amber-500': readerTheme === 'sepia' }"
                    >
                        বুক সেপিয়া (Sepia)
                    </button>
                </div>
            </div>

            <!-- Reading Timer Progress Bar -->
            <div class="p-4 bg-indigo-500/10 border border-indigo-200 dark:border-indigo-900/50 rounded-2xl flex items-center justify-between text-xs font-semibold">
                <div class="flex items-center space-x-2 text-indigo-700 dark:text-indigo-300">
                    <Clock class="w-4 h-4" />
                    <span>সক্রিয় পড়ার সময়: {{ Math.floor(timeSpent / 60) }} মিনিট / {{ Math.ceil(minRequired / 60) }} মিনিট</span>
                </div>
                <div class="flex items-center space-x-1">
                    <span v-if="isExamUnlocked" class="text-emerald-600 dark:text-emerald-400 flex items-center">
                        <CheckCircle class="w-4 h-4 mr-1 inline" /> কুইজ আনলকড!
                    </span>
                    <span v-else class="text-amber-600 dark:text-amber-400">
                        সঠিক মনোযোগ দিয়ে পড়ুন
                    </span>
                </div>
            </div>

            <!-- AI Summary Box -->
            <div v-if="chapter.study_material?.ai_summary" class="p-6 bg-gradient-to-tr from-indigo-50 to-purple-50 dark:from-indigo-950/40 dark:to-purple-950/40 border border-indigo-100 dark:border-indigo-900/60 rounded-3xl shadow-sm">
                <div class="flex items-center space-x-2 text-indigo-700 dark:text-indigo-300 mb-3">
                    <Sparkles class="w-5 h-5 text-indigo-500" />
                    <h3 class="font-bold text-base">এআই সংক্ষেপণ (AI Summary)</h3>
                </div>
                <div class="text-sm leading-relaxed text-gray-700 dark:text-slate-300 whitespace-pre-line font-bengali">
                    {{ chapter.study_material.ai_summary }}
                </div>
            </div>

            <!-- Page Navigation Control (Top Bar) -->
            <div v-if="activeViewTab === 'text' && totalPages > 1" class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm">
                <button
                    @click="prevPage"
                    :disabled="currentPageIndex === 0"
                    class="px-3 py-1.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-1"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span>আগের পৃষ্ঠা</span>
                </button>

                <div class="flex items-center space-x-1.5">
                    <span class="text-xs font-bold text-gray-500 mr-2">পৃষ্ঠা:</span>
                    <button
                        v-for="(p, pIdx) in totalPages"
                        :key="pIdx"
                        @click="goToPage(pIdx)"
                        class="w-7 h-7 rounded-lg text-xs font-bold transition"
                        :class="pIdx === currentPageIndex ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-200'"
                    >
                        {{ pIdx + 1 }}
                    </button>
                </div>

                <button
                    @click="nextPage"
                    :disabled="currentPageIndex + 1 >= totalPages"
                    class="px-3 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-1 shadow-md shadow-indigo-600/20"
                >
                    <span>পরের পৃষ্ঠা</span>
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>

            <!-- PDF Embedded Viewer Mode -->
            <div v-if="activeViewTab === 'pdf' && chapter.study_material?.pdf_file_path" class="bg-white dark:bg-slate-800 p-4 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                <div class="flex items-center justify-between px-2">
                    <span class="text-xs font-bold text-indigo-600">📄 মূল বইয়ের PDF পৃষ্ঠা</span>
                    <a
                        :href="chapter.study_material.pdf_file_path"
                        target="_blank"
                        class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl flex items-center space-x-1"
                    >
                        <Download class="w-3.5 h-3.5" />
                        <span>PDF ডাউনলোড করুন</span>
                    </a>
                </div>
                <iframe
                    :src="chapter.study_material.pdf_file_path"
                    class="w-full h-[650px] rounded-2xl border border-gray-200 dark:border-slate-700"
                ></iframe>
            </div>

            <!-- Formatted Content Reader Body (Book Typography Engine) -->
            <article
                v-else
                class="p-8 md:p-12 rounded-3xl border shadow-sm prose-reading no-select transition-colors duration-300"
                :class="[
                    readerTheme === 'sepia' ? 'sepia-mode border-amber-200' : 'bg-white dark:bg-slate-800 border-gray-100 dark:border-slate-700 text-gray-900 dark:text-slate-100'
                ]"
            >
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-200/60 dark:border-slate-700 text-xs opacity-75">
                    <span>📖 পৃষ্ঠা {{ currentPageIndex + 1 }} (মোট {{ totalPages }} পৃষ্ঠা)</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">EducationAlwaysFree • ডিজিটাল বুক ফরম্যাট</span>
                </div>
                <div v-html="formatBookHtml(pages[currentPageIndex])"></div>
            </article>

            <!-- Page Navigation Control (Bottom Bar) -->
            <div v-if="activeViewTab === 'text' && totalPages > 1" class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm">
                <button
                    @click="prevPage"
                    :disabled="currentPageIndex === 0"
                    class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-1"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span>আগের পৃষ্ঠা</span>
                </button>

                <span class="text-xs font-bold text-indigo-600">পৃষ্ঠা {{ currentPageIndex + 1 }} / {{ totalPages }}</span>

                <button
                    @click="nextPage"
                    :disabled="currentPageIndex + 1 >= totalPages"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold disabled:opacity-40 flex items-center space-x-1 shadow-md shadow-indigo-600/20"
                >
                    <span>পরের পৃষ্ঠা</span>
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>

            <!-- Floating AI Tutor Widget Trigger -->
            <div class="fixed bottom-24 right-4 z-[9980]">
                <button
                    @click="isChatOpen = !isChatOpen"
                    class="p-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center space-x-2 font-bold text-xs transition transform hover:scale-105"
                >
                    <MessageSquare class="w-5 h-5" />
                    <span class="hidden sm:inline">AI টিউটরকে প্রশ্ন করুন</span>
                </button>

                <!-- Chat Popup Modal -->
                <div v-if="isChatOpen" class="absolute bottom-16 right-0 w-80 md:w-96 bg-white dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 rounded-3xl shadow-2xl overflow-hidden flex flex-col h-96">
                    <div class="p-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <Sparkles class="w-4 h-4" />
                            <h4 class="font-bold text-sm">এআই চ্যাপ্টার টিউটর</h4>
                        </div>
                        <button @click="isChatOpen = false" class="text-white/80 hover:text-white">✕</button>
                    </div>

                    <!-- Messages Container -->
                    <div class="flex-1 p-4 overflow-y-auto space-y-3 text-xs">
                        <div
                            v-for="(msg, i) in chatMessages"
                            :key="i"
                            class="p-3 rounded-2xl max-w-[85%]"
                            :class="msg.sender === 'user' ? 'bg-indigo-600 text-white ml-auto' : 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200'"
                        >
                            {{ msg.text }}
                        </div>
                    </div>

                    <!-- Input Box -->
                    <div class="p-3 border-t border-gray-100 dark:border-slate-700 flex items-center space-x-2">
                        <input
                            v-model="chatInput"
                            @keyup.enter="sendMessage"
                            type="text"
                            placeholder="এই অধ্যায় সম্পর্কিত প্রশ্ন লিখুন..."
                            class="flex-1 px-3 py-2 bg-gray-100 dark:bg-slate-700 border-none rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"
                        />
                        <button @click="sendMessage" :disabled="isChatLoading" class="p-2 bg-indigo-600 text-white rounded-xl">
                            <Send class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
