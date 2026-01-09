<template>
    <bubble-menu 
        v-if="editor" 
        :editor="editor" 
        :tippy-options="{ duration: 100 }" 
        :should-show="shouldShow"
        class="flex items-center gap-1 p-1 bg-popover border border-border rounded-md shadow-md"
    >
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
</template>

<script setup>
import { BubbleMenu } from '@tiptap/vue-3/menus'
import Button from '@/components/ui/button.vue'
import { 
    Bold, 
    Italic, 
    Underline as UnderlineIcon,
    Strikethrough
} from 'lucide-vue-next'

const props = defineProps({
    editor: Object
})

const shouldShow = ({ editor }) => {
    // Don't show if image or video is selected (we will have a different bubble for that)
    if (editor.isActive('image') || editor.isActive('video') || editor.isActive('htmlEmbed') || editor.isActive('icon')) {
        return false
    }
    return editor.isEditable && !editor.state.selection.empty
}
</script>
