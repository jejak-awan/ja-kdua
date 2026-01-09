<template>
    <node-view-wrapper 
        class="shape-node-wrapper relative inline-block max-w-full"
        :class="{ 'is-selected': selected }"
        :style="{ width: node.attrs.width, height: node.attrs.height }"
        @click="selectNode"
    >
        <div 
            class="shape-content w-full h-full p-4 overflow-hidden relative"
            :style="styleObject"
        >
            <node-view-content class="content-dom h-full" />
        </div>

        <!-- Resize Handles (Only when selected) -->
        <div v-if="selected" class="resize-handles">
            <!-- Corners -->
            <div class="resize-handle handle-tl" @mousedown.prevent.stop="startResize($event, 'tl')"></div>
            <div class="resize-handle handle-tr" @mousedown.prevent.stop="startResize($event, 'tr')"></div>
            <div class="resize-handle handle-bl" @mousedown.prevent.stop="startResize($event, 'bl')"></div>
            <div class="resize-handle handle-br" @mousedown.prevent.stop="startResize($event, 'br')"></div>
            
            <!-- Sides -->
            <div class="resize-handle handle-l" @mousedown.prevent.stop="startResize($event, 'l')"></div>
            <div class="resize-handle handle-r" @mousedown.prevent.stop="startResize($event, 'r')"></div>
            <div class="resize-handle handle-t" @mousedown.prevent.stop="startResize($event, 't')"></div>
            <div class="resize-handle handle-b" @mousedown.prevent.stop="startResize($event, 'b')"></div>
        </div>
    </node-view-wrapper>
</template>

<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'
import { NodeViewWrapper, NodeViewContent } from '@tiptap/vue-3'

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    updateAttributes: {
        type: Function,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
    getPos: {
        type: Function,
        required: true
    }
})

const selectNode = () => {
    // handled by Tiptap mostly, but can force if needed
}

const styleObject = computed(() => {
    const attrs = props.node.attrs
    return {
        backgroundColor: attrs.backgroundColor,
        borderColor: attrs.borderColor,
        borderWidth: attrs.borderWidth + (attrs.borderWidth.endsWith('px') ? '' : 'px'),
        borderStyle: attrs.borderWidth && attrs.borderWidth !== '0' ? 'solid' : 'none',
        borderRadius: attrs.borderRadius + (attrs.borderRadius.endsWith('px') ? '' : 'px'),
        boxShadow: attrs.boxShadow,
        color: attrs.color,
        textAlign: attrs.textAlign,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center'
    }
})

// Resize Logic (Adapted from HtmlEmbed)
const startResize = (e, handle) => {
    e.preventDefault()
    e.stopPropagation()

    const checkDimension = (val) => {
        if (!val) return 0;
        return parseInt(val.toString().replace('px', ''), 10) || 0;
    }

    const startX = e.clientX
    const startY = e.clientY
    const startWidth = checkDimension(props.node.attrs.width) || 300
    const startHeight = checkDimension(props.node.attrs.height) || 200
    
    const onResize = (e) => {
        const dx = e.clientX - startX
        const dy = e.clientY - startY
        
        let newWidth = startWidth
        let newHeight = startHeight

        if (handle.includes('r')) newWidth = startWidth + dx
        if (handle.includes('l')) newWidth = startWidth - dx
        if (handle.includes('b')) newHeight = startHeight + dy
        if (handle.includes('t')) newHeight = startHeight - dy

        // Minimum constraints
        newWidth = Math.max(50, newWidth)
        newHeight = Math.max(50, newHeight)

        props.updateAttributes({
            width: `${Math.round(newWidth)}px`,
            height: `${Math.round(newHeight)}px`
        })
    }

    const stopResize = () => {
        document.removeEventListener('mousemove', onResize)
        document.removeEventListener('mouseup', stopResize)
    }

    document.addEventListener('mousemove', onResize)
    document.addEventListener('mouseup', stopResize)
}

</script>

<style scoped>
.shape-node-wrapper {
    transition: outline 0.1s;
}

.shape-node-wrapper.is-selected {
    outline: 2px solid hsl(var(--primary));
    outline-offset: 2px;
    z-index: 10;
}

/* Resize Handles */
.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: hsl(var(--primary));
    border: 1px solid white;
    border-radius: 50%;
    z-index: 20;
}

/* Corner Positions */
.handle-tl { top: -5px; left: -5px; cursor: nwse-resize; }
.handle-tr { top: -5px; right: -5px; cursor: nesw-resize; }
.handle-bl { bottom: -5px; left: -5px; cursor: nesw-resize; }
.handle-br { bottom: -5px; right: -5px; cursor: nwse-resize; }

/* Side Positions */
.handle-t { top: -5px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.handle-b { bottom: -5px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.handle-l { left: -5px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }
.handle-r { right: -5px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }

.content-dom {
    outline: none;
}
</style>
