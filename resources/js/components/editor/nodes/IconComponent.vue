<template>
    <node-view-wrapper 
        as="span" 
        class="inline-block align-middle select-none mx-0.5 leading-none relative group"
        :class="{ 'ring-1 ring-primary rounded-sm': selected }"
        :style="wrapperStyle"
    >
        <component 
            :is="iconComponent" 
            :size="sizeValue"
            :stroke-width="node.attrs.strokeWidth"
            class="transition-all"
            :style="iconStyle"
        />

        <!-- Resize Handle (Only visible when selected) -->
        <div 
            v-if="selected"
            class="absolute -right-1 -bottom-1 w-3 h-3 bg-primary border border-white rounded-full cursor-se-resize z-10"
            @mousedown.stop.prevent="startResize"
        ></div>
    </node-view-wrapper>
</template>

<script setup>
import { computed, ref, onUnmounted } from 'vue';
import { NodeViewWrapper } from '@tiptap/vue-3';
import * as LucideIcons from 'lucide-vue-next';

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
    }
});

const iconComponent = computed(() => {
    const name = props.node.attrs.name;
    return LucideIcons[name] || LucideIcons.Circle;
});

// Computed Styles
const wrapperStyle = computed(() => ({
    backgroundColor: props.node.attrs.backgroundColor,
    borderRadius: props.node.attrs.borderRadius,
    padding: props.node.attrs.padding,
    transform: `rotate(${props.node.attrs.rotate}deg)`,
    opacity: props.node.attrs.opacity,
}));

const iconStyle = computed(() => ({
    color: props.node.attrs.color,
    width: props.node.attrs.size,
    height: props.node.attrs.size,
}));

const sizeValue = computed(() => {
    // If size is '1em' or string, pass it to style, but Lucide prop expects number or string.
    return props.node.attrs.size;
});

// Resize Logic
const isResizing = ref(false);
const startX = ref(0);
const startWidth = ref(0);

const startResize = (event) => {
    isResizing.value = true;
    startX.value = event.clientX;
    
    // Parse current size to pixels if possible, or approximate
    // Limit resizing to pixel values for consistency during drag
    const currentSize = props.node.attrs.size;
    
    if (typeof currentSize === 'string' && currentSize.endsWith('px')) {
        startWidth.value = parseInt(currentSize);
    } else if (typeof currentSize === 'string' && currentSize.endsWith('em')) {
         // Convert em to approx px for resizing interaction (assuming 16px base)
         // This is a rough approximation to start the drag
         startWidth.value = parseFloat(currentSize) * 16;
    } else {
        startWidth.value = 24; // Default fallback
    }

    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
};

const onResize = (event) => {
    if (!isResizing.value) return;
    
    const diff = event.clientX - startX.value;
    const newSize = Math.max(12, startWidth.value + diff); // Min 12px
    
    props.updateAttributes({
        size: `${Math.round(newSize)}px`
    });
};

const stopResize = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
};

onUnmounted(() => {
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
});
</script>
