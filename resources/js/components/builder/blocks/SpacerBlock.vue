<template>
    <div 
        :class="['w-full transition-all duration-500', height, padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div v-if="showLine" class="h-px w-full max-w-7xl mx-auto bg-primary/10"></div>
    </div>
</template>

<script setup>
const props = defineProps({
    height: { type: String, default: 'h-12' },
    showLine: { type: Boolean, default: false },
    padding: { type: String, default: 'py-0' },
    bgColor: String,
    animation: { type: String, default: '' }
});
</script>
