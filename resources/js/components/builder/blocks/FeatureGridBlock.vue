<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h2 v-if="title" class="text-3xl font-bold mb-12 text-center tracking-tight">{{ title }}</h2>
            <div class="grid gap-8 md:grid-cols-3">
                <div 
                    v-for="(item, index) in items" 
                    :key="index"
                    class="p-8 rounded-2xl border bg-card/50 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20 hover:-translate-y-1"
                >
                    <h3 class="font-bold text-xl mb-3">{{ item.title }}</h3>
                    <p class="text-muted-foreground leading-relaxed">{{ item.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    title: String,
    items: { type: Array, default: () => [] },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});
</script>
