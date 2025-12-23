<template>
    <div class="rich-text-editor">
        <!-- Editor Mode Toggle -->
        <div class="flex items-center justify-end mb-2">
            <div class="flex items-center space-x-2 border border-gray-300 rounded-md p-1 bg-card">
                <button
                    @click="editorMode = 'wysiwyg'"
                    :class="[
                        'px-3 py-1 text-sm rounded',
                        editorMode === 'wysiwyg'
                            ? 'bg-indigo-600 text-white'
                            : 'text-foreground hover:bg-accent'
                    ]"
                >
                    WYSIWYG
                </button>
                <button
                    @click="editorMode = 'markdown'"
                    :class="[
                        'px-3 py-1 text-sm rounded',
                        editorMode === 'markdown'
                            ? 'bg-indigo-600 text-white'
                            : 'text-foreground hover:bg-accent'
                    ]"
                >
                    Markdown
                </button>
            </div>
        </div>

        <!-- WYSIWYG Editor -->
        <div v-if="editorMode === 'wysiwyg'">
            <div ref="editor" class="editor-container" />
        </div>

        <!-- Markdown Editor -->
        <MarkdownEditor
            v-else
            :model-value="markdownValue"
            @update:model-value="handleMarkdownUpdate"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount, computed, nextTick } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import MarkdownEditor from './MarkdownEditor.vue';
import { marked } from 'marked';
import TurndownService from 'turndown';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const editorMode = ref('wysiwyg');
const markdownValue = ref('');
let quill = null;
let turndown = null;

// Initialize Turndown for HTML to Markdown conversion
const initTurndown = () => {
    if (!turndown) {
        turndown = new TurndownService({
            headingStyle: 'atx',
            codeBlockStyle: 'fenced',
        });
    }
};

// Convert HTML to Markdown
const htmlToMarkdown = (html) => {
    if (!html) return '';
    initTurndown();
    return turndown.turndown(html);
};

// Convert Markdown to HTML
const markdownToHtml = (markdown) => {
    if (!markdown) return '';
    return marked.parse(markdown);
};

// Handle mode switch
watch(() => editorMode.value, (newMode, oldMode) => {
    if (oldMode === 'wysiwyg' && newMode === 'markdown') {
        // Convert HTML to Markdown
        if (quill) {
            const html = quill.root.innerHTML;
            markdownValue.value = htmlToMarkdown(html);
        } else {
            // If quill not initialized, convert from modelValue
            markdownValue.value = htmlToMarkdown(props.modelValue || '');
        }
    } else if (oldMode === 'markdown' && newMode === 'wysiwyg') {
        // Convert Markdown to HTML
        const html = markdownToHtml(markdownValue.value);
        if (quill) {
            quill.root.innerHTML = html;
        }
        emit('update:modelValue', html);
    }
});

// Handle markdown update
const handleMarkdownUpdate = (value) => {
    markdownValue.value = value;
    const html = markdownToHtml(value);
    emit('update:modelValue', html);
};

const initQuill = () => {
    if (quill || !editor.value) return;

    quill = new Quill(editor.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image', 'video'],
            ],
        },
    });

    // Set initial content
    if (props.modelValue) {
        quill.root.innerHTML = props.modelValue;
    }

    // Listen for text changes
    quill.on('text-change', () => {
        emit('update:modelValue', quill.root.innerHTML);
    });
};

onMounted(() => {
    initTurndown();

    // Initialize Quill if WYSIWYG mode
    if (editorMode.value === 'wysiwyg') {
        initQuill();
    }

    // Initialize markdown value if needed
    if (props.modelValue && editorMode.value === 'markdown') {
        markdownValue.value = htmlToMarkdown(props.modelValue);
    }
});

// Watch for mode change to initialize Quill when switching to WYSIWYG
watch(() => editorMode.value, (newMode) => {
    if (newMode === 'wysiwyg' && !quill) {
        nextTick(() => {
            initQuill();
        });
    }
});

watch(() => props.modelValue, (newValue) => {
    if (editorMode.value === 'wysiwyg' && quill && quill.root.innerHTML !== newValue) {
        quill.root.innerHTML = newValue || '';
    } else if (editorMode.value === 'markdown') {
        const md = htmlToMarkdown(newValue || '');
        if (markdownValue.value !== md) {
            markdownValue.value = md;
        }
    }
});

onBeforeUnmount(() => {
    if (quill) {
        quill = null;
    }
});
</script>

<style scoped>
.editor-container {
    min-height: 300px;
}
</style>

