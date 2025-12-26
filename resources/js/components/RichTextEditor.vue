<template>
    <div class="rich-text-editor space-y-2">
        <!-- Editor Mode Toggle -->
        <div class="flex items-center justify-end">
            <div class="inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground">
                <button
                    @click="editorMode = 'wysiwyg'"
                    type="button"
                    :class="[
                        'inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                        editorMode === 'wysiwyg'
                            ? 'bg-background text-foreground shadow-sm'
                            : 'hover:bg-background/50 hover:text-foreground'
                    ]"
                >
                    WYSIWYG
                </button>
                <button
                    @click="editorMode = 'markdown'"
                    type="button"
                    :class="[
                        'inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                        editorMode === 'markdown'
                            ? 'bg-background text-foreground shadow-sm'
                            : 'hover:bg-background/50 hover:text-foreground'
                    ]"
                >
                    Markdown
                </button>
            </div>
        </div>

        <!-- Editor Container -->
        <div class="rounded-md border border-input bg-transparent overflow-hidden ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
            <!-- WYSIWYG Editor -->
            <div v-show="editorMode === 'wysiwyg'">
                <div ref="editor" class="editor-container"></div>
            </div>

            <!-- Markdown Editor -->
            <MarkdownEditor
                v-show="editorMode === 'markdown'"
                :model-value="markdownValue"
                @update:model-value="handleMarkdownUpdate"
                class="min-h-[300px] p-4 font-mono text-sm outline-none bg-transparent"
            />
        </div>
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

/* Deep selector to target Quill internals */
:deep(.ql-toolbar.ql-snow) {
    border: none;
    border-bottom: 1px solid hsl(var(--input));
    background-color: hsl(var(--muted) / 0.3);
    font-family: inherit;
}

:deep(.ql-container.ql-snow) {
    border: none;
    font-family: inherit;
    font-size: 0.875rem;
}

:deep(.ql-editor) {
    min-height: 300px;
    padding: 1rem;
}

/* Dark mode adjustments for toolbar icons */
:deep(.ql-snow .ql-stroke) {
    stroke: hsl(var(--muted-foreground));
}

:deep(.ql-snow .ql-fill) {
    fill: hsl(var(--muted-foreground));
}

:deep(.ql-snow .ql-picker) {
    color: hsl(var(--muted-foreground));
}

:deep(.ql-snow .ql-picker-options) {
    background-color: hsl(var(--popover));
    border-color: hsl(var(--border));
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

:deep(.ql-snow .ql-picker-item:hover),
:deep(.ql-snow .ql-picker-label:hover) {
    color: hsl(var(--primary));
}

:deep(.ql-snow button:hover .ql-stroke),
:deep(.ql-snow .ql-picker-label:hover .ql-stroke) {
    stroke: hsl(var(--primary));
}

:deep(.ql-snow button:hover .ql-fill),
:deep(.ql-snow .ql-picker-label:hover .ql-fill) {
    fill: hsl(var(--primary));
}

:deep(.ql-snow .ql-active .ql-stroke) {
    stroke: hsl(var(--primary));
}

:deep(.ql-snow .ql-active .ql-fill) {
    fill: hsl(var(--primary));
}
</style>

