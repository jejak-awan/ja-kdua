<template>
    <div 
        :class="['w-full transition-all duration-500 relative', heightClass, padding, animation]"
        :style="{ 
            backgroundColor: bgColor || 'transparent',
            ...(heightPx ? { height: heightPx + 'px' } : {})
        }"
    >
        <div v-if="showLine" class="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-px max-w-7xl mx-auto bg-border"></div>
    </div>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';

const props = defineProps({
    height: { type: String, default: 'h-24' },
    heightPx: { type: [Number, String], default: null },
    showLine: { type: Boolean, default: false },
    padding: { type: String, default: 'py-0' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const heightClass = computed(() => {
    // If heightPx is set, don't use class-based height
    if (props.heightPx) return '';
    return props.height;
});
</script>

