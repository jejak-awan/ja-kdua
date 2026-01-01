<template>
    <section 
        :class="['px-6 transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto', width]">
            <h2 v-if="title" class="text-3xl font-bold mb-10 text-center tracking-tight">{{ title }}</h2>
            
            <div :class="['grid gap-4 md:gap-6', gridCols]">
                <div 
                    v-for="(image, index) in images" 
                    :key="index"
                    class="group relative aspect-square overflow-hidden rounded-2xl bg-muted/50 border border-primary/5 cursor-pointer shadow-lg transition-all duration-500 hover:shadow-2xl hover:border-primary/20"
                >
                    <img 
                        :src="image.url || '/images/placeholder.svg'" 
                        :alt="image.caption || 'Gallery Image'"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    >
                    <div v-if="image.caption" class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-6 opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0">
                        <p class="text-white text-sm font-semibold tracking-wide">{{ image.caption }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    images: { type: Array, default: () => [] },
    columns: { type: [String, Number], default: 3 },
    width: { type: String, default: 'max-w-6xl' },
    padding: { type: String, default: 'py-16' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});

const gridCols = computed(() => {
    const cols = parseInt(props.columns);
    if (cols === 2) return 'grid-cols-2';
    if (cols === 4) return 'grid-cols-2 lg:grid-cols-4';
    return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
});
</script>
