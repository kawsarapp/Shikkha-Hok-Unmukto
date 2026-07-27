<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Settings, Sliders, Layout, BookOpen, Plus, Sparkles, Save, Check, Layers, FileText, HelpCircle, Search, Edit, Trash2, Eye, FileUp, Cpu, User, Bold, Heading1, Heading2, List, AlertCircle, Scissors, ChevronDown, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    settings: Array,
    adSlots: Array,
    courses: Array,
});

const activeTab = ref('courses'); // 'courses', 'settings', 'ads'
const searchQuery = ref('');
const expandedAdminSubjects = ref({}); // Track expanded subjects in Admin

const toggleAdminSubjectAccordion = (subjKey) => {
    expandedAdminSubjects.value[subjKey] = !expandedAdminSubjects.value[subjKey];
};

const isAdminSubjectExpanded = (subjKey) => {
    return expandedAdminSubjects.value[subjKey] !== false; // Default true (expanded)
};

const getAdminGroupedChapters = (chapters) => {
    if (!chapters) return {};
    return chapters.reduce((acc, curr) => {
        const subj = curr.subject || 'বাংলা';
        if (!acc[subj]) acc[subj] = [];
        acc[subj].push(curr);
        return acc;
    }, {});
};

// Filtered Courses
const filteredCourses = computed(() => {
    if (!searchQuery.value.trim()) return props.courses;
    const q = searchQuery.value.toLowerCase();

    return props.courses.filter(course => {
        const matchesCourse = course.title.toLowerCase().includes(q);
        const matchesChapter = course.chapters?.some(c => c.title.toLowerCase().includes(q) || (c.subject && c.subject.toLowerCase().includes(q)));
        return matchesCourse || matchesChapter;
    });
});

// Settings Form
const settingsForm = useForm({
    settings: props.settings.reduce((acc, curr) => {
        acc[curr.key_name] = curr.value;
        return acc;
    }, {}),
});

const saveSettings = () => {
    settingsForm.post('/admin/settings', { preserveScroll: true });
};

// Course Form
const courseForm = useForm({
    title: '',
    description: '',
});

const createCourse = () => {
    courseForm.post('/admin/courses', {
        preserveScroll: true,
        onSuccess: () => courseForm.reset(),
    });
};

// Chapter Form with Subject support
const chapterForm = useForm({
    course_id: '',
    subject: 'বাংলা',
    title: '',
    min_reading_time_seconds: 300,
    passing_score_percentage: 70,
});

const createChapter = () => {
    chapterForm.post('/admin/chapters', {
        preserveScroll: true,
        onSuccess: () => chapterForm.reset('title'),
    });
};

// Material Form & Formatting Toolbar Helper
const richEditorRef = ref(null);
const selectedChapterForMaterial = ref(null);
const materialForm = useForm({
    chapter_id: '',
    content: '',
    pdf_file_path: '',
});

const insertHtmlToEditor = (html) => {
    if (richEditorRef.value?.insertHtmlAtCursor) {
        richEditorRef.value.insertHtmlAtCursor(html);
    } else {
        materialForm.content += html;
    }
};

const insertFormatting = (tag) => {
    let snippet = '';
    switch (tag) {
        case 'h1':
            snippet = '\n<h1>মূল শিরোনাম বা বিষয়...</h1>\n';
            break;
        case 'h2':
            snippet = '\n<h2>উপক্যাপশন বা পরিচ্ছেদ...</h2>\n';
            break;
        case 'bold':
            snippet = ' <strong>গুরুত্বপূর্ণ তথ্য/সাল</strong> ';
            break;
        case 'list':
            snippet = '\n<ul><li>প্রথম পয়েন্ট</li><li>দ্বিতীয় পয়েন্ট</li><li>তৃতীয় পয়েন্ট</li></ul>\n';
            break;
        case 'alert':
            snippet = '\n<blockquote>💡 <strong>বিশেষ নোট:</strong> পরীক্ষার জন্য এই অংশটি অত্যন্ত গুরুত্বপূর্ণ।</blockquote>\n';
            break;
        case 'page':
            snippet = '\n<hr class="my-6 border-dashed" />\n';
            break;
        case 'book_ribbon':
            snippet = '\n<div class="book-header-ribbon">>>> পাটিগণিত <<<</div>\n<div class="book-subheader-box">বাস্তব সংখ্যা, গড়, ভগ্নাংশ, ল.সা.গু. ও গ.সা.গু.</div>\n';
            break;
        case 'math_lcm':
            snippet = '\n<div class="math-formula-box">\n$$\\text{ভগ্নাংশের ল.সা.গু} = \\frac{\\text{লবগুলোর ল.সা.গু}}{\\text{হরগুলোর গ.সা.গু}}$$\n</div>\n';
            break;
        case 'fraction':
            snippet = ' $$\\frac{২}{৫}$$ ';
            break;
        case 'sqrt':
            snippet = ' $$\\sqrt{\\frac{৯}{৪}} = \\frac{৩}{২} = ১.৫$$ ';
            break;
        case 'bcs_grid':
            snippet = '\n<table class="bcs-analysis-table">\n  <thead>\n    <tr><th>বিসিএস পরীক্ষা</th><th>প্রশ্ন সংখ্যা</th><th>বিসিএস পরীক্ষা</th><th>প্রশ্ন সংখ্যা</th></tr>\n  </thead>\n  <tbody>\n    <tr><td>৫০তম বিসিএস</td><td>২টি</td><td>৪৯তম বিসিএস</td><td>১টি</td></tr>\n    <tr><td>৪৮তম বিসিএস</td><td>১টি</td><td>৪৭তম বিসিএস</td><td>২টি</td></tr>\n  </tbody>\n</table>\n';
            break;
        case '2col':
            snippet = '\n<div class="book-2col">\n  <div>\n    <h4>১. বাম পাশের কলাম</h4>\n    <p>বাম পাশের টেক্সট ও কুইজ সমাধান...</p>\n  </div>\n  <div>\n    <h4>২. ডান পাশের কলাম</h4>\n    <p>ডান পাশের টেক্সট ও ব্যাখ্যা...</p>\n  </div>\n</div>\n';
            break;
        case '3col_model_test':
            snippet = `\n<div class="book-3col">\n  <div>\n    <h4 style="color:#0F766E; font-weight:bold;">১. বাংলা ভাষা ও সাহিত্য</h4>\n    <p><strong>১. 'চর্যাপদ' কত সালে আবিষ্কৃত হয়?</strong><br>(ক) ১৯০৫ (খ) ১৯০৭ (গ) ১৯১২ (ঘ) ১৯১৬</p>\n  </div>\n  <div>\n    <h4 style="color:#0F766E; font-weight:bold;">২. ইংরেজি সাহিত্য</h4>\n    <p><strong>২. Who wrote 'Hamlet'?</strong><br>(a) Milton (b) Shakespeare (c) Keats (d) Shelley</p>\n  </div>\n  <div>\n    <h4 style="color:#0F766E; font-weight:bold;">৩. বাংলাদেশ বিষয়াবলি</h4>\n    <p><strong>৩. সংবিধান দিবস কত তারিখে?</strong><br>(ক) ৪ নভেম্বর (খ) ১৬ ডিসেম্বর (গ) ২৬ মার্চ (ঘ) ১৭ এপ্রিল</p>\n  </div>\n</div>\n<div class="omr-answer-key-box">\n  <div class="omr-title">📝 ৩-কলাম বিসিএস মডেল টেস্ট উত্তরপত্র ও ব্যাখ্যা</div>\n  <div class="omr-grid">\n    <div><strong>১. (খ) ১৯০৭</strong> - মহামহোপাধ্যায় হরপ্রসাদ শাস্ত্রী নেপালের রাজদরবারের রয়্যাল লাইব্রেরি থেকে চর্যাপদ আবিষ্কার করেন।</div>\n    <div><strong>২. (b) Shakespeare</strong> - Hamlet is a tragedy written by William Shakespeare.</div>\n    <div><strong>৩. (ক) ৪ নভেম্বর</strong> - ১৯৭২ সালের ৪ নভেম্বর বাংলাদেশের সংবিধান গৃহীত হয়।</div>\n  </div>\n</div>\n`;
            break;
    }
    if (snippet) insertHtmlToEditor(snippet);
};

