<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'এখানে MS Word এর মতো টাইপ করুন, ফরম্যাট করুন অথবা ছবি ড্র্যাগ-এন্ড-ড্রপ করুন...',
    },
});

const emit = defineEmits(['update:modelValue']);
const editorContainer = ref(null);
let quillInstance = null;
let tableModule = null;

// Live Word & Character Counter State
const wordCount = ref(0);
const charCount = ref(0);

// Find & Replace Modal State inside Editor
const isFindReplaceOpen = ref(false);
const findText = ref('');
const replaceText = ref('');
const replaceCount = ref(null);

const updateMetrics = () => {
    if (!quillInstance) return;
    const text = quillInstance.getText() || '';
    const cleanText = text.trim();
    charCount.value = cleanText.length;
    wordCount.value = cleanText ? cleanText.split(/\s+/).filter(Boolean).length : 0;
};

onMounted(() => {
    if (window.Quill && editorContainer.value) {
        // Register custom Bengali Font Family Whitelist
        const Font = window.Quill.import('formats/font');
        Font.whitelist = ['hind-siliguri', 'noto-sans-bengali', 'tiro-bangla', 'anek-bangla', 'atma', 'mina', 'galada'];
        window.Quill.register(Font, true);

        quillInstance = new window.Quill(editorContainer.value, {
            theme: 'snow',
            placeholder: props.placeholder,
            modules: {
                table: true,
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true,
                },
                toolbar: [
                    [{ font: ['hind-siliguri', 'noto-sans-bengali', 'tiro-bangla', 'anek-bangla', 'atma', 'mina', 'galada'] }, { size: ['small', false, 'large', 'huge'] }],
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ align: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['clean'],
                ],
            },
        });

        tableModule = quillInstance.getModule('table');

        if (props.modelValue) {
            quillInstance.root.innerHTML = props.modelValue;
        }
        updateMetrics();

        quillInstance.on('text-change', () => {
            const html = quillInstance.root.innerHTML;
            emit('update:modelValue', html === '<p><br></p>' ? '' : html);
            updateMetrics();
        });
    }
});

watch(() => props.modelValue, (newVal) => {
    if (quillInstance && newVal !== quillInstance.root.innerHTML) {
        quillInstance.root.innerHTML = newVal || '';
        updateMetrics();
    }
});

const insertHtmlAtCursor = (html) => {
    if (!quillInstance) return;
    const range = quillInstance.getSelection(true);
    const index = range ? range.index : quillInstance.getLength();
    quillInstance.clipboard.dangerouslyPasteHTML(index, html);
    const updatedHtml = quillInstance.root.innerHTML;
    emit('update:modelValue', updatedHtml);
    updateMetrics();
};

// MS Word Direct Table Manipulation Actions
const handleInsertColLeft = () => {
    if (tableModule) tableModule.insertColumnLeft();
};

const handleInsertColRight = () => {
    if (tableModule) tableModule.insertColumnRight();
};

const handleInsertRowAbove = () => {
    if (tableModule) tableModule.insertRowAbove();
};

const handleInsertRowBelow = () => {
    if (tableModule) tableModule.insertRowBelow();
};

const handleDeleteCol = () => {
    if (tableModule) tableModule.deleteColumn();
};

const handleDeleteRow = () => {
    if (tableModule) tableModule.deleteRow();
};

const handleDeleteTable = () => {
    if (tableModule) tableModule.deleteTable();
};

// Undo / Redo Actions
const handleUndo = () => {
    if (quillInstance?.history) quillInstance.history.undo();
};

const handleRedo = () => {
    if (quillInstance?.history) quillInstance.history.redo();
};

// Marker Highlight Action
const setHighlight = (color) => {
    if (!quillInstance) return;
    const range = quillInstance.getSelection();
    if (range) {
        quillInstance.format('background', color);
    }
};

// Find & Replace Action
const performFindReplace = () => {
    if (!quillInstance || !findText.value) return;
    const content = quillInstance.root.innerHTML;
    const regex = new RegExp(findText.value, 'g');
    const matches = (content.match(regex) || []).length;
    
    if (matches > 0) {
        const newContent = content.replace(regex, replaceText.value);
        quillInstance.root.innerHTML = newContent;
        emit('update:modelValue', newContent);
        updateMetrics();
        replaceCount.value = matches;
    } else {
        replaceCount.value = 0;
    }
};

