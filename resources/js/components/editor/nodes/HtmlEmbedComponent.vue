<template>
    <node-view-wrapper 
        class="html-embed-wrapper"
        :style="{
            justifyContent: justifyContent,
            textAlign: node.attrs.align,
        }"
        :class="{
            'float-left': node.attrs.displayMode === 'float-left',
            'float-right': node.attrs.displayMode === 'float-right',
            'w-full': node.attrs.displayMode === 'block',
            'inline-block': node.attrs.displayMode === 'inline'
        }"
    >
        <div 
            class="html-embed-container"
            :class="{ 
                'is-selected': selected,
                'is-empty': !sanitizedHtml
            }"
            :style="containerStyles"
            @dblclick="onDblClick"
        >
            <!-- Resize Handles (Only when selected) -->
            <template v-if="selected">
                <div class="resize-handle top-left" @mousedown.stop="startResize($event, 'tl')"></div>
                <div class="resize-handle top-right" @mousedown.stop="startResize($event, 'tr')"></div>
                <div class="resize-handle bottom-left" @mousedown.stop="startResize($event, 'bl')"></div>
                <div class="resize-handle bottom-right" @mousedown.stop="startResize($event, 'br')"></div>
                <div class="resize-handle left" @mousedown.stop="startResize($event, 'l')"></div>
                <div class="resize-handle right" @mousedown.stop="startResize($event, 'r')"></div>
            </template>

            <!-- Interact Overlay: Captures clicks when not in interactive mode -->
            <div 
                v-if="!isInteractive && sanitizedHtml" 
                class="absolute inset-0 z-10 bg-transparent cursor-pointer"
                @click.stop="selectNode"
            ></div>

            <!-- Interact Toggle Button (Visible when selected) -->
            <div v-if="selected || isInteractive" class="absolute top-2 right-2 z-50 flex gap-1 bg-background/80 backdrop-blur-sm p-1 rounded-md shadow-sm border border-border/50">
                 <button 
                    @click.stop="fitToContent"
                    class="p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                    title="Fit to Content (Reset Size)"
                 >
                    <Maximize2 class="w-3.5 h-3.5" />
                 </button>
                 <div class="w-px bg-border/50 mx-0.5"></div>
                 <button 
                    v-if="!isInteractive"
                    @click.stop="startInteraction"
                    class="p-1.5 rounded-sm hover:bg-primary hover:text-primary-foreground text-muted-foreground transition-colors"
                    title="Interact with content"
                 >
                    <MousePointerClick class="w-3.5 h-3.5" />
                 </button>
                 <button 
                    v-else
                    @click.stop="stopInteraction"
                    class="p-1.5 rounded-sm hover:bg-destructive hover:text-destructive-foreground text-foreground transition-colors"
                    title="Stop interaction"
                 >
                    <X class="w-3.5 h-3.5" />
                 </button>
            </div>

            <div 
                v-if="sanitizedHtml"
                ref="contentRef"
                class="html-embed-content"
                :class="{ 'is-interactive': isInteractive }"
                v-html="sanitizedHtml"
            ></div>
            <div v-else class="html-embed-empty">
                <Code class="w-8 h-8 text-muted-foreground mb-2" />
                <p class="text-sm text-muted-foreground">Empty HTML Embed</p>
                <p class="text-xs text-muted-foreground/70">Double click to configure</p>
            </div>
        </div>
    </node-view-wrapper>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { NodeViewWrapper } from '@tiptap/vue-3'
import { Code, MousePointerClick, X, Maximize2 } from 'lucide-vue-next'
import DOMPurify from 'dompurify'

const props = defineProps({
// ... (props unchanged)
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
    editor: {
        type: Object,
        required: true
    }
})

const isInteractive = ref(false)

// Reset interaction when deselected
watch(() => props.selected, (isSelected) => {
    if (!isSelected) {
        isInteractive.value = false
    }
})

const startInteraction = () => {
    isInteractive.value = true
}

const stopInteraction = () => {
    isInteractive.value = false
}

// Select node manually if overlay clicked (redundant but safe)
const selectNode = () => {
    // Tiptap handles selection via DOM event, but overlay ensures it catches it
}

