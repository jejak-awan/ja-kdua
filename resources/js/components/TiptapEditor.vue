<template>
    <Card class="overflow-hidden">
        <!-- Toolbar -->
        <div v-if="editor" class="editor-toolbar flex flex-wrap items-center gap-1 p-2 border-b border-border bg-muted/40">
            <!-- Text Style -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <Select :model-value="getHeaderLevel()" @update:model-value="setHeaderLevel">
                    <SelectTrigger class="w-[130px] h-8 border-none bg-transparent hover:bg-muted shadow-none">
                        <SelectValue placeholder="Paragraph" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[250px] overflow-y-auto">
                        <SelectItem value="p">Paragraph</SelectItem>
                        <SelectItem value="1">Heading 1</SelectItem>
                        <SelectItem value="2">Heading 2</SelectItem>
                        <SelectItem value="3">Heading 3</SelectItem>
                        <SelectItem value="4">Heading 4</SelectItem>
                        <SelectItem value="5">Heading 5</SelectItem>
                        <SelectItem value="6">Heading 6</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Font Family -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                 <Select :model-value="getFontFamily()" @update:model-value="setFontFamily">
                    <SelectTrigger class="w-[110px] h-8 border-none bg-transparent hover:bg-muted shadow-none">
                        <SelectValue placeholder="Font" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[250px]">
                        <SelectItem value="Inter">Inter</SelectItem>
                        <SelectItem value="Comic Sans MS, Comic Sans">Comic Sans</SelectItem>
                        <SelectItem value="serif">Serif</SelectItem>
                        <SelectItem value="monospace">Monospace</SelectItem>
                        <SelectItem value="cursive">Cursive</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Formatting Group -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-muted': editor.isActive('bold') }" title="Bold">
                    <Bold class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-muted': editor.isActive('italic') }" title="Italic">
                    <Italic class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-muted': editor.isActive('underline') }" title="Underline">
                    <UnderlineIcon class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-muted': editor.isActive('strike') }" title="Strikethrough">
                    <Strikethrough class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleCode().run()" :class="{ 'bg-muted': editor.isActive('code') }" title="Inline Code">
                    <Code class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleCodeBlock().run()" :class="{ 'bg-muted': editor.isActive('codeBlock') }" title="Code Block">
                    <Code2 class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().unsetAllMarks().run()" title="Clear Formatting">
                    <RemoveFormatting class="w-4 h-4" />
                </Button>
            </div>

            <!-- Colors -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <!-- Text Color -->
                <div class="relative">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="showTextColorPicker = !showTextColorPicker" title="Text Color">
                        <Palette class="w-4 h-4" />
                    </Button>
                    <div v-if="showTextColorPicker" class="absolute top-full left-0 mt-1 p-2 bg-popover border border-border rounded-md shadow-lg z-50 grid grid-cols-5 gap-1">
                        <button v-for="color in colorOptions" :key="'text-'+color" 
                            class="w-6 h-6 rounded border border-border/50 cursor-pointer hover:scale-110 transition-transform"
                            :style="{ backgroundColor: color }"
                            @click="setTextColor(color)"
                            :title="color"
                        />
                        <button class="w-6 h-6 rounded border border-border flex items-center justify-center cursor-pointer hover:bg-muted" @click="setTextColor(null)" title="Remove color">
                            <X class="w-3 h-3" />
                        </button>
                    </div>
                </div>
                <!-- Highlight Color -->
                <div class="relative">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="showHighlightPicker = !showHighlightPicker" title="Highlight">
                        <Highlighter class="w-4 h-4" />
                    </Button>
                    <div v-if="showHighlightPicker" class="absolute top-full left-0 mt-1 p-2 bg-popover border border-border rounded-md shadow-lg z-50 grid grid-cols-5 gap-1">
                        <button v-for="color in highlightOptions" :key="'hl-'+color" 
                            class="w-6 h-6 rounded border border-border/50 cursor-pointer hover:scale-110 transition-transform"
                            :style="{ backgroundColor: color }"
                            @click="setHighlight(color)"
                            :title="color"
                        />
                        <button class="w-6 h-6 rounded border border-border flex items-center justify-center cursor-pointer hover:bg-muted" @click="setHighlight(null)" title="Remove highlight">
                            <X class="w-3 h-3" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alignment -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                 <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'left' }) }" title="Align Left">
                    <AlignLeft class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'center' }) }" title="Align Center">
                    <AlignCenter class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'right' }) }" title="Align Right">
                    <AlignRight class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('justify').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'justify' }) }" title="Justify">
                    <AlignJustify class="w-4 h-4" />
                </Button>
            </div>

            <!-- Lists & Quote -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-muted': editor.isActive('bulletList') }" title="Bullet List">
                    <List class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'bg-muted': editor.isActive('orderedList') }" title="Numbered List">
                    <ListOrdered class="w-4 h-4" />
                </Button>
                 <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'bg-muted': editor.isActive('blockquote') }" title="Quote">
                    <Quote class="w-4 h-4" />
                </Button>
            </div>

            <!-- Table -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="showTableDialog = true" title="Insert Table">
                    <TableIcon class="w-4 h-4" />
                </Button>
                <template v-if="editor.isActive('table')">
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().addColumnAfter().run()" title="Add Column Right">
                        <Columns class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().addRowAfter().run()" title="Add Row Below">
                        <RowsIcon class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().mergeCells().run()" title="Merge Cells">
                        <Merge class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().splitCell().run()" title="Split Cell">
                        <Split class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive" @click="editor.chain().focus().deleteColumn().run()" title="Delete Column">
                        <Columns class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive" @click="editor.chain().focus().deleteRow().run()" title="Delete Row">
                        <RowsIcon class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive" @click="editor.chain().focus().deleteTable().run()" title="Delete Table">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </template>
            </div>

            <!-- Extras -->
             <div class="flex items-center gap-0.5">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="showMediaPicker = true" title="Insert Image">
                    <ImageIcon class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="insertHtmlEmbed" title="Embed HTML (Maps, Videos, Widgets)">
                    <FileCode2 class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setHorizontalRule().run()" title="Horizontal Line">
                    <Minus class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()" title="Undo">
                    <Undo class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()" title="Redo">
                    <Redo class="w-4 h-4" />
                </Button>
                <div class="border-l border-border/50 ml-1 pl-1">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8" 
                        :class="{ 'bg-muted': showHtmlView }"
                        @click="toggleHtmlView" 
                        title="View/Edit HTML"
                    >
                        <FileCode class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

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
        
        <!-- Bubble Menu -->
        <bubble-menu v-if="editor" :editor="editor" :tippy-options="{ duration: 100 }" class="flex items-center gap-1 p-1 bg-popover border border-border rounded-md shadow-md">
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-muted': editor.isActive('bold') }">
                <Bold class="w-3 h-3" />
            </Button>
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-muted': editor.isActive('italic') }">
                <Italic class="w-3 h-3" />
            </Button>
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-muted': editor.isActive('underline') }">
                <UnderlineIcon class="w-3 h-3" />
            </Button>
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-muted': editor.isActive('strike') }">
                <Strikethrough class="w-3 h-3" />
            </Button>
        </bubble-menu>

        <!-- Media Picker Modal -->
        <MediaPicker
            v-model:open="showMediaPicker"
            @selected="handleMediaSelect"
        />

        <!-- Table Insert Dialog -->
        <div v-if="showTableDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showTableDialog = false">
            <div class="bg-popover border border-border rounded-lg shadow-xl p-6 w-[300px] space-y-4">
                <h3 class="text-lg font-semibold text-foreground">Insert Table</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-muted-foreground">Rows</label>
                        <input 
                            v-model.number="tableConfig.rows" 
                            type="number" 
                            min="1" 
                            max="20"
                            class="w-20 px-2 py-1 text-sm border border-border rounded-md bg-background text-foreground"
                        />
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-muted-foreground">Columns</label>
                        <input 
                            v-model.number="tableConfig.cols" 
                            type="number" 
                            min="1" 
                            max="10"
                            class="w-20 px-2 py-1 text-sm border border-border rounded-md bg-background text-foreground"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <Button variant="outline" size="sm" @click="showTableDialog = false">Cancel</Button>
                    <Button size="sm" @click="insertTable">Insert</Button>
                </div>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import Card from '@/components/ui/card.vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import { BubbleMenu } from '@tiptap/vue-3/menus'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import ImageExtension from '@tiptap/extension-image'