// Graphical Table & Formula Builders (Zero Code Input!)
const isEditorFullscreen = ref(false);
const isTableBuilderOpen = ref(false);
const tableHeaders = ref(['বিসিএস পরীক্ষা', 'প্রশ্ন সংখ্যা', 'বিসিএস পরীক্ষা', 'প্রশ্ন সংখ্যা']);
const tableMatrix = ref([
    ['৫০তম বিসিএস', '২টি', '৪৯তম বিসিএস', '১টি'],
    ['৪৮তম বিসিএস', '১টি', '৪৭তম বিসিএস', '২টি'],
]);

const addTableColumn = () => {
    tableHeaders.value.push(`কলাম ${tableHeaders.value.length + 1}`);
    tableMatrix.value.forEach(row => row.push(''));
};

const removeTableColumn = (colIndex) => {
    if (tableHeaders.value.length <= 1) return;
    tableHeaders.value.splice(colIndex, 1);
    tableMatrix.value.forEach(row => row.splice(colIndex, 1));
};

const addTableRow = () => {
    tableMatrix.value.push(new Array(tableHeaders.value.length).fill(''));
};

const removeTableRow = (rowIndex) => {
    if (tableMatrix.value.length <= 1) return;
    tableMatrix.value.splice(rowIndex, 1);
};

const generateAndInsertTable = () => {
    let html = '\n<table class="bcs-analysis-table">\n  <tbody>\n    <tr class="table-header-row">\n';
    tableHeaders.value.forEach(h => {
        html += `      <td class="table-header-cell">${h || '-'}</td>\n`;
    });
    html += '    </tr>\n';
    tableMatrix.value.forEach(row => {
        html += '    <tr>\n';
        row.forEach(cell => {
            html += `      <td>${cell || '-'}</td>\n`;
        });
        html += '    </tr>\n';
    });
    html += '  </tbody>\n</table>\n';
    insertHtmlToEditor(html);
    isTableBuilderOpen.value = false;
};

// Math Formula Builder State
const isMathBuilderOpen = ref(false);
const mathForm = ref({
    title: 'ভগ্নাংশের ল.সা.গু',
    numerator: 'লবগুলোর ল.সা.গু',
    denominator: 'হরগুলোর গ.সা.গু',
});

const generateAndInsertMath = () => {
    let html = `\n<div class="math-formula-box">\n$$\\text{${mathForm.value.title}} = \\frac{\\text{${mathForm.value.numerator}}}{\\text{${mathForm.value.denominator}}}$$\n</div>\n`;
    insertHtmlToEditor(html);
    isMathBuilderOpen.value = false;
};

const openMaterialModal = (chapter) => {
    selectedChapterForMaterial.value = chapter;
    materialForm.chapter_id = chapter.id;
    materialForm.content = chapter.study_material?.content || '';
    materialForm.pdf_file_path = chapter.study_material?.pdf_file_path || '';
};

const saveMaterial = () => {
    materialForm.post('/admin/study-material', {
        preserveScroll: true,
        onSuccess: () => {
            selectedChapterForMaterial.value = null;
        },
    });
};

// Question Form & Inspection List
const selectedChapterForQA = ref(null);
const editingQuestion = ref(null);

const qaForm = useForm({
    chapter_id: '',
    question_text: '',
    options: ['', '', '', ''],
    correct_option_index: 0,
    explanation: '',
});

