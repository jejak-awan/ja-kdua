<template>
    <Card class="overflow-hidden relative" @contextmenu="handleContextMenu">
        <!-- Toolbar Component -->
        <Toolbar 
            v-if="editor" 
            :editor="editor" 
            :show-html-view="showHtmlView"
            @insert-table="showTableDialog = true"
            @open-media="showMediaPicker = true"
            @insert-html="insertHtmlEmbed"
            @toggle-html="toggleHtmlView"
        />

        <!-- Editor Content (WYSIWYG View) -->
        <editor-content 
            v-show="!showHtmlView"
            :editor="editor" 
            class="prose prose-sm sm:prose-base dark:prose-invert max-w-none focus:outline-none min-h-[400px] p-4 text-card-foreground bg-card" 
        />

        <!-- HTML Source View -->
        <div v-show="showHtmlView" class="html-view">
            <textarea
                v-model="htmlContent"
                class="w-full min-h-[400px] p-4 font-mono text-sm bg-card text-card-foreground border-none resize-y focus:outline-none focus:ring-0"
                placeholder="<p>Your HTML code here...</p>"
                @blur="applyHtmlChanges"
            ></textarea>
        </div>
        
        <!-- Standard Bubble Menu -->
        <BubbleMenu :editor="editor" />

        <!-- Media Specific Bubble Menu -->
        <MediaBubbleMenu :editor="editor" @open-properties="openProperties" />

        <!-- Media Properties Popover -->
        <PropertiesPopover 
            v-model:open="showPropertiesModal"
            :node="selectedNodeForProperties"
            :anchor="propertiesAnchor"
            @save="saveMediaProperties"
        />

        <!-- Context Menu -->
        <ContextMenu 
            :show="contextMenu.show"
            :position="contextMenu.position"
            :items="contextMenu.items"
            @close="contextMenu.show = false"
            @action="handleContextMenuAction"
        />

        <!-- Media Picker Modal -->
        <MediaPicker
            v-model:open="showMediaPicker"
            @selected="handleMediaSelect"
        >
            <template #trigger><div class="hidden"></div></template>
        </MediaPicker>

        <!-- Table Insert Dialog -->
        <TableInsertDialog 
            v-model:open="showTableDialog"
            @insert="insertTable"
        />
    </Card>
</template>

<script setup>
import { ref, watch, onBeforeUnmount, reactive, nextTick } from 'vue'
import Card from '@/components/ui/card.vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import BubbleMenuExtension from '@tiptap/extension-bubble-menu'
import Typography from '@tiptap/extension-typography'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import { FontFamily } from '@tiptap/extension-font-family'
import { Color } from '@tiptap/extension-color'
import Highlight from '@tiptap/extension-highlight'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'

// Modular Components
import Toolbar from './editor/Toolbar.vue'
import BubbleMenu from './editor/BubbleMenu.vue'
import MediaBubbleMenu from './editor/MediaBubbleMenu.vue'
import ContextMenu from './editor/ContextMenu.vue'
import PropertiesPopover from './editor/PropertiesPopover.vue'
import TableInsertDialog from './editor/TableInsertDialog.vue'
import MediaPicker from './MediaPicker.vue'

// Custom Extensions
import { CustomImage } from './editor/extensions/CustomImage'
import { VideoExtension } from './editor/extensions/Video'
import { Dropcap } from './editor/extensions/Dropcap'
import { Columns } from './editor/extensions/Columns'
import { Column } from './editor/extensions/Column'
import { TextColumns } from './editor/extensions/TextColumns'
import { CodeBlockWithCopyExtension } from './editor/extensions/CodeBlockExtension'
import { HtmlEmbed } from './editor/extensions/HtmlEmbedExtension'

import { createLowlight } from 'lowlight'
import javascript from 'highlight.js/lib/languages/javascript'
import typescript from 'highlight.js/lib/languages/typescript'
import html from 'highlight.js/lib/languages/xml'
import css from 'highlight.js/lib/languages/css'
import php from 'highlight.js/lib/languages/php'
import python from 'highlight.js/lib/languages/python'
import json from 'highlight.js/lib/languages/json'
import bash from 'highlight.js/lib/languages/bash'
import sql from 'highlight.js/lib/languages/sql'
import markdown from 'highlight.js/lib/languages/markdown'

import { 
    Settings as SettingsIcon,
    Trash2,
    Copy,
    Bold,
    Italic,
    Undo,
    Redo,
    RemoveFormatting
} from 'lucide-vue-next'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Start writing...',
    }
})

const emit = defineEmits(['update:modelValue'])

const showMediaPicker = ref(false)
const showTableDialog = ref(false)
const showHtmlView = ref(false)
const htmlContent = ref('')

// Properties Popover State
const showPropertiesModal = ref(false)
const selectedNodeForProperties = ref(null)
const propertiesAnchor = ref(null)

