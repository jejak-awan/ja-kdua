<template>
    <div class="markdown-editor">
        <!-- Toolbar -->
        <div class="flex items-center justify-between border-b border-border p-2 bg-muted">
            <div class="flex items-center space-x-2">
                <button
                    v-for="tool in toolbar"
                    :key="tool.action"
                    @click="insertMarkdown(tool.action, tool.syntax)"
                    :title="tool.title"
                    class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded"
                >
                    <svg v-if="tool.icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="tool.icon"></svg>
                    <span v-else>{{ tool.label }}</span>
                </button>
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    @click="togglePreview"
                    size="sm"
                    :variant="showPreview ? 'default' : 'secondary'"
                >
                    {{ showPreview ? 'Edit' : 'Preview' }}
                </Button>
            </div>
        </div>

        <!-- Editor/Preview Split -->
        <div class="flex" :class="{ 'h-96': true }">
            <!-- Markdown Editor -->
            <div
                v-if="!showPreview || splitView"
                class="flex-1 border-r border-border"
                :class="{ 'w-1/2': splitView, 'w-full': !splitView }"
            >
                <textarea
                    ref="textarea"
                    v-model="markdownContent"
                    @input="handleInput"
                    class="w-full h-full p-4 font-mono text-sm border-0 resize-none focus:outline-none"
                    placeholder="Write your markdown here..."
                    spellcheck="false"
                ></textarea>
            </div>

            <!-- Preview Pane -->
            <div
                v-if="showPreview || splitView"
                class="flex-1 overflow-auto p-4 prose max-w-none"
                :class="{ 'w-1/2': splitView, 'w-full': !splitView }"
                v-html="renderedPreview"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/github.min.css';
import Button from './ui/button.vue';

// Configure marked
marked.setOptions({
    highlight: function(code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (err) {}
        }
        return hljs.highlightAuto(code).value;
    },
    breaks: true,
    gfm: true,
});

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const textarea = ref(null);
const markdownContent = ref('');
const showPreview = ref(false);
const splitView = ref(false);

// Configure marked
marked.setOptions({
    highlight: function(code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (err) {}
        }
        return hljs.highlightAuto(code).value;
    },
    breaks: true,
    gfm: true,
});