import LinkExtension from '@tiptap/extension-link'
import BubbleMenuExtension from '@tiptap/extension-bubble-menu'
import Typography from '@tiptap/extension-typography'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import { FontFamily } from '@tiptap/extension-font-family'
import { Color } from '@tiptap/extension-color'
import Highlight from '@tiptap/extension-highlight'
import Underline from '@tiptap/extension-underline'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'
import { CodeBlockWithCopyExtension } from './editor/CodeBlockExtension'
import { HtmlEmbed } from './editor/HtmlEmbedExtension'
import { createLowlight } from 'lowlight'
// Import only common languages to reduce bundle size
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

import Button from '@/components/ui/button.vue'
import Select from '@/components/ui/select.vue'
import SelectTrigger from '@/components/ui/select-trigger.vue'
import SelectValue from '@/components/ui/select-value.vue'
import SelectContent from '@/components/ui/select-content.vue'
import SelectItem from '@/components/ui/select-item.vue'
import MediaPicker from './MediaPicker.vue'
import { 
    Bold, 
    Italic, 
    Underline as UnderlineIcon,
    Strikethrough, 
    List, 
    ListOrdered, 
    Quote, 
    Minus, 
    Undo, 
    Redo,
    Image as ImageIcon,
    Code,
    Code2,
    RemoveFormatting,
    AlignLeft,
    AlignCenter,
    AlignRight,
    AlignJustify,
    Table as TableIcon,
    Plus,
    Rows as RowsIcon,
    Columns,
    Trash2,
    Copy,
    Check,
    Palette,
    Highlighter,
    X,
    Merge,
    Split,
    FileCode,
    FileCode2,
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
const tableConfig = ref({ rows: 3, cols: 3 })
const showTextColorPicker = ref(false)
const showHighlightPicker = ref(false)
const showHtmlView = ref(false)
const htmlContent = ref('')

// Color palettes
const colorOptions = [
    '#000000', '#374151', '#6B7280', '#9CA3AF', '#D1D5DB',
    '#EF4444', '#F97316', '#EAB308', '#22C55E', '#14B8A6',
    '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#F43F5E',
]

const highlightOptions = [
    '#FEF9C3', '#FEF08A', '#FDE047', '#FACC15', '#EAB308',
    '#D9F99D', '#BEF264', '#A3E635', '#84CC16', '#65A30D',
    '#CCFBF1', '#99F6E4', '#5EEAD4', '#2DD4BF', '#14B8A6',
    '#E0E7FF', '#C7D2FE', '#A5B4FC', '#818CF8', '#6366F1',
    '#FCE7F3', '#FBCFE8', '#F9A8D4', '#F472B6', '#EC4899',
]

// Create lowlight instance with common languages
const lowlight = createLowlight()
lowlight.register('javascript', javascript)
lowlight.register('js', javascript)
lowlight.register('typescript', typescript)
lowlight.register('ts', typescript)
lowlight.register('html', html)
lowlight.register('xml', html)
lowlight.register('css', css)
lowlight.register('php', php)
lowlight.register('python', python)
lowlight.register('py', python)
lowlight.register('json', json)
lowlight.register('bash', bash)
lowlight.register('sh', bash)
lowlight.register('sql', sql)
lowlight.register('markdown', markdown)
lowlight.register('md', markdown)

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            codeBlock: false, // Use custom CodeBlockWithCopyExtension instead
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        ImageExtension,
        LinkExtension,
        Typography,
        BubbleMenuExtension,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        TextStyle,
        FontFamily,
        Color,
        Highlight.configure({
            multicolor: true,
        }),
        Underline,
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
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML())
    },
})

