<template>
    <div class="border border-border rounded-md overflow-hidden bg-background">
        <!-- Toolbar -->
        <div v-if="editor" class="editor-toolbar flex flex-wrap items-center gap-1 p-2 border-b border-border bg-muted/30">
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
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-muted': editor.isActive('bold') }">
                    <Bold class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-muted': editor.isActive('italic') }">
                    <Italic class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-muted': editor.isActive('strike') }">
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

            <!-- Alignment -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                 <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'left' }) }">
                    <AlignLeft class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'center' }) }">
                    <AlignCenter class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'right' }) }">
                    <AlignRight class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setTextAlign('justify').run()" :class="{ 'bg-muted': editor.isActive({ textAlign: 'justify' }) }">
                    <AlignJustify class="w-4 h-4" />
                </Button>
            </div>

            <!-- Lists & Indent -->
            <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-muted': editor.isActive('bulletList') }">
                    <List class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleOrderedList().run()" :class="{ 'bg-muted': editor.isActive('orderedList') }">
                    <ListOrdered class="w-4 h-4" />
                </Button>
                 <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().toggleBlockquote().run()" :class="{ 'bg-muted': editor.isActive('blockquote') }">
                    <Quote class="w-4 h-4" />
                </Button>
            </div>

            <!-- Extras -->
             <div class="flex items-center gap-0.5">
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="addImage">
                    <ImageIcon class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().setHorizontalRule().run()">
                    <Minus class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().undo().run()" :disabled="!editor.can().undo()">
                    <Undo class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8" @click="editor.chain().focus().redo().run()" :disabled="!editor.can().redo()">
                    <Redo class="w-4 h-4" />
                </Button>
            </div>
        </div>

        <!-- Editor Content -->
        <editor-content :editor="editor" class="prose prose-sm sm:prose-base dark:prose-invert max-w-none focus:outline-none min-h-[400px] p-4 text-foreground bg-background" />
        
        <!-- Bubble Menu -->
        <bubble-menu v-if="editor" :editor="editor" :tippy-options="{ duration: 100 }" class="flex items-center gap-1 p-1 bg-popover border border-border rounded-md shadow-md">
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-muted': editor.isActive('bold') }">
                <Bold class="w-3 h-3" />
            </Button>
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-muted': editor.isActive('italic') }">
                <Italic class="w-3 h-3" />
            </Button>
            <Button variant="ghost" size="sm" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-muted': editor.isActive('strike') }">
                <Strikethrough class="w-3 h-3" />
            </Button>
        </bubble-menu>
    </div>
</template>

<script setup>
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
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import { all, createLowlight } from 'lowlight'
import Button from '@/components/ui/button.vue'
import { watch, onBeforeUnmount } from 'vue'
import { 
    Bold, 
    Italic, 
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
    AlignJustify
} from 'lucide-vue-next'
import Select from '@/components/ui/select.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';

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

const lowlight = createLowlight(all)

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            codeBlock: false,
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
        CodeBlockLowlight.configure({
            lowlight,
        }),
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

function addImage() {
    const url = window.prompt('URL')
    if (url) {
        editor.value.chain().focus().setImage({ src: url }).run()
    }
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
/* Ensure text visibility in light mode */
.ProseMirror {
    color: hsl(var(--foreground));
    background-color: hsl(var(--background));
}

</style>
