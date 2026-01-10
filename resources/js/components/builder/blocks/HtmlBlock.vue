<template>
    <div 
        :class="['transition-all duration-300', padding]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div v-html="content"></div>
    </div>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    content: { type: String, default: '<!-- Enter your raw HTML here -->' },
    padding: { type: String, default: 'py-0' },
    bgColor: { type: String, default: 'transparent' }
});
</script>