// Sync content when modelValue changes from outside
watch(() => props.modelValue, (newValue) => {
    const isSame = editor.value?.getHTML() === newValue
    if (editor.value && !isSame) {
        editor.value.commands.setContent(newValue, false)
    }
})

function handleMediaSelect(media) {
    console.log('Media selected:', media) // Debug
    const url = media?.url || media?.path || media?.file_url
    if (url) {
        editor.value.chain().focus().setImage({ 
            src: url, 
            alt: media?.alt || media?.name || '' 
        }).run()
    }
    showMediaPicker.value = false
}

function insertTable() {
    editor.value.chain().focus().insertTable({ 
        rows: tableConfig.value.rows, 
        cols: tableConfig.value.cols, 
        withHeaderRow: true 
    }).run()
    showTableDialog.value = false
    tableConfig.value = { rows: 3, cols: 3 } // Reset
}

function setTextColor(color) {
    if (color) {
        editor.value.chain().focus().setColor(color).run()
    } else {
        editor.value.chain().focus().unsetColor().run()
    }
    showTextColorPicker.value = false
}

function setHighlight(color) {
    if (color) {
        editor.value.chain().focus().setHighlight({ color }).run()
    } else {
        editor.value.chain().focus().unsetHighlight().run()
    }
    showHighlightPicker.value = false
}