const fitToContent = () => {
    let html = props.node.attrs.html || ''
    // Try to extract original dimensions from HTML
    const widthMatch = html.match(/(?:width|WIDTH)=["']?(\d+)(?:px)?["']?/)
    const heightMatch = html.match(/(?:height|HEIGHT)=["']?(\d+)(?:px)?["']?/)
    
    if (widthMatch && widthMatch[1] && heightMatch && heightMatch[1]) {
        props.updateAttributes({
            width: `${widthMatch[1]}px`,
            height: `${heightMatch[1]}px`
        })
    } else {
        // Fallback or if no dimensions found, maybe reset to defaults?
        // Let's just reset to empty to allow auto-flow if nothing found
         props.updateAttributes({
            width: undefined, // undefined will remove attr? or use null
            height: undefined
        })
    }
}

const contentRef = ref(null)

const sanitizedHtml = computed(() => {
    let html = props.node.attrs.html || ''
    
    // Configure DOMPurify to allow iframe/embeds
    const config = {
        ADD_TAGS: ['iframe', 'embed'],
        ADD_ATTR: [
            'allow', 'allowfullscreen', 'frameborder', 'scrolling', 
            'target', 'width', 'height', 'src', 'type', 'style', 'class'
        ],
        // Safety: forbid scripting
        FORBID_TAGS: ['script', 'style'], // style tag debatable, but safer to forbid global style injection. Note: 'style' attr is allowed above.
        FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover'] // Event handlers
    }

    return DOMPurify.sanitize(html, config)
})

const justifyContent = computed(() => {
    if (props.node.attrs.displayMode === 'block') {
        switch (props.node.attrs.align) {
            case 'left': return 'flex-start'
            case 'right': return 'flex-end'
            case 'center': default: return 'center'
        }
    }
    return undefined
})

// Helper to ensure pixel units
const toPx = (val) => {
    if (!val) return undefined
    const str = String(val)
    return str.endsWith('px') ? str : `${str}px`
}

const containerStyles = computed(() => ({
    width: props.node.attrs.width,
    height: props.node.attrs.height,
    borderRadius: toPx(props.node.attrs.borderRadius),
    borderWidth: toPx(props.node.attrs.borderWidth),
    borderColor: props.node.attrs.borderColor,
    margin: toPx(props.node.attrs.margin),
    marginRight: props.node.attrs.displayMode === 'float-left' ? (props.node.attrs.margin ? toPx(props.node.attrs.margin) : '16px') : undefined,
    marginLeft: props.node.attrs.displayMode === 'float-right' ? (props.node.attrs.margin ? toPx(props.node.attrs.margin) : '16px') : undefined,
}))

// Resize Logic
let resizing = false
let startX = 0
let startY = 0
let startWidth = 0
let startHeight = 0
let aspectRatio = 1 // Store aspect ratio
let activeHandle = ''

const startResize = (e, handle) => {
    if (isInteractive.value) return // Disable resize in interactive mode
    e.preventDefault() // Prevent text selection
    resizing = true
    activeHandle = handle
    startX = e.clientX
    startY = e.clientY
    
    // Get current dimensions
    const container = e.target.parentElement
    const rect = container.getBoundingClientRect()
    startWidth = rect.width
    startHeight = rect.height
    aspectRatio = startWidth / startHeight

    document.addEventListener('mousemove', onResize)
    document.addEventListener('mouseup', stopResize)
}

const onResize = (e) => {
    if (!resizing) return

    const dx = e.clientX - startX
    const dy = e.clientY - startY
    
    let newWidth = startWidth
    let newHeight = startHeight

    // Corner Handles (Preserve Aspect Ratio)
    if (['tl', 'tr', 'bl', 'br'].includes(activeHandle)) {
         // Logic to determine dominant axis or just use 'width' driven resize for simplicity?
         // Let's use width driven for left/right movement
         if (activeHandle.includes('r')) {
             newWidth = startWidth + dx
         } else {
             newWidth = startWidth - dx
         }
         
         // Enforce aspect ratio
         newHeight = newWidth / aspectRatio
    } 
    // Side Handles (Free Resize)
    else {
        if (activeHandle.includes('l')) newWidth = startWidth - dx
        if (activeHandle.includes('r')) newWidth = startWidth + dx
        if (activeHandle.includes('t')) newHeight = startHeight - dy
        if (activeHandle.includes('b')) newHeight = startHeight + dy
    }

    // Minimum constraints
    if (newWidth < 50) newWidth = 50
    if (newHeight < 50) newHeight = 50

    // Update attributes (pixels for now during resize)
    // We update eagerly for smooth feedback, might consider throttling if performance is issue
    props.updateAttributes({
        width: `${Math.round(newWidth)}px`,
        height: `${Math.round(newHeight)}px`
    })
}

const stopResize = () => {
    resizing = false
    activeHandle = ''
    document.removeEventListener('mousemove', onResize)
    document.removeEventListener('mouseup', stopResize)
}

// Double Click Handler - fallback if global handler misses (though global one handles it mostly)
const onDblClick = () => {
    // This is optional since we have a global handler, but good for robustness
}

</script>

<style scoped>
.html-embed-wrapper {
    display: flex;
    position: relative;
    user-select: none; /* Important for drag */
    line-height: 0;
    transition: all 0.2s ease;
    clear: both;
}

.html-embed-wrapper.float-left {
    float: left;
    display: block;
    clear: none;
}

.html-embed-wrapper.float-right {
    float: right;
    display: block;
    clear: none;
}

.html-embed-wrapper.inline-block {
    display: inline-block;
    vertical-align: middle;
}

.html-embed-container {
    position: relative; /* Anchor for handles */
    transition: box-shadow 0.2s ease;
    border: 1px solid transparent; /* default invisible border */
    min-width: 50px;
    min-height: 50px;
}

.html-embed-container.is-selected {
    outline: 2px solid hsl(var(--primary));
    outline-offset: 2px;
}

.html-embed-container.is-empty {
    border: 2px dashed hsl(var(--muted-foreground)/0.3);
    border-radius: 0.5rem;
    background: hsl(var(--muted)/0.3);
}

.html-embed-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    min-width: 200px;
    min-height: 120px;
    pointer-events: none; /* Let clicks pass through to container */
}

.html-embed-content {
    width: 100%;
    height: 100%;
    overflow: hidden; /* Prevent spillover */
}

/* Ensure iframes fill the container */
:deep(iframe) {
    width: 100%;
    height: 100%;
    border: none;
    pointer-events: none; /* Crucial: allows clicking on the container to select it, instead of interacting with iframe */
}

/* When interactive, allow pointer events on iframe */
.html-embed-content.is-interactive :deep(iframe) {
    pointer-events: auto;
}

/* Re-enable pointer events on iframe only when we might want to interact? 
   Actually for editor, it's better to disable iframe interaction so we can select/drag the node. 
   Interaction happens in preview/frontend or via property editing. 
   Or we can use a small overlay. 
*/
/* .is-selected :deep(iframe) handled by .is-interactive class now */


/* Resize Handles */
.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: hsl(var(--primary));
    border: 1px solid hsl(var(--background));
    border-radius: 50%; /* Circle handles */
    z-index: 50;
    display: none; /* Hidden by default */
}

.is-selected .resize-handle {
    display: block;
}

.resize-handle.top-left { top: -5px; left: -5px; cursor: nwse-resize; }
.resize-handle.top-right { top: -5px; right: -5px; cursor: nesw-resize; }
.resize-handle.bottom-left { bottom: -5px; left: -5px; cursor: nesw-resize; }
.resize-handle.bottom-right { bottom: -5px; right: -5px; cursor: nwse-resize; }

.resize-handle.left { 
    top: 50%; left: -5px; transform: translateY(-50%); 
    cursor: ew-resize; 
    border-radius: 2px; height: 16px; width: 6px;
}
.resize-handle.right { 
    top: 50%; right: -5px; transform: translateY(-50%); 
    cursor: ew-resize;
    border-radius: 2px; height: 16px; width: 6px;
}

</style>
