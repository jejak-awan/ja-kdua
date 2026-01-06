<template>
    <div 
        ref="wrapper"
        class="relative group/resize"
        :class="{ 'resizable-active': isResizing }"
        :style="wrapperStyle"
    >
        <!-- Slot for content (BlockWrapper) -->
        <slot></slot>

        <!-- Resize Handles (only visible when selected in builder) -->
        <div v-if="isActive && !isPreview" class="absolute inset-0 pointer-events-none z-50">
            <!-- Edges -->
            <div class="resize-handle resize-e" @mousedown.stop="startResize($event, 'e')"></div>
            <div class="resize-handle resize-s" @mousedown.stop="startResize($event, 's')"></div>
            
            <!-- Corners -->
            <div class="resize-handle resize-se" @mousedown.stop="startResize($event, 'se')"></div>
        </div>

        <!-- Size Display Overlay -->
        <div 
            v-if="isResizing" 
            class="absolute top-2 right-2 bg-black/80 text-white text-[10px] px-2 py-1 rounded z-50 font-mono"
        >
            {{ Math.round(currentWidth) }}px × {{ Math.round(currentHeight) }}px
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    isActive: { type: Boolean, default: false },
    isPreview: { type: Boolean, default: false },
    width: { type: [String, Number], default: 'auto' },
    height: { type: [String, Number], default: 'auto' },
    minWidth: { type: Number, default: 50 },
    minHeight: { type: Number, default: 50 }
});

const emit = defineEmits(['update:width', 'update:height', 'resize-end']);

const wrapper = ref(null);
const isResizing = ref(false);
const resizeDir = ref(null);
const startX = ref(0);
const startY = ref(0);
const startWidth = ref(0);
const startHeight = ref(0);
const currentWidth = ref(0);
const currentHeight = ref(0);

const wrapperStyle = computed(() => {
    // Only apply width/height if set (and valid numbers or pixel strings)
    // Avoid overriding block's own sizing logic if 'auto'
    const style = {};
    if (props.width && props.width !== 'auto') style.width = typeof props.width === 'number' ? `${props.width}px` : props.width;
    if (props.height && props.height !== 'auto') style.height = typeof props.height === 'number' ? `${props.height}px` : props.height;
    return style;
});

const startResize = (e, dir) => {
    e.preventDefault();
    isResizing.value = true;
    resizeDir.value = dir;
    startX.value = e.clientX;
    startY.value = e.clientY;
    
    const rect = wrapper.value.getBoundingClientRect();
    startWidth.value = rect.width;
    startHeight.value = rect.height;
    currentWidth.value = rect.width;
    currentHeight.value = rect.height;

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
};

const rafId = ref(null);

const handleMouseMove = (e) => {
    if (!isResizing.value) return;

    if (rafId.value) {
        cancelAnimationFrame(rafId.value);
    }

    rafId.value = requestAnimationFrame(() => {
        const dx = e.clientX - startX.value;
        const dy = e.clientY - startY.value;

        if (resizeDir.value && resizeDir.value.includes('e')) {
            const newWidth = Math.max(props.minWidth, startWidth.value + dx);
            currentWidth.value = newWidth;
            emit('update:width', newWidth);
        }
        
        if (resizeDir.value && resizeDir.value.includes('s')) {
            const newHeight = Math.max(props.minHeight, startHeight.value + dy);
            currentHeight.value = newHeight;
            emit('update:height', newHeight);
        }
        
        rafId.value = null;
    });
};

const handleMouseUp = () => {
    if (rafId.value) {
        cancelAnimationFrame(rafId.value);
        rafId.value = null;
    }
    
    if (isResizing.value) {
        emit('resize-end', { width: currentWidth.value, height: currentHeight.value });
    }
    isResizing.value = false;
    resizeDir.value = null;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
};
</script>

<style scoped>
.resize-handle {
    position: absolute;
    pointer-events: auto; /* Enable clicks on handles */
    opacity: 0;
    transition: opacity 0.2s;
    background-color: hsl(var(--primary));
}

/* Show handles on hover of wrapper (when active) OR when resizing */
.group\/resize:hover .resize-handle,
.resizable-active .resize-handle {
    opacity: 0.5;
}
.resize-handle:hover,
.resizable-active .resize-handle {
    opacity: 1;
}

/* East Handle (Right) */
.resize-e {
    top: 50%;
    right: -4px;
    width: 6px;
    height: 24px;
    transform: translateY(-50%);
    cursor: e-resize;
    border-radius: 4px;
}

/* South Handle (Bottom) */
.resize-s {
    bottom: -4px;
    left: 50%;
    width: 24px;
    height: 6px;
    transform: translateX(-50%);
    cursor: s-resize;
    border-radius: 4px;
}

/* South-East Handle (Corner) */
.resize-se {
    bottom: -5px;
    right: -5px;
    width: 10px;
    height: 10px;
    cursor: se-resize;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
}
</style>