const toolbar = [
    { action: 'bold', syntax: '**text**', title: 'Bold', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>' },
    { action: 'italic', syntax: '*text*', title: 'Italic', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>' },
    { action: 'heading', syntax: '## Heading', title: 'Heading', label: 'H' },
    { action: 'link', syntax: '[text](url)', title: 'Link', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>' },
    { action: 'image', syntax: '![alt](url)', title: 'Image', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>' },
    { action: 'code', syntax: '`code`', title: 'Inline Code', label: '</>' },
    { action: 'codeblock', syntax: '```\ncode\n```', title: 'Code Block', label: '{}' },
    { action: 'list', syntax: '- item', title: 'List', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>' },
    { action: 'quote', syntax: '> quote', title: 'Quote', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>' },
];

const renderedPreview = computed(() => {
    if (!markdownContent.value) return '';
    try {
        return marked.parse(markdownContent.value);
    } catch (e) {
        console.error('Markdown parsing error:', e);
        return markdownContent.value;
    }
});

const handleInput = () => {
    emit('update:modelValue', markdownContent.value);
};

const insertMarkdown = (action, syntax) => {
    if (!textarea.value) return;

    const textareaEl = textarea.value;
    const start = textareaEl.selectionStart;
    const end = textareaEl.selectionEnd;
    const selectedText = markdownContent.value.substring(start, end);
    const before = markdownContent.value.substring(0, start);
    const after = markdownContent.value.substring(end);

    let replacement = '';

    switch (action) {
        case 'bold':
            replacement = selectedText ? `**${selectedText}**` : '**bold text**';
            break;
        case 'italic':
            replacement = selectedText ? `*${selectedText}*` : '*italic text*';
            break;
        case 'heading':
            replacement = selectedText ? `## ${selectedText}` : '## Heading';
            break;
        case 'link':
            replacement = selectedText ? `[${selectedText}](url)` : '[link text](url)';
            break;
        case 'image':
            replacement = '![alt text](image-url)';
            break;
        case 'code':
            replacement = selectedText ? `\`${selectedText}\`` : '`code`';
            break;
        case 'codeblock':
            replacement = selectedText ? `\`\`\`\n${selectedText}\n\`\`\`` : '```\ncode\n```';
            break;
        case 'list':
            replacement = selectedText ? `- ${selectedText.split('\n').join('\n- ')}` : '- List item';
            break;
        case 'quote':
            replacement = selectedText ? `> ${selectedText.split('\n').join('\n> ')}` : '> Quote';
            break;
        default:
            replacement = syntax;
    }

    markdownContent.value = before + replacement + after;
    
    nextTick(() => {
        textareaEl.focus();
        const newStart = start + replacement.length;
        textareaEl.setSelectionRange(newStart, newStart);
    });

    handleInput();
};

const togglePreview = () => {
    if (showPreview.value) {
        showPreview.value = false;
        splitView.value = false;
    } else {
        showPreview.value = true;
        splitView.value = true;
    }
};

// Convert HTML to Markdown on mount if needed
watch(() => props.modelValue, (newValue) => {
    if (newValue && newValue !== markdownContent.value) {
        // Check if it's HTML
        if (newValue.trim().startsWith('<')) {
            // Simple HTML to Markdown conversion (basic)
            markdownContent.value = htmlToMarkdown(newValue);
        } else {
            markdownContent.value = newValue;
        }
    }
}, { immediate: true });

// Simple HTML to Markdown converter
const htmlToMarkdown = (html) => {
    // This is a basic converter - you might want to use a library like turndown
    let md = html
        .replace(/<h1>(.*?)<\/h1>/gi, '# $1\n')
        .replace(/<h2>(.*?)<\/h2>/gi, '## $1\n')
        .replace(/<h3>(.*?)<\/h3>/gi, '### $1\n')
        .replace(/<strong>(.*?)<\/strong>/gi, '**$1**')
        .replace(/<b>(.*?)<\/b>/gi, '**$1**')
        .replace(/<em>(.*?)<\/em>/gi, '*$1*')
        .replace(/<i>(.*?)<\/i>/gi, '*$1*')
        .replace(/<a href="(.*?)">(.*?)<\/a>/gi, '[$2]($1)')
        .replace(/<img src="(.*?)" alt="(.*?)"\/?>/gi, '![$2]($1)')
        .replace(/<ul>(.*?)<\/ul>/gis, (match, content) => {
            return content.replace(/<li>(.*?)<\/li>/gi, '- $1\n');
        })
        .replace(/<ol>(.*?)<\/ol>/gis, (match, content) => {
            let counter = 1;
            return content.replace(/<li>(.*?)<\/li>/gi, () => `${counter++}. $1\n`);
        })
        .replace(/<p>(.*?)<\/p>/gi, '$1\n\n')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<[^>]+>/g, '')
        .trim();
    
    return md;
};
</script>

<style scoped>
.markdown-editor {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    overflow: hidden;
}

textarea {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    line-height: 1.6;
    background-color: transparent;
    color: hsl(var(--foreground));
}

.prose {
    color: hsl(var(--foreground));
}

.prose :deep(h1), .prose :deep(h2), .prose :deep(h3), .prose :deep(h4) {
    color: hsl(var(--foreground));
}

.prose :deep(code) {
    background-color: hsl(var(--muted));
    color: hsl(var(--foreground));
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.prose :deep(blockquote) {
    border-left: 4px solid hsl(var(--border));
    padding-left: 1rem;
    font-style: italic;
    color: hsl(var(--muted-foreground));
}
</style>

