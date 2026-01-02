<template>
    <section 
        :class="['px-6 transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto prose prose-lg dark:prose-invert prose-primary', width, alignment]">
            <h2 v-if="title" class="text-3xl font-bold mb-6 tracking-tight">{{ title }}</h2>
            <div v-html="content" class="leading-relaxed whitespace-pre-wrap"></div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: String,
    content: String,
    alignment: { type: String, default: 'text-left' },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-4xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});
</script>
