<template>
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
            <Button
                size="icon"
                variant="ghost"
                class="w-8 h-8"
                :class="{ 'bg-muted': editor.isActive('paragraph', { dropcap: true }) }"
                title="Dropcap"
                @click="editor.chain().focus().toggleDropcap().run()"
            >
                <DropcapIcon class="w-4 h-4" />
            </Button>

            <div class="w-px h-8 mx-1 bg-border" />

            <!-- Lists -->
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

        <!-- Columns -->
        <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
            <!-- Grid Style (Old/Container based) -->
            <div class="flex items-center gap-0.5 bg-background/20 rounded p-0.5 mr-1">
                <Button variant="ghost" size="icon" class="h-7 w-7" @click="editor.chain().focus().insertColumns({ count: 2 }).run()" title="Insert 2 Grid Columns (Containers)">
                    <LayoutGrid class="w-3.5 h-3.5 opacity-70" />
                </Button>
                <Button variant="ghost" size="icon" class="h-7 w-7" @click="editor.chain().focus().insertColumns({ count: 3 }).run()" title="Insert 3 Grid Columns (Containers)">
                    <div class="grid grid-cols-3 gap-0.5">
                         <div class="w-1 h-3 bg-foreground/40 rounded-full" />
                         <div class="w-1 h-3 bg-foreground/40 rounded-full" />
                         <div class="w-1 h-3 bg-foreground/40 rounded-full" />
                    </div>
                </Button>
            </div>
            
            <div class="w-px h-4 bg-border/40 mx-1" />

            <!-- Newspaper Style (New/Flow based) -->
            <Button 
                variant="ghost" 
                size="icon" 
                class="h-8 w-8" 
                :class="{ 'bg-muted': editor.isActive('textColumns', { count: 2 }) }"
                @click="editor.chain().focus().toggleTextColumns(2).run()" 
                title="Newspaper Layout (2 Columns Flow)"
            >
                <Columns class="w-4 h-4" />
            </Button>
            <Button 
                variant="ghost" 
                size="icon" 
                class="h-8 w-8" 
                :class="{ 'bg-muted': editor.isActive('textColumns', { count: 3 }) }"
                @click="editor.chain().focus().toggleTextColumns(3).run()" 
                title="Newspaper Layout (3 Columns Flow)"
            >
                <div class="flex gap-0.5" :class="{ 'opacity-100': editor.isActive('textColumns', { count: 3 }), 'opacity-60': !editor.isActive('textColumns', { count: 3 }) }">
                    <div class="w-1 h-3 bg-primary rounded-full" />
                    <div class="w-1 h-3 bg-primary rounded-full" />
                    <div class="w-1 h-3 bg-primary rounded-full" />
                </div>
            </Button>
        </div>

        <!-- Table -->
        <div class="flex items-center gap-0.5 border-r pr-2 mr-2 border-border/50">
            <Button variant="ghost" size="icon" class="h-8 w-8" @click="$emit('insertTable')" title="Insert Table">
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
            <Button variant="ghost" size="icon" class="h-8 w-8" @click="$emit('openMedia')" title="Insert Image">
                <ImageIcon class="w-4 h-4" />
            </Button>
            <Button variant="ghost" size="icon" class="h-8 w-8" @click="$emit('insertHtml')" title="Embed HTML (Maps, Videos, Widgets)">
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
                    @click="$emit('toggleHtml')" 
                    title="View/Edit HTML"
                >
                    <FileCode class="w-4 h-4" />
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from '@/components/ui/button.vue'
import Select from '@/components/ui/select.vue'
import SelectTrigger from '@/components/ui/select-trigger.vue'
import SelectValue from '@/components/ui/select-value.vue'
import SelectContent from '@/components/ui/select-content.vue'
import SelectItem from '@/components/ui/select-item.vue'
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
    Rows as RowsIcon,
    Columns,
    Trash2,
    Check,
    Palette,
    Highlighter,
    X,
    Merge,
    Split,
    FileCode,
    FileCode2,
    Type as DropcapIcon,
    LayoutGrid,
} from 'lucide-vue-next'

const props = defineProps({
    editor: Object,
    showHtmlView: Boolean
})

defineEmits(['insertTable', 'openMedia', 'insertHtml', 'toggleHtml'])

const showTextColorPicker = ref(false)
const showHighlightPicker = ref(false)

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

function getHeaderLevel() {
    if (props.editor?.isActive('heading', { level: 1 })) return '1'
    if (props.editor?.isActive('heading', { level: 2 })) return '2'
    if (props.editor?.isActive('heading', { level: 3 })) return '3'
    if (props.editor?.isActive('heading', { level: 4 })) return '4'
    if (props.editor?.isActive('heading', { level: 5 })) return '5'
    if (props.editor?.isActive('heading', { level: 6 })) return '6'
    return 'p'
}

function setHeaderLevel(val) {
    if (val === 'p') {
         props.editor.chain().focus().setParagraph().run()
    } else {
         props.editor.chain().focus().toggleHeading({ level: parseInt(val) }).run()
    }
}

function getFontFamily() {
    return props.editor?.getAttributes('textStyle').fontFamily || 'Inter'
}

function setFontFamily(val) {
    props.editor.chain().focus().setFontFamily(val).run()
}

function setTextColor(color) {
    if (color) {
        props.editor.chain().focus().setColor(color).run()
    } else {
        props.editor.chain().focus().unsetColor().run()
    }
    showTextColorPicker.value = false
}

function setHighlight(color) {
    if (color) {
        props.editor.chain().focus().setHighlight({ color }).run()
    } else {
        props.editor.chain().focus().unsetHighlight().run()
    }
    showHighlightPicker.value = false
}
</script>
