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

onMounted(() => {
    if (window.Quill && editorContainer.value) {
        quillInstance = new window.Quill(editorContainer.value, {
            theme: 'snow',
            placeholder: props.placeholder,
            modules: {
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
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
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