function toggleHtmlView() {
    if (!showHtmlView.value) {
        // Switching to HTML view - get current HTML
        htmlContent.value = editor.value.getHTML()
    } else {
        // Switching back to WYSIWYG - apply changes
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

function getHeaderLevel() {
    if (editor.value?.isActive('heading', { level: 1 })) return '1'
    if (editor.value?.isActive('heading', { level: 2 })) return '2'
    if (editor.value?.isActive('heading', { level: 3 })) return '3'
    if (editor.value?.isActive('heading', { level: 4 })) return '4'
    if (editor.value?.isActive('heading', { level: 5 })) return '5'
    if (editor.value?.isActive('heading', { level: 6 })) return '6'
    return 'p'
}

function setHeaderLevel(val) {
    if (val === 'p') {
         editor.value.chain().focus().setParagraph().run()
    } else {
         editor.value.chain().focus().toggleHeading({ level: parseInt(val) }).run()
    }
}

function getFontFamily() {
    return editor.value?.getAttributes('textStyle').fontFamily || 'Inter'
}

function setFontFamily(val) {
    editor.value.chain().focus().setFontFamily(val).run()
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

/* Ensure text visibility */
.ProseMirror {
    color: hsl(var(--card-foreground));
    background-color: hsl(var(--card));
}

/* Heading Styles */
.ProseMirror h1 {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.ProseMirror h2 {
    font-size: 1.875rem;
    font-weight: 700;
    line-height: 1.25;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.ProseMirror h3 {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.3;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.ProseMirror h4 {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
    margin-top: 0.75rem;
    margin-bottom: 0.5rem;
}

.ProseMirror h5 {
    font-size: 1.125rem;
    font-weight: 500;
    line-height: 1.4;
    margin-top: 0.5rem;
    margin-bottom: 0.25rem;
}

.ProseMirror h6 {
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.4;
    margin-top: 0.5rem;
    margin-bottom: 0.25rem;
    color: hsl(var(--muted-foreground));
}

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

/* Code Block Container - styles handled by CodeBlockWithCopy component */
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
    line-height: 1.6;
}

/* Syntax highlighting colors */
.ProseMirror pre .hljs-comment,
.ProseMirror pre .hljs-quote { color: #6a9955; }
.ProseMirror pre .hljs-keyword,
.ProseMirror pre .hljs-selector-tag,
.ProseMirror pre .hljs-built_in { color: #569cd6; }
.ProseMirror pre .hljs-string,
.ProseMirror pre .hljs-title,
.ProseMirror pre .hljs-attr { color: #ce9178; }
.ProseMirror pre .hljs-number,
.ProseMirror pre .hljs-literal { color: #b5cea8; }
.ProseMirror pre .hljs-variable,
.ProseMirror pre .hljs-template-variable { color: #9cdcfe; }
.ProseMirror pre .hljs-type,
.ProseMirror pre .hljs-class .hljs-title { color: #4ec9b0; }
.ProseMirror pre .hljs-function { color: #dcdcaa; }
.ProseMirror pre .hljs-meta { color: #c586c0; }

/* Blockquote */
.ProseMirror blockquote {
    border-left: 3px solid hsl(var(--primary));
    margin-left: 0;
    padding-left: 1rem;
    color: hsl(var(--muted-foreground));
    font-style: italic;
}

/* Lists */
.ProseMirror ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

.ProseMirror ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

.ProseMirror li {
    margin: 0.25rem 0;
}

.ProseMirror li p {
    margin: 0;
}

/* Nested lists */
.ProseMirror ul ul,
.ProseMirror ol ol,
.ProseMirror ul ol,
.ProseMirror ol ul {
    margin: 0.25rem 0;
}

/* Horizontal Rule */
.ProseMirror hr {
    border: none;
    border-top: 1px solid hsl(var(--border));
    margin: 1.5rem 0;
}

/* Table Styles */
.ProseMirror table {
    border-collapse: collapse;
    table-layout: fixed;
    width: 100%;
    margin: 1rem 0;
    overflow: hidden;
    border-radius: 0.5rem;
    border: 2px solid hsl(var(--border));
}

.ProseMirror th,
.ProseMirror td {
    border: 1px solid hsl(var(--border));
    padding: 0.75rem 1rem;
    vertical-align: top;
    position: relative;
    min-width: 100px;
}

/* Table Header - Title Row */
.ProseMirror th {
    background-color: hsl(var(--muted));
    font-weight: 600;
    text-align: left;
    border-bottom: 2px solid hsl(var(--primary) / 0.3);
    color: hsl(var(--foreground));
}

/* Darker header for dark mode */
:root.dark .ProseMirror th,
.dark .ProseMirror th {
    background-color: hsl(var(--muted));
}

.ProseMirror td {
    background-color: hsl(var(--card));
}

/* Alternating row colors */
.ProseMirror tr:nth-child(even) td {
    background-color: hsl(var(--muted) / 0.3);
}

/* Selected cell */
.ProseMirror .selectedCell {
    background-color: hsl(var(--primary) / 0.2) !important;
    border: 2px solid hsl(var(--primary));
}

/* Resize handle */
.ProseMirror .column-resize-handle {
    position: absolute;
    right: -2px;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: hsl(var(--primary));
    cursor: col-resize;
}

.ProseMirror.resize-cursor {
    cursor: col-resize;
}

/* Image */
.ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

.ProseMirror img.ProseMirror-selectednode {
    outline: 2px solid hsl(var(--primary));
}
</style>
