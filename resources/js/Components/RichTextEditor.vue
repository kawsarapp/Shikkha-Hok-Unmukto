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

onMounted(() => {
    if (window.Quill && editorContainer.value) {
        quillInstance = new window.Quill(editorContainer.value, {
            theme: 'snow',
            placeholder: props.placeholder,
            modules: {
                table: true,
                toolbar: [
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

        quillInstance.on('text-change', () => {
            const html = quillInstance.root.innerHTML;
            emit('update:modelValue', html === '<p><br></p>' ? '' : html);
        });
    }
});

watch(() => props.modelValue, (newVal) => {
    if (quillInstance && newVal !== quillInstance.root.innerHTML) {
        quillInstance.root.innerHTML = newVal || '';
    }
});

const insertHtmlAtCursor = (html) => {
    if (!quillInstance) return;
    const range = quillInstance.getSelection(true);
    const index = range ? range.index : quillInstance.getLength();
    quillInstance.clipboard.dangerouslyPasteHTML(index, html);
    const updatedHtml = quillInstance.root.innerHTML;
    emit('update:modelValue', updatedHtml);
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

defineExpose({
    insertHtmlAtCursor,
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm relative">
        <!-- Sticky Toolbar Header Wrapper -->
        <div class="sticky top-0 z-40 bg-slate-100 dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700">
            <!-- MS Word Quick Table Action Toolbar -->
            <div class="px-3 py-1.5 border-b border-gray-200/80 dark:border-slate-700 flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <span class="text-teal-700 dark:text-teal-400 font-extrabold mr-1">📝 MS Word টেবিল টুলস:</span>
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
                    ❌ কলাম ডিলিট
                </button>
                <button @click="handleDeleteRow" type="button" class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950 border border-rose-200 text-rose-600 rounded hover:bg-rose-100" title="কার্সরের সারিটি মুছুন">
                    ❌ সারি ডিলিট
                </button>
                <button @click="handleDeleteTable" type="button" class="px-2 py-0.5 bg-rose-600 text-white rounded hover:bg-rose-700" title="পুরো টেবিলটি মুছুন">
                    🗑️ টেবিল ডিলিট
                </button>
            </div>
        </div>

        <!-- Quill Editor Container -->
        <div ref="editorContainer" class="font-bengali text-sm text-gray-900 dark:text-slate-100"></div>
    </div>
</template>

<style>
/* Custom MS Word Responsive Styling for Quill Editor */
.ql-toolbar.ql-snow {
    background-color: #F8FAFC;
    border: none !important;
    border-bottom: 1px solid #E2E8F0 !important;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
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
    padding: 1rem !important;
    overflow-y: auto !important;
}

.ql-editor.ql-blank::before {
    font-style: normal !important;
    color: #94A3B8 !important;
}
</style>
