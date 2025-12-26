<template>
    <node-view-wrapper class="html-embed-wrapper">
        <div class="html-embed-container">
            <!-- Edit Mode -->
            <div v-if="isEditing" class="html-embed-editor">
                <div class="html-embed-header">
                    <span class="html-embed-label">
                        <Code class="w-4 h-4" />
                        HTML Embed
                    </span>
                    <div class="flex gap-1">
                        <button class="html-embed-btn success" @click="saveHtml" title="Save">
                            <Check class="w-4 h-4" />
                        </button>
                        <button class="html-embed-btn" @click="cancelEdit" title="Cancel">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <textarea 
                    v-model="editableHtml"
                    class="html-embed-textarea"
                    placeholder="Paste your HTML code here (iframe, embed, map, etc.)..."
                    rows="6"
                ></textarea>
            </div>

            <!-- Preview Mode -->
            <div v-else class="html-embed-preview">
                <div class="html-embed-header">
                    <span class="html-embed-label">
                        <Code class="w-4 h-4" />
                        HTML Embed
                    </span>
                    <div class="flex gap-1">
                        <button class="html-embed-btn" @click="startEdit" title="Edit HTML">
                            <Pencil class="w-4 h-4" />
                        </button>
                        <button class="html-embed-btn danger" @click="deleteNode" title="Delete">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <div 
                    v-if="node.attrs.html"
                    ref="contentRef"
                    class="html-embed-content"
                ></div>
                <div v-else class="html-embed-empty">
                    <p>Click edit to add HTML content</p>
                </div>
            </div>
        </div>
    </node-view-wrapper>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { NodeViewWrapper } from '@tiptap/vue-3'
import { Code, Pencil, Trash2, Check, X } from 'lucide-vue-next'

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    updateAttributes: {
        type: Function,
        required: true,
    },
    deleteNode: {
        type: Function,
        required: true,
    },
})

const isEditing = ref(!props.node.attrs.html)
const editableHtml = ref(props.node.attrs.html || '')
const contentRef = ref(null)

const sanitizedHtml = computed(() => {
    // Allow iframes for maps/videos, but sanitize script tags for security
    let html = props.node.attrs.html || ''
    // Remove script tags for security but keep iframes
    html = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    return html
})

function renderContent() {
    nextTick(() => {
        if (contentRef.value && sanitizedHtml.value) {
            contentRef.value.innerHTML = sanitizedHtml.value
        }
    })
}

onMounted(() => {
    if (!isEditing.value && props.node.attrs.html) {
        renderContent()
    }
})

watch(() => props.node.attrs.html, () => {
    if (!isEditing.value) {
        renderContent()
    }
})

watch(isEditing, (newVal) => {
    if (!newVal && props.node.attrs.html) {
        renderContent()
    }
})

function startEdit() {
    editableHtml.value = props.node.attrs.html || ''
    isEditing.value = true
}

function saveHtml() {
    props.updateAttributes({ html: editableHtml.value })
    isEditing.value = false
}

function cancelEdit() {
    editableHtml.value = props.node.attrs.html || ''
    isEditing.value = false
}
</script>

<style>
.html-embed-wrapper {
    margin: 1rem 0;
}

.html-embed-container {
    border: 2px dashed hsl(var(--border));
    border-radius: 0.5rem;
    overflow: hidden;
    background: hsl(var(--muted) / 0.3);
}

.html-embed-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: hsl(var(--muted));
    border-bottom: 1px solid hsl(var(--border));
}

.html-embed-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: hsl(var(--muted-foreground));
}

.html-embed-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem;
    border-radius: 0.25rem;
    background: transparent;
    border: none;
    color: hsl(var(--muted-foreground));
    cursor: pointer;
    transition: all 0.2s;
}

.html-embed-btn:hover {
    background: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}

.html-embed-btn.success:hover {
    background: hsl(142 76% 36%);
    color: white;
}

.html-embed-btn.danger:hover {
    background: hsl(var(--destructive));
    color: white;
}

.html-embed-textarea {
    width: 100%;
    padding: 0.75rem;
    border: none;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    resize: vertical;
    background: hsl(var(--background));
    color: hsl(var(--foreground));
}

.html-embed-textarea:focus {
    outline: none;
}

.html-embed-content {
    padding: 1rem;
    min-height: 100px;
}

.html-embed-content iframe {
    max-width: 100%;
    border-radius: 0.25rem;
}

.html-embed-empty {
    padding: 2rem;
    text-align: center;
    color: hsl(var(--muted-foreground));
    font-size: 0.875rem;
}
</style>