// Context Menu State
const contextMenu = reactive({
    show: false,
    x: 0,
    y: 0,
    items: [],
    position: { x: 0, y: 0 }
})

// Create lowlight instance
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('typescript', typescript)
lowlight.register('html', html)
lowlight.register('css', css)
lowlight.register('php', php)
lowlight.register('python', python)
lowlight.register('json', json)
lowlight.register('bash', bash)
lowlight.register('sql', sql)
lowlight.register('markdown', markdown)

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            codeBlock: false,
            paragraph: false,
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        CustomImage,
        VideoExtension,
        Dropcap,
        Columns,
        Column,
        TextColumns,
        Typography,
        BubbleMenuExtension,
        TextAlign.configure({
            types: ['heading', 'paragraph', 'image', 'video'],
        }),
        TextStyle,
        FontFamily,
        Color,
        Highlight.configure({
            multicolor: true,
        }),
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        CodeBlockWithCopyExtension.configure({
            lowlight,
        }),
        HtmlEmbed,
    ],
    editorProps: {
        attributes: {
            class: 'focus:outline-none min-h-[400px]',
        },
        handleDOMEvents: {
            dblclick: (view, event) => {
                // Check if we clicked on a media node
                if (editor.value && (editor.value.isActive('image') || editor.value.isActive('video') || editor.value.isActive('htmlEmbed'))) {
                    openProperties()
                    return true
                }
                return false
            }
        }
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML())
    },
})

// Sync content
watch(() => props.modelValue, (newValue) => {
    const isSame = editor.value?.getHTML() === newValue
    if (editor.value && !isSame) {
        editor.value.commands.setContent(newValue, false)
    }
})

// Media Handlers
function handleMediaSelect(media) {
    const url = media?.url || media?.path || media?.file_url
    if (url) {
        editor.value.chain().focus().setImage({ 
            src: url, 
            alt: media?.alt || media?.name || '' 
        }).run()
    }
    showMediaPicker.value = false
}

function openProperties() {
    let type = 'image'
    if (editor.value.isActive('video')) type = 'video'
    else if (editor.value.isActive('htmlEmbed')) type = 'htmlEmbed'

    selectedNodeForProperties.value = {
        type,
        attrs: editor.value.getAttributes(type)
    }
    
    // Get DOM element for anchoring
    const { selection } = editor.value.state
    const dom = editor.value.view.nodeDOM(selection.from)
    propertiesAnchor.value = dom instanceof HTMLElement ? dom : null
    
    showPropertiesModal.value = true
}

function saveMediaProperties(properties) {
    const type = selectedNodeForProperties.value.type
    editor.value.chain().focus().updateAttributes(type, properties).run()
}

// Table Handlers
function insertTable(config) {
    editor.value.chain().focus().insertTable({ 
        rows: config.rows, 
        cols: config.cols, 
        withHeaderRow: true 
    }).run()
}

// HTML View Handlers
function toggleHtmlView() {
    if (!showHtmlView.value) {
        htmlContent.value = editor.value.getHTML()
    } else {
        applyHtmlChanges()
    }
    showHtmlView.value = !showHtmlView.value
}

function applyHtmlChanges() {
    if (editor.value && htmlContent.value !== editor.value.getHTML()) {
        editor.value.commands.setContent(htmlContent.value, false)
        emit('update:modelValue', htmlContent.value)
    }
}

function insertHtmlEmbed() {
    editor.value.chain().focus().insertHtmlEmbed('').run()
}

// Context Menu Logic
const handleContextMenu = (e) => {
    if (!editor.value) return 
    
    // Allow default browser context menu inside the text area in HTML view
    if (showHtmlView.value) return

    e.preventDefault()

    const items = []

    // Contextual actions based on selection
    if (editor.value.isActive('image') || editor.value.isActive('video')) {
        items.push({ label: 'Properties', icon: SettingsIcon, action: 'properties' })
        items.push({ label: 'Delete Media', icon: Trash2, action: 'delete' })
    } else if (editor.value.isActive('table')) {
        items.push({ label: 'Delete Table', icon: Trash2, action: 'deleteTable' })
    } else {
         // General actions
         items.push({ label: 'Bold', icon: Bold, action: 'bold' })
         items.push({ label: 'Italic', icon: Italic, action: 'italic' })
         items.push({ label: 'Clean Formatting', icon: RemoveFormatting, action: 'clearFormatting' })
         items.push({ type: 'separator' })
         items.push({ label: 'Undo', icon: Undo, action: 'undo' })
         items.push({ label: 'Redo', icon: Redo, action: 'redo' })
    }

    if (items.length === 0) return

    contextMenu.position = { x: e.clientX, y: e.clientY }
    contextMenu.items = items
    contextMenu.show = true
}