// Print / PDF Export
const exportPdf = () => {
    if (!quillInstance) return;
    const content = quillInstance.root.innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>পাঠ্যসামগ্রী PDF প্রিন্ট</title>
            <style>
                body { font-family: 'Hind Siliguri', sans-serif; padding: 2rem; color: #1e293b; line-height: 1.8; }
                table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
                th { background-color: #0f766e; color: white; padding: 8px; border: 1px solid #0d9488; }
                td { padding: 8px; border: 1px solid #cbd5e1; text-align: center; }
                tr:nth-child(even) { background-color: #f0fdf4; }
                .book-header-ribbon { text-align: center; border: 3px double #1e293b; padding: 10px; font-weight: bold; font-size: 1.5rem; margin: 1rem 0; }
                .math-formula-box { background-color: #f1f5f9; border-left: 4px solid #4f46e5; padding: 1rem; margin: 1rem 0; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <div>${content}</div>
            <script>
                window.onload = function() { window.print(); window.close(); };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
};

defineExpose({
    insertHtmlAtCursor,
    exportPdf,
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm relative flex flex-col">
        <!-- Sticky Toolbar Header Wrapper -->
        <div class="sticky top-0 z-40 bg-slate-100 dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 flex-shrink-0 space-y-1">
            <!-- MS Word Quick Toolbar Actions (Undo/Redo, Find/Replace, Table Tools, PDF Export) -->
            <div class="px-3 py-1.5 border-b border-gray-200/80 dark:border-slate-700 flex flex-wrap items-center justify-between gap-1.5 text-xs font-bold">
                <!-- Left: Table & Formatting Actions -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-teal-700 dark:text-teal-400 font-extrabold mr-1">📝 MS Word টুলস:</span>
                    
                    <!-- Undo & Redo -->
                    <button @click="handleUndo" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-gray-100 shadow-sm" title="Undo (পূর্বে ফেরত)">
                        ↩️ Undo
                    </button>
                    <button @click="handleRedo" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-gray-100 shadow-sm" title="Redo (পুনরায় করা)">
                        ↪️ Redo
                    </button>

                    <div class="h-4 w-px bg-gray-300 dark:bg-slate-600 mx-1"></div>

                    <!-- Highlight Marker Color Picker -->
                    <span class="text-gray-500 font-bold">🖍️ হাইলাইটার:</span>
                    <button @click="setHighlight('#FEF08A')" type="button" class="w-5 h-5 rounded-full bg-yellow-200 border border-yellow-400 hover:scale-110 transition" title="হলুদ হাইলাইট"></button>
                    <button @click="setHighlight('#BBF7D0')" type="button" class="w-5 h-5 rounded-full bg-green-200 border border-green-400 hover:scale-110 transition" title="সবুজ হাইলাইট"></button>
                    <button @click="setHighlight('#A5F3FC')" type="button" class="w-5 h-5 rounded-full bg-cyan-200 border border-cyan-400 hover:scale-110 transition" title="নীল হাইলাইট"></button>
                    <button @click="setHighlight('#FBCFE8')" type="button" class="w-5 h-5 rounded-full bg-pink-200 border border-pink-400 hover:scale-110 transition" title="গোলাপি হাইলাইট"></button>
                    <button @click="setHighlight(false)" type="button" class="px-1.5 py-0.5 text-[10px] bg-gray-200 rounded text-gray-600">রিমুভ</button>

                    <div class="h-4 w-px bg-gray-300 dark:bg-slate-600 mx-1"></div>

                    <!-- Table Tools -->
                    <button @click="handleInsertColLeft" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-teal-50" title="কার্সরের বামে নতুন কলাম যোগ করুন">
                        + ⬅️ কলাম বামে
                    </button>
                    <button @click="handleInsertColRight" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-teal-50" title="কার্সরের ডানে নতুন কলাম যোগ করুন">
                        + ➡️ কলাম ডানে
                    </button>
                    <button @click="handleInsertRowAbove" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-indigo-50" title="কার্সরের ওপরে নতুন সারি যোগ করুন">
                        + ⬆️ সারি ওপরে
                    </button>
                    <button @click="handleInsertRowBelow" type="button" class="px-2 py-0.5 bg-white dark:bg-slate-700 border rounded hover:bg-indigo-50" title="কার্সরের নিচে নতুন সারি যোগ করুন">
                        + ⬇️ সারি নিচে
                    </button>
                    <button @click="handleDeleteCol" type="button" class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950 border border-rose-200 text-rose-600 rounded hover:bg-rose-100" title="কার্সরের কলামটি মুছুন">
                        ❌ কলাম
                    </button>
                    <button @click="handleDeleteRow" type="button" class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950 border border-rose-200 text-rose-600 rounded hover:bg-rose-100" title="কার্সরের সারিটি মুছুন">
                        ❌ সারি
                    </button>
                </div>

                <!-- Right Actions: Find & Replace & Print PDF -->
                <div class="flex items-center space-x-1.5">
                    <button @click="isFindReplaceOpen = true" type="button" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-extrabold flex items-center space-x-1 shadow-sm">
                        <span>🔍 খুঁজুন ও পরিবর্তন</span>
                    </button>
                    <button @click="exportPdf" type="button" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-extrabold flex items-center space-x-1 shadow-sm">
                        <span>📥 PDF ডাউনলোড / প্রিন্ট</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quill Editor Container -->
        <div ref="editorContainer" class="font-bengali text-sm text-gray-900 dark:text-slate-100 flex-1"></div>

        <!-- MS Word Bottom Status Bar (Live Word & Character Counter) -->
        <div class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between text-xs text-gray-600 dark:text-slate-400 font-bold flex-shrink-0">
            <div class="flex items-center space-x-4">
                <span>📝 মোট শব্দ: <strong class="text-indigo-600 dark:text-indigo-400 font-extrabold">{{ wordCount }}</strong>টি</span>
                <span>অক্ষর সংখ্যা: <strong class="text-purple-600 dark:text-purple-400 font-extrabold">{{ charCount }}</strong>টি</span>
            </div>
            <div class="text-[11px] text-gray-400">
                স্বয়ংক্রিয় MS Word রিচ টেক্সট ফরম্যাট এনজিন
            </div>
        </div>

        <!-- Find & Replace Modal Dialog -->
        <div v-if="isFindReplaceOpen" class="fixed inset-0 z-[10000] bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl max-w-md w-full border border-gray-200 dark:border-slate-700 shadow-2xl space-y-4 text-xs">
                <div class="flex items-center justify-between pb-2 border-b">
                    <h3 class="text-base font-bold text-gray-900 dark:text-slate-100">🔍 খুঁজুন ও পরিবর্তন (Find & Replace)</h3>
                    <button @click="isFindReplaceOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-bold mb-1">কোন শব্দটি খুঁজবেন (Find Text):</label>
                        <input
                            v-model="findText"
                            type="text"
                            placeholder="যেমন: চর্যাপদ / ১৯০৭..."
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl"
                        />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">নতুন যা দিয়ে পরিবর্তন করবেন (Replace With):</label>
                        <input
                            v-model="replaceText"
                            type="text"
                            placeholder="যেমন: শ্রীকৃষ্ণকীর্তন / ১৯১২..."
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-900 border rounded-xl text-indigo-600 font-bold"
                        />
                    </div>

                    <div v-if="replaceCount !== null" class="p-2 rounded-xl text-center font-bold" :class="replaceCount > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                        {{ replaceCount > 0 ? `✓ মোট ${replaceCount}টি স্থানে সফলভাবে পরিবর্তন করা হয়েছে!` : '⚠️ উল্লিখিত শব্দটি খুঁজে পাওয়া যায়নি!' }}
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button @click="isFindReplaceOpen = false" type="button" class="px-4 py-2 bg-gray-200 text-xs font-bold rounded-xl">বন্ধ করুন</button>
                    <button @click="performFindReplace" type="button" class="px-5 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow">সবগুলো পরিবর্তন করুন</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Custom MS Word Responsive Styling for Quill Editor */
.ql-toolbar.ql-snow {
    background-color: #F8FAFC;
    border: none !important;
    border-bottom: 1px solid #E2E8F0 !important;
    padding: 8px !important;
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 4px !important;
}

.dark .ql-toolbar.ql-snow {
    background-color: #0F172A;
    border-bottom-color: #334155 !important;
}

.ql-container.ql-snow {
    border: none !important;
    font-family: 'Hind Siliguri', sans-serif !important;
    font-size: 1rem !important;
    min-height: 320px !important;
    resize: vertical !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-y !important;
}

.ql-editor {
    min-height: 300px !important;
    padding: 1.25rem !important;
    overflow-y: auto !important;
    line-height: 1.85 !important;
}

.ql-editor.ql-blank::before {
    font-style: normal !important;
    color: #94A3B8 !important;
}
</style>
