<template>
    <section 
        :class="['px-6 transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto overflow-hidden shadow-2xl transition-all duration-500', width, radius]">
            <img v-if="url" :src="url" :alt="title || 'Image'" class="w-full h-auto object-cover" />
            <div v-if="title" class="p-6 bg-card border-t italic text-center">
                {{ title }}
            </div>
            <div v-if="!url" class="p-20 bg-muted/30 border-2 border-dashed border-primary/10 flex items-center justify-center">
                <p class="text-xs font-bold text-muted-foreground/40 uppercase tracking-widest">No Image Selected</p>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: String,
    url: String,
    width: { type: String, default: 'max-w-5xl' },
    padding: { type: String, default: 'py-16' },
    bgColor: String,
    radius: { type: String, default: 'rounded-2xl' },
    animation: { type: String, default: '' }
});
</script>