const openQaModal = (chapter) => {
    selectedChapterForQA.value = chapter;
    qaForm.chapter_id = chapter.id;
    editingQuestion.value = null;
    qaForm.reset('question_text', 'options', 'explanation');
    qaForm.options = ['', '', '', ''];
};

const startEditQuestion = (question) => {
    editingQuestion.value = question;
    qaForm.question_text = question.question_text;
    qaForm.options = [...question.options];
    qaForm.correct_option_index = question.correct_option_index;
    qaForm.explanation = question.explanation || '';
};

const saveQuestion = () => {
    if (editingQuestion.value) {
        qaForm.post(`/admin/questions/${editingQuestion.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                editingQuestion.value = null;
                qaForm.reset('question_text', 'options', 'explanation');
                qaForm.options = ['', '', '', ''];
            },
        });
    } else {
        qaForm.post('/admin/questions', {
            preserveScroll: true,
            onSuccess: () => {
                qaForm.reset('question_text', 'options', 'explanation');
                qaForm.options = ['', '', '', ''];
            },
        });
    }
};

const deleteQuestion = (questionId) => {
    if (confirm('আপনি কি নিশ্চিত যে এই প্রশ্নটি মুছে ফেলতে চান?')) {
        router.delete(`/admin/questions/${questionId}`, {
            preserveScroll: true,
        });
    }
};

const triggerAiQaGen = (chapterId) => {
    useForm({}).post(`/admin/chapters/${chapterId}/generate-ai-qa`, {
        preserveScroll: true,
    });
};

const selectedChapterForSubChapter = ref(null);
const subChapterForm = useForm({
    course_id: '',
    parent_id: '',
    title: '',
    importance_percentage: 85,
});

const openSubChapterModal = (chapter) => {
    selectedChapterForSubChapter.value = chapter;
    subChapterForm.course_id = chapter.course_id;
    subChapterForm.parent_id = chapter.id;
    subChapterForm.title = '';
    subChapterForm.importance_percentage = 85;
};

const createSubChapter = () => {
    subChapterForm.post('/admin/subchapters', {
        preserveScroll: true,
        onSuccess: () => {
            selectedChapterForSubChapter.value = null;
        },
    });
};

const togglePublishChapter = (chapter) => {
    router.post(`/admin/chapters/${chapter.id}/toggle-publish`, {}, { preserveScroll: true });
};

const updateChapterImportance = (chapter, value) => {
    router.post(`/admin/chapters/${chapter.id}/update-importance`, {
        importance_percentage: value,
    }, { preserveScroll: true });
};

const updateQuestionImportance = (question, value) => {
    router.post(`/admin/questions/${question.id}/update-importance`, {
        importance_percentage: value,
    }, { preserveScroll: true });
};

const deleteCourse = (course) => {
    if (confirm(`আপনি কি নিশ্চিত যে '${course.title}' কোর্সটি মুছে ফেলতে চান?`)) {
        router.delete(`/admin/courses/${course.id}`, {
            preserveScroll: true,
        });
    }
};

const updateAdSlot = (adSlot) => {
    useForm({
        ad_code: adSlot.ad_code,
        is_active: adSlot.is_active,
    }).post(`/admin/ads/${adSlot.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header Banner -->
            <div class="p-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 bg-indigo-500/30 border border-indigo-400/30 rounded-full text-indigo-200 text-xs font-semibold">
                        সিস্টেম এডমিন প্যানেল
                    </span>
                    <h1 class="text-3xl font-extrabold mt-2">100% Dynamic Admin Dashboard</h1>
                    <p class="text-xs text-indigo-200 mt-1">কোর্স, বিষয়, প্রশ্ন চেক/এডিট, PDF বুকলিংক ও Gemini AI প্রশ্ন জেনারেটর।</p>
                </div>

                <!-- Tab Buttons -->
                <div class="flex items-center space-x-2 bg-slate-800/80 p-1.5 rounded-2xl border border-slate-700">
                    <button
                        @click="activeTab = 'courses'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'courses' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-700'"
                    >
                        📚 কোর্স ও প্রশ্নমালা
                    </button>
                    <button
                        @click="activeTab = 'settings'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'settings' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-700'"
                    >
                        ⚙️ সেটিংস
                    </button>
                    <button
                        @click="activeTab = 'ads'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition"
                        :class="activeTab === 'ads' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-700'"
                    >
                        📢 এডস
                    </button>
                    <Link
                        href="/admin/users"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-purple-600/30"
                    >
                        👥 সুপার এডমিন প্যানেল
                    </Link>
                </div>
            </div>

            <!-- TAB 1: Course & Chapter Management -->
            <div v-if="activeTab === 'courses'" class="space-y-8">
                <!-- Flash Error Reason Banner -->
                <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 dark:bg-rose-950/80 border-2 border-rose-400 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 font-bold text-sm flex items-center justify-between shadow-md">
                    <div class="flex items-center space-x-2">
                        <AlertCircle class="w-5 h-5 flex-shrink-0 text-rose-600" />
                        <span>{{ $page.props.flash.error }}</span>
                    </div>
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm">
                    <div class="relative flex-1">
                        <Search class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="কোর্স বা অধ্যায়ের নাম লিখে খুঁজুন (Search)..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <!-- Add New Course Form -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 mb-4 flex items-center space-x-2">
                        <Plus class="w-5 h-5 text-indigo-600" />
                        <span>নতুন কোর্স তৈরি করুন (যেমন: প্রাইমারি সহকারী শিক্ষক নিয়োগ প্রস্তুতি)</span>
                    </h3>

                    <form @submit.prevent="createCourse" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <input
                                v-model="courseForm.title"
                                type="text"
                                required
                                placeholder="কোর্সের নাম (যেমন: প্রাইমারি শিক্ষক নিয়োগ প্রস্তুতি)..."
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="md:col-span-1">
                            <input
                                v-model="courseForm.description"
                                type="text"
                                placeholder="কোর্সের বিবরণ..."
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="md:col-span-1">
                            <button
                                type="submit"
                                :disabled="courseForm.processing"
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20"
                            >
                                কোর্স সেভ করুন
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Courses & Chapters List -->
                <div v-for="course in filteredCourses" :key="course.id" class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-100 dark:border-slate-700 gap-4">
                        <div class="flex items-center space-x-3">
                            <div>
                                <span class="text-xs font-bold text-indigo-600 uppercase">কোর্স</span>
                                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-slate-100">{{ course.title }}</h2>
                            </div>
                            <button
                                @click="deleteCourse(course)"
                                class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold border border-rose-200 dark:border-rose-900 flex items-center space-x-1"
                                title="কোর্স ডিলিট করুন"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                                <span>ডিলিট কোর্স</span>
                            </button>
                        </div>

                        <!-- Add Chapter Form with Subject selection -->
                        <form @submit.prevent="createChapter" class="flex flex-wrap items-center gap-2">
                            <input type="hidden" v-model="chapterForm.course_id" />
                            
                            <select
                                v-model="chapterForm.subject"
                                class="px-3 py-2 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-bold text-indigo-600"
                            >
                                <option value="বাংলা">বিষয়: বাংলা</option>
                                <option value="ইংরেজি">বিষয়: ইংরেজি</option>
                                <option value="গণিত">বিষয়: গণিত</option>
                                <option value="সাধারণ জ্ঞান">বিষয়: সাধারণ জ্ঞান</option>
                                <option value="বিজ্ঞান">বিষয়: বিজ্ঞান</option>
                            </select>

                            <input
                                v-model="chapterForm.title"
                                type="text"
                                required
                                placeholder="অধ্যায়ের নাম (যেমন: চর্যাপদ/Tense/বীজগণিত)..."
                                class="px-3 py-2 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-xs flex-1 min-w-[180px]"
                            />

                            <button
                                @click="chapterForm.course_id = course.id"
                                type="submit"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold"
                            >
                                + বিষয়ভিত্তিক অধ্যায় যুক্ত করুন
                            </button>
                        </form>
                    </div>

                    <!-- Subject-Wise Chapters Accordion Tree -->
                    <div class="space-y-4">
                        <div
                            v-for="(subjChapters, subjName) in getAdminGroupedChapters(course.chapters)"
                            :key="subjName"
                            class="border border-gray-200 dark:border-slate-700/80 rounded-2xl overflow-hidden bg-gray-50/50 dark:bg-slate-900/30 space-y-3 p-4"
                        >
                            <div
                                @click="toggleAdminSubjectAccordion(course.id + '_' + subjName)"
                                class="flex items-center justify-between cursor-pointer select-none p-2 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-xl transition"
                            >
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-indigo-600 text-white font-extrabold text-xs rounded-xl shadow-sm">
                                        📚 বিষয়: {{ subjName }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-bold">({{ subjChapters.length }}টি অধ্যায়)</span>
                                </div>
                                <div class="flex items-center space-x-1 text-indigo-600 font-bold text-xs">
                                    <span>{{ isAdminSubjectExpanded(course.id + '_' + subjName) ? 'সংকুচিত করুন' : 'অধ্যায়সমূহ দেখুন' }}</span>
                                    <ChevronDown v-if="isAdminSubjectExpanded(course.id + '_' + subjName)" class="w-4 h-4" />
                                    <ChevronRight v-else class="w-4 h-4" />
                                </div>
                            </div>

                            <div v-if="isAdminSubjectExpanded(course.id + '_' + subjName)" class="space-y-4 pt-2">
                                <div
                                    v-for="ch in subjChapters"
                                    :key="ch.id"
                                    class="p-4 rounded-2xl border bg-white dark:bg-slate-800 space-y-3 transition"
                                    :class="[
                                        ch.is_published ? 'border-gray-200 dark:border-slate-700' : 'border-dashed border-amber-300 dark:border-amber-800 bg-amber-50/20 dark:bg-amber-950/10 opacity-75'
                                    ]"
                                >
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h4 class="font-bold text-base text-gray-900 dark:text-slate-100 flex items-center space-x-2">
                                                    <span>📖 {{ ch.title }}</span>
                                                    <span v-if="!ch.is_published" class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full">
                                                        🙈 হাইড করা আছে
                                                    </span>
                                                </h4>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-2">
                                                <!-- Importance Badge -->
                                                <span class="px-2 py-0.5 bg-rose-50 text-rose-700 font-extrabold text-[11px] rounded-lg border border-rose-200 flex items-center space-x-1">
                                                    <span>🔥 গুরুত্ব:</span>
                                                    <select
                                                        :value="ch.importance_percentage || 85"
                                                        @change="updateChapterImportance(ch, $event.target.value)"
                                                        class="bg-transparent font-extrabold text-rose-700 border-none p-0 cursor-pointer focus:ring-0 text-xs"
                                                    >
                                                        <option value="95">৯৫% (হট টপিক 🔥)</option>
                                                        <option value="90">৯০% (খুব গুরুত্বপূর্ণ ⭐)</option>
                                                        <option value="85">৮৫% (গুরুত্বপূর্ণ)</option>
                                                        <option value="75">৭৫% (সাধারণ)</option>
                                                        <option value="60">৬০% (কম গুরুত্বপূর্ণ)</option>
                                                    </select>
                                                </span>

                                                <span>•</span>
                                                <span v-if="ch.study_material?.content" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 font-bold text-[10px] rounded-full border border-emerald-200">
                                                    ✓ পড়া যুক্ত আছে
                                                </span>
                                                <span v-else class="px-2 py-0.5 bg-amber-50 text-amber-600 font-bold text-[10px] rounded-full border border-amber-200">
                                                    ⚠️ পড়া বাকি
                                                </span>
                                                <span v-if="ch.study_material?.pdf_file_path" class="px-2 py-0.5 bg-purple-50 text-purple-600 font-bold text-[10px] rounded-full border border-purple-200">
                                                    📄 PDF যুক্ত আছে
                                                </span>
                                                <span>•</span>
                                                <span class="font-bold text-indigo-600">
                                                    📝 {{ ch.questions?.length || 0 }}টি প্রশ্ন
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Chapter Action Controls -->
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <!-- Hide/Publish Toggle Button -->
                                            <button
                                                @click="togglePublishChapter(ch)"
                                                type="button"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition flex items-center space-x-1"
                                                :class="[
                                                    ch.is_published
                                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                                        : 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200'
                                                ]"
                                                title="হাইড বা পাবলিশ পরিবর্তন করুন"
                                            >
                                                <span>{{ ch.is_published ? '👁️ পাবলিশ' : '🙈 হাইড করা' }}</span>
                                            </button>

                                            <!-- Add Sub-Chapter Button -->
                                            <button
                                                @click="openSubChapterModal(ch)"
                                                type="button"
                                                class="px-2.5 py-1.5 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 rounded-xl text-xs font-bold border border-purple-200 hover:bg-purple-100 flex items-center space-x-1"
                                            >
                                                <Plus class="w-3.5 h-3.5" />
                                                <span>+ সাব-অধ্যায় তৈরি</span>
                                            </button>

                                            <button
                                                @click="openMaterialModal(ch)"
                                                class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-bold border border-indigo-200 flex items-center space-x-1"
                                            >
                                                <FileText class="w-3.5 h-3.5" />
                                                <span>পঠনসামগ্রী ও PDF</span>
                                            </button>

                                            <button
                                                @click="openQaModal(ch)"
                                                class="px-2.5 py-1.5 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl text-xs font-bold border border-amber-200 flex items-center space-x-1"
                                            >
                                                <HelpCircle class="w-3.5 h-3.5" />
                                                <span>প্রশ্ন ({{ ch.questions?.length || 0 }})</span>
                                            </button>

                                            <button
                                                @click="triggerAiQaGen(ch.id)"
                                                class="px-2.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold flex items-center space-x-1 shadow-md shadow-purple-600/20"
                                            >
                                                <Sparkles class="w-3.5 h-3.5" />
                                                <span>এআই প্রশ্ন</span>
                                            </button>

                                            <button
                                                @click="deleteChapter(ch)"
                                                class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold border border-rose-200"
                                                title="অধ্যায় ডিলিট করুন"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Sub-Chapters Nested Tree View -->
                                    <div v-if="ch.sub_chapters?.length" class="ml-4 pl-4 border-l-2 border-purple-300 dark:border-purple-800 space-y-2 pt-2">
                                        <div class="text-xs font-extrabold text-purple-700 dark:text-purple-400 uppercase tracking-wider mb-1">
                                            📂 সাব-অধ্যায়সমূহ ({{ ch.sub_chapters.length }}টি):
                                        </div>

                                        <div
                                            v-for="subCh in ch.sub_chapters"
                                            :key="subCh.id"
                                            class="p-3 bg-purple-50/40 dark:bg-slate-900/60 border border-purple-100 dark:border-slate-700 rounded-xl flex items-center justify-between gap-3 text-xs"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <span class="font-bold text-gray-900 dark:text-slate-100">📌 {{ subCh.title }}</span>
                                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 text-[10px] font-extrabold rounded-md">
                                                    🔥 গুরুত্ব: {{ subCh.importance_percentage || 85 }}%
                                                </span>
                                                <span class="text-gray-400">({{ subCh.questions?.length || 0 }}টি প্রশ্ন)</span>
                                            </div>

                                            <div class="flex items-center space-x-1.5">
                                                <button @click="openMaterialModal(subCh)" class="px-2 py-1 bg-white border rounded text-[11px] font-bold">পঠনসামগ্রী</button>
                                                <button @click="openQaModal(subCh)" class="px-2 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded text-[11px] font-bold">প্রশ্ন</button>
                                                <button @click="deleteChapter(subCh)" class="px-1.5 py-1 bg-rose-50 text-rose-600 rounded">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Dynamic Settings -->
            <form v-if="activeTab === 'settings'" @submit.prevent="saveSettings" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-slate-700">
                    <div class="flex items-center space-x-2">
                        <Sliders class="w-5 h-5 text-indigo-600" />
                        <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100">গ্লোবাল সিস্টেম কনফিগারেশন</h3>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold">
                        সেটিংস সেভ করুন
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold mb-2">Google Gemini API Key</label>
                        <input v-model="settingsForm.settings['gemini_api_key']" type="password" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-2">Gemini Model</label>
                        <select v-model="settingsForm.settings['gemini_model']" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs">
                            <option value="gemini-1.5-flash">gemini-1.5-flash (Fast)</option>
                            <option value="gemini-1.5-pro">gemini-1.5-pro (High Precision)</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- TAB 3: Ads Management -->
            <div v-if="activeTab === 'ads'" class="bg-white dark:bg-slate-800 p-8 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="ad in adSlots" :key="ad.id" class="p-5 border rounded-2xl bg-gray-50/50 dark:bg-slate-900/40 space-y-4">
                        <h4 class="font-bold text-sm">{{ ad.title || ad.slot_name }}</h4>
                        <textarea v-model="ad.ad_code" rows="3" class="w-full px-3 py-2 bg-white dark:bg-slate-800 border rounded-xl text-xs font-mono"></textarea>
                        <button @click="updateAdSlot(ad)" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">
                            এড আপডেট করুন
                        </button>
                    </div>
                </div>
            </div>

            <!-- Material Content & Rich Formatting Toolbar Edit Modal -->
            <!-- Book Material & Rich Editor Modal Dialog -->
            <div v-if="selectedChapterForMaterial" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 md:p-6">
                <div
                    class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
                    :class="[
                        isEditorFullscreen
                            ? 'fixed inset-0 z-[10000] max-w-none max-h-none rounded-none h-screen w-screen p-4 md:p-6'
                            : 'max-w-4xl max-h-[92vh] w-full rounded-3xl p-5'
                    ]"
                >
                    <!-- Fixed Modal Header & Pinned Toolbar (STRICTLY PINNED AT TOP) -->
                    <div class="flex-shrink-0 space-y-3 pb-3 border-b border-gray-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100">অধ্যায়: {{ selectedChapterForMaterial.title }} - পঠনসামগ্রী ও PDF</h3>
                            
                            <button
                                @click="isEditorFullscreen = !isEditorFullscreen"
                                type="button"
                                class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl text-xs font-extrabold flex items-center space-x-1 border border-indigo-200 dark:border-indigo-900"
                            >
                                <span>{{ isEditorFullscreen ? '🗗 ছোট স্ক্রিন' : '🖵 ফুল স্ক্রিন মোড' }}</span>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold mb-1">বইয়ের PDF লিঙ্ক / URL (ঐচ্ছিক):</label>
                                <input
                                    v-model="materialForm.pdf_file_path"
                                    type="url"
                                    placeholder="https://example.com/books/primary-bangla.pdf"
                                    class="w-full px-3 py-1.5 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs"
                                />
                            </div>
                        </div>

                        <!-- Rich Book Formatting Pinned Action Toolbar (Categorized MS Word Ribbon Layout) -->
                        <div class="bg-gray-50 dark:bg-slate-900/90 p-2.5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-extrabold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">
                                    🛠️ বইয়ের সাজানোর সরঞ্জাম (MS Word Ribbon Categorized Toolbar):
                                </label>
                                <span class="text-[10px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-extrabold px-2 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-800">
                                    📌 Pinned Sticky
                                </span>
                            </div>

                            <!-- Categorized Ribbon Groups -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs">
                                <!-- Group 1: Book Formatting -->
                                <div class="p-2 bg-white dark:bg-slate-800 rounded-xl border border-indigo-100 dark:border-slate-700 space-y-1">
                                    <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-300 uppercase block">📖 ১. টেক্সট ও হেডার:</span>
                                    <div class="flex flex-wrap gap-1">
                                        <button @click="insertFormatting('h1')" type="button" class="px-2 py-0.5 bg-gray-50 dark:bg-slate-900 border rounded text-[11px] font-bold hover:bg-indigo-50">H1 হেডিং</button>
                                        <button @click="insertFormatting('h2')" type="button" class="px-2 py-0.5 bg-gray-50 dark:bg-slate-900 border rounded text-[11px] font-bold hover:bg-indigo-50">H2 হেডিং</button>
                                        <button @click="insertFormatting('bold')" type="button" class="px-2 py-0.5 bg-gray-50 dark:bg-slate-900 border rounded text-[11px] font-bold hover:bg-indigo-50">বোল্ড</button>
                                        <button @click="insertFormatting('list')" type="button" class="px-2 py-0.5 bg-gray-50 dark:bg-slate-900 border rounded text-[11px] font-bold hover:bg-indigo-50">পয়েন্ট</button>
                                        <button @click="insertFormatting('alert')" type="button" class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900 rounded text-[11px] font-bold">নোট বক্স</button>
                                        <button @click="insertFormatting('book_ribbon')" type="button" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900 rounded text-[11px] font-bold">📜 বুক রিবন</button>
                                    </div>
                                </div>

                                <!-- Group 2: Math & Formulas -->
                                <div class="p-2 bg-white dark:bg-slate-800 rounded-xl border border-emerald-100 dark:border-slate-700 space-y-1">
                                    <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-300 uppercase block">📐 ২. গণিত ও সমীকরণ:</span>
                                    <div class="flex flex-wrap gap-1">
                                        <button @click="insertFormatting('math_lcm')" type="button" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900 rounded text-[11px] font-bold">📐 ল.সা.গু</button>
                                        <button @click="insertFormatting('fraction')" type="button" class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 rounded text-[11px] font-bold">➗ ভগ্নাংশ</button>
                                        <button @click="insertFormatting('sqrt')" type="button" class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900 rounded text-[11px] font-bold">√ বর্গমূল</button>
                                        <button @click="isMathBuilderOpen = true" type="button" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-extrabold shadow">✨ ম্যাথ বিল্ডার</button>
                                    </div>
                                </div>

                                <!-- Group 3: Grids & Layouts -->
                                <div class="p-2 bg-white dark:bg-slate-800 rounded-xl border border-purple-100 dark:border-slate-700 space-y-1">
                                    <span class="text-[10px] font-extrabold text-purple-600 dark:text-purple-300 uppercase block">📰 ৩. টেবিল ও লেআউট:</span>
                                    <div class="flex flex-wrap gap-1">
                                        <button @click="isTableBuilderOpen = true" type="button" class="px-2 py-0.5 bg-teal-600 hover:bg-teal-700 text-white rounded text-[11px] font-extrabold shadow">✨ 📊 টেবিল বিল্ডার</button>
                                        <button @click="insertFormatting('2col')" type="button" class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 rounded text-[11px] font-bold">📰 ২-কলাম</button>
                                        <button @click="insertFormatting('3col_model_test')" type="button" class="px-2 py-0.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-[11px] font-extrabold shadow">✨ 📰 ৩-কলাম টেস্ট</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scrollable Text Area Container (ONLY THIS AREA SCROLLS) -->
                    <div class="flex-1 overflow-y-auto py-3 space-y-3">
                        <!-- MS Word-like WYSIWYG Rich Text Editor Component -->
                        <RichTextEditor ref="richEditorRef" v-model="materialForm.content" />
                    </div>

                    <!-- Modal Footer Actions (Fixed at bottom) -->
                    <div class="flex-shrink-0 pt-3 border-t border-gray-200 dark:border-slate-700 flex items-center justify-end space-x-2">
                        <button @click="selectedChapterForMaterial = null" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">বাতিল</button>
                        <button @click="saveMaterial" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">সেভ করুন</button>
                    </div>
                </div>
            </div>

            <!-- Graphical Dynamic Table Builder Modal (Full Column & Row Control) -->
            <div v-if="isTableBuilderOpen" class="fixed inset-0 z-[10000] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl max-w-3xl w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100">📊 ডাইনামিক কলাম ও সারি টেবিল বিল্ডার</h3>
                            <p class="text-xs text-gray-500">আপনার ইচ্ছামতো কলাম ও সারি যোগ/মুছে টেবিল তৈরি করুন</p>
                        </div>
                        <button @click="isTableBuilderOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <!-- Column Controls Toolbar -->
                    <div class="flex items-center justify-between p-3 bg-teal-50 dark:bg-teal-950/50 rounded-2xl border border-teal-200 dark:border-teal-900">
                        <span class="text-xs font-bold text-teal-800 dark:text-teal-300">
                            মোট কলাম: {{ tableHeaders.length }}টি | মোট সারি: {{ tableMatrix.length }}টি
                        </span>
                        <div class="flex items-center space-x-2">
                            <button @click="addTableColumn" type="button" class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-sm">
                                + কলাম যোগ করুন
                            </button>
                            <button @click="addTableRow" type="button" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm">
                                + সারি যোগ করুন
                            </button>
                        </div>
                    </div>

                    <!-- Table Matrix Input Area -->
                    <div class="overflow-x-auto max-h-[50vh] overflow-y-auto p-1 border rounded-2xl">
                        <table class="w-full text-xs text-left border-collapse">
                            <!-- Dynamic Table Headers -->
                            <thead>
                                <tr class="bg-gray-100 dark:bg-slate-900 border-b">
                                    <th class="p-2 text-center text-gray-400 font-normal w-8">#</th>
                                    <th v-for="(header, colIdx) in tableHeaders" :key="'h_'+colIdx" class="p-2 border-r">
                                        <div class="flex items-center space-x-1">
                                            <input
                                                v-model="tableHeaders[colIdx]"
                                                placeholder="কলামের নাম"
                                                class="w-full px-2 py-1 bg-white dark:bg-slate-800 border rounded font-bold text-teal-700 dark:text-teal-300 text-xs"
                                            />
                                            <button
                                                v-if="tableHeaders.length > 1"
                                                @click="removeTableColumn(colIdx)"
                                                type="button"
                                                class="text-rose-500 hover:bg-rose-50 p-1 rounded font-bold"
                                                title="এই কলামটি মুছুন"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </th>
                                    <th class="p-2 text-center w-10">মুছুন</th>
                                </tr>
                            </thead>
                            <!-- Dynamic Table Matrix Rows -->
                            <tbody>
                                <tr v-for="(row, rowIdx) in tableMatrix" :key="'r_'+rowIdx" class="border-b hover:bg-gray-50/50 dark:hover:bg-slate-900/30">
                                    <td class="p-2 text-center font-bold text-gray-400 text-[11px]">{{ rowIdx + 1 }}</td>
                                    <td v-for="(cell, colIdx) in row" :key="'c_'+rowIdx+'_'+colIdx" class="p-1.5 border-r">
                                        <input
                                            v-model="tableMatrix[rowIdx][colIdx]"
                                            placeholder="ডাটা লিখুন"
                                            class="w-full px-2 py-1 bg-gray-50 dark:bg-slate-900 border rounded text-xs"
                                        />
                                    </td>
                                    <td class="p-2 text-center">
                                        <button
                                            v-if="tableMatrix.length > 1"
                                            @click="removeTableRow(rowIdx)"
                                            type="button"
                                            class="text-rose-600 hover:bg-rose-50 p-1 rounded font-bold"
                                            title="এই সারিটি মুছুন"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button @click="isTableBuilderOpen = false" class="px-4 py-2 bg-gray-200 text-xs font-bold rounded-xl">বাতিল</button>
                        <button @click="generateAndInsertTable" class="px-5 py-2 bg-teal-600 text-white text-xs font-bold rounded-xl shadow">ইনসার্ট টেবিল</button>
                    </div>
                </div>
            </div>

            <!-- Graphical Math Builder Modal (No KaTeX Code Needed!) -->
            <div v-if="isMathBuilderOpen" class="fixed inset-0 z-[10000] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl max-w-md w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-lg font-bold">📐 ল.সা.গু / গ.সা.গু সমীকরণ ও ভগ্নাংশ বিল্ডার</h3>
                        <button @click="isMathBuilderOpen = false" class="text-gray-400">✕</button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold mb-1">সূত্র বা সমীকরণের নাম (যেমন: ভগ্নাংশের ল.সা.গু):</label>
                            <input v-model="mathForm.title" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">লব (Numerator / ওপরের অংশ):</label>
                            <input v-model="mathForm.numerator" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-emerald-600 font-bold" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">হর (Denominator / নিচের অংশ):</label>
                            <input v-model="mathForm.denominator" type="text" class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-indigo-600 font-bold" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button @click="isMathBuilderOpen = false" class="px-4 py-2 bg-gray-200 text-xs font-bold rounded-xl">বাতিল</button>
                        <button @click="generateAndInsertMath" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow">ইনসার্ট ম্যাথ ফর্মুলা</button>
                    </div>
                </div>
            </div>

            <!-- Question Inspection, Editing & Creation Modal -->
            <div v-if="selectedChapterForQA" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl max-w-3xl w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 uppercase">অধ্যায় প্রশ্নমালা</span>
                            <h3 class="text-xl font-bold">{{ selectedChapterForQA.title }}</h3>
                        </div>
                        <button @click="selectedChapterForQA = null" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <!-- Question Input / Edit Form -->
                    <div class="p-5 bg-gray-50 dark:bg-slate-900/60 border border-indigo-100 dark:border-slate-700 rounded-2xl space-y-3">
                        <h4 class="font-bold text-xs text-indigo-600 uppercase">
                            {{ editingQuestion ? '✏️ প্রশ্ন টি এডিট করুন' : '➕ নতুন প্রশ্ন যুক্ত করুন' }}
                        </h4>

                        <input v-model="qaForm.question_text" type="text" placeholder="প্রশ্নটি লিখুন..." class="w-full px-3 py-2 bg-white dark:bg-slate-800 border rounded-xl text-xs font-bengali" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div v-for="(opt, idx) in 4" :key="idx" class="flex items-center space-x-2 bg-white dark:bg-slate-800 p-2 border rounded-xl">
                                <input type="radio" name="correct_opt" :value="idx" v-model="qaForm.correct_option_index" />
                                <input v-model="qaForm.options[idx]" type="text" :placeholder="'অপশন ' + (idx + 1)" class="flex-1 px-2 py-1 border-none text-xs font-bengali focus:ring-0" />
                            </div>
                        </div>

                        <textarea v-model="qaForm.explanation" rows="2" placeholder="সঠিক উত্তরের ব্যাখ্যা..." class="w-full px-3 py-2 bg-white dark:bg-slate-800 border rounded-xl text-xs font-bengali"></textarea>

                        <div class="flex items-center justify-end space-x-2">
                            <button v-if="editingQuestion" @click="editingQuestion = null" class="px-3 py-1.5 bg-gray-200 text-xs font-bold rounded-xl">বাতিল</button>
                            <button @click="saveQuestion" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">
                                {{ editingQuestion ? 'আপডেট সেভ করুন' : 'প্রশ্ন সেভ করুন' }}
                            </button>
                        </div>
                    </div>

                    <!-- Existing Questions List -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-xs text-gray-500 uppercase">অধ্যায়ের বর্তমান প্রশ্নসমূহ ({{ selectedChapterForQA.questions?.length || 0 }}টি):</h4>
                        
                        <div v-if="!selectedChapterForQA.questions?.length" class="text-center py-6 text-xs text-gray-400">
                            এখনও কোনো প্রশ্ন যুক্ত করা হয়নি।
                        </div>

                        <div
                            v-for="(q, index) in selectedChapterForQA.questions"
                            :key="q.id"
                            class="p-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl flex items-start justify-between gap-4"
                        >
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-xs text-indigo-600">#{{ index + 1 }}</span>
                                    <span v-if="q.created_by_type === 'ai'" class="px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-full flex items-center space-x-1">
                                        <Cpu class="w-3 h-3" />
                                        <span>AI Generated</span>
                                    </span>
                                    <span v-else class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full flex items-center space-x-1">
                                        <User class="w-3 h-3" />
                                        <span>Human Created</span>
                                    </span>
                                </div>

                                <h5 class="font-bold text-sm mt-1 text-gray-900 dark:text-slate-100 font-bengali">{{ q.question_text }}</h5>
                                <div class="grid grid-cols-2 gap-1 text-xs text-gray-600 dark:text-slate-300 mt-2 font-bengali">
                                    <span v-for="(opt, optIdx) in q.options" :key="optIdx" :class="optIdx === q.correct_option_index ? 'text-emerald-600 font-bold' : ''">
                                        {{ optIdx + 1 }}. {{ opt }} {{ optIdx === q.correct_option_index ? '✓' : '' }}
                                    </span>
                                </div>
                                <p v-if="q.explanation" class="text-[11px] text-gray-400 mt-2 font-bengali">💡 {{ q.explanation }}</p>
                            </div>

                            <div class="flex items-center space-x-1">
                                <button @click="startEditQuestion(q)" class="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-xl" title="এডিট">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteQuestion(q.id)" class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-700 rounded-xl" title="ডিলিট">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-Chapter Creation Modal Dialog -->
            <div v-if="selectedChapterForSubChapter" class="fixed inset-0 z-[10000] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl max-w-md w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-base font-bold">➕ নতুন সাব-অধ্যায় তৈরি (অধ্যায়: {{ selectedChapterForSubChapter.title }})</h3>
                        <button @click="selectedChapterForSubChapter = null" class="text-gray-400">✕</button>
                    </div>

                    <form @submit.prevent="createSubChapter" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold mb-1">সাব-অধ্যায়ের শিরোনাম (Sub-Chapter Title):</label>
                            <input
                                v-model="subChapterForm.title"
                                type="text"
                                required
                                placeholder="যেমন: স্বরবর্ণ ও ব্যঞ্জনবর্ণ / সমাসের প্রকারভেদ..."
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold mb-1">গুরুত্ব নির্ধারণ (% Importance):</label>
                            <select
                                v-model="subChapterForm.importance_percentage"
                                class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-xs font-bold text-rose-600"
                            >
                                <option :value="95">৯৫% (বিসিএস হট টপিক 🔥)</option>
                                <option :value="90">৯০% (খুব গুরুত্বপূর্ণ ⭐)</option>
                                <option :value="85">৮৫% (গুরুত্বপূর্ণ)</option>
                                <option :value="75">৭৫% (সাধারণ)</option>
                                <option :value="60">৬০% (কম গুরুত্বপূর্ণ)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-2">
                            <button @click="selectedChapterForSubChapter = null" type="button" class="px-4 py-2 bg-gray-200 text-xs font-bold rounded-xl">বাতিল</button>
                            <button type="submit" class="px-5 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl shadow">সাব-অধ্যায় সেভ করুন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