const handleContextMenuAction = (action) => {
    contextMenu.show = false
    
    switch (action) {
        case 'properties':
            openProperties()
            break
        case 'delete':
            // Robust delete: find which one is active or just delete selection if it's a node
            const isImage = editor.value.isActive('image')
            const isVideo = editor.value.isActive('video')
            const nodeType = isImage ? 'image' : (isVideo ? 'video' : null)
            
            if (nodeType) {
                editor.value.chain().focus().deleteNode(nodeType).run()
            } else {
                // Fallback: delete selection
                editor.value.chain().focus().deleteSelection().run()
            }
            break
        case 'deleteTable':
            editor.value.chain().focus().deleteTable().run()
            break
        case 'clearFormatting':
            editor.value.chain().focus().unsetAllMarks().clearNodes().run()
            break
        case 'bold':
            editor.value.chain().focus().toggleBold().run()
            break
        case 'italic':
            editor.value.chain().focus().toggleItalic().run()
            break
        case 'undo':
            editor.value.chain().focus().undo().run()
            break
        case 'redo':
            editor.value.chain().focus().redo().run()
            break
    }
}

onBeforeUnmount(() => {
    editor.value?.destroy()
})
</script>

<style>
/* Custom prose overrides for Tiptap */
.ProseMirror p.is-editor-empty:first-child::before {
  color: hsl(var(--muted-foreground));
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.ProseMirror {
    color: hsl(var(--card-foreground));
    background-color: hsl(var(--card));
}

/* Heading Styles */
.ProseMirror h1 { font-size: 2.25rem; font-weight: 700; line-height: 1.2; margin-top: 1.5rem; margin-bottom: 0.75rem; }
.ProseMirror h2 { font-size: 1.875rem; font-weight: 700; line-height: 1.25; margin-top: 1.25rem; margin-bottom: 0.5rem; }
.ProseMirror h3 { font-size: 1.5rem; font-weight: 600; line-height: 1.3; margin-top: 1rem; margin-bottom: 0.5rem; }

/* Inline Code */
.ProseMirror code {
    background-color: hsl(var(--muted));
    border-radius: 0.25rem;
    padding: 0.125rem 0.375rem;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.875em;
    color: hsl(var(--primary));
    border: 1px solid hsl(var(--border));
}

/* Code Block */
.ProseMirror pre {
    background-color: hsl(220 15% 12%);
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1rem 0;
    overflow-x: auto;
    border: 1px solid hsl(var(--border));
}

.ProseMirror pre code {
    background: none;
    border: none;
    padding: 0;
    color: hsl(210 14% 83%);
    font-size: 0.875rem;
}

/* Blockquote */
.ProseMirror blockquote {
    border-left: 3px solid hsl(var(--primary));
    margin-left: 0;
    padding-left: 1rem;
    color: hsl(var(--muted-foreground));
    font-style: italic;
}

/* Table Styles */
.ProseMirror table {
    border-collapse: collapse;
    table-layout: fixed;
    width: 100%;
    margin: 1rem 0;
    border: 2px solid hsl(var(--border));
}

.ProseMirror th, .ProseMirror td {
    border: 1px solid hsl(var(--border));
    padding: 0.75rem 1rem;
    min-width: 100px;
}

.ProseMirror th {
    background-color: hsl(var(--muted));
    font-weight: 600;
}

/* Gapcursor */
.ProseMirror .ProseMirror-gapcursor {
    height: 1px;
    background: hsl(var(--primary));
}

/* Node Selection */
.ProseMirror-selectednode {
    outline: 2px solid hsl(var(--primary));
}

/* Dropcap Styles */
.ProseMirror p.has-dropcap::first-letter {
    float: left;
    font-size: 4rem;
    line-height: 1;
    margin-right: 0.15em;
    margin-bottom: -0.1em;
    font-weight: 800;
    color: hsl(var(--primary));
    font-family: serif; /* Classic look */
    text-transform: uppercase;
}

/* Newspaper Style Columns */
.ProseMirror div[data-type="text-columns"] {
    width: 100%;
    margin: 2rem 0;
    column-fill: balance;
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
    -webkit-hyphens: auto;
}

.ProseMirror div[data-type="text-columns"] > * {
    margin-top: 0;
}

.ProseMirror div[data-type="text-columns"]:focus-within {
    outline: 1px dashed hsl(var(--primary) / 30%);
    outline-offset: 10px;
}

/* Old Grid Columns Styles (Legacy) */
.ProseMirror div[data-type="columns"] {
    display: flex;
    gap: 2rem;
    margin: 2rem 0;
    width: 100%;
}

.ProseMirror div[data-type="column"] {
    flex: 1;
    min-width: 0;
    border: 1px dashed hsl(var(--border) / 50%);
    padding: 1rem;
    border-radius: 0.5rem;
    position: relative;
}
</style>
制作
