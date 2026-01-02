<template>
    <section 
        :class="['relative overflow-hidden transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <!-- Background Image Overlay -->
        <div 
            v-if="bgImage" 
            class="absolute inset-0 bg-cover bg-center z-0" 
            :style="{ backgroundImage: `url(${bgImage})` }"
        >
            <div class="absolute inset-0 bg-black/50"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center">
            <h1 v-if="title" class="text-5xl md:text-7xl font-extrabold mb-6 tracking-tight text-white drop-shadow-lg">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="text-xl md:text-2xl text-white/80 max-w-3xl mx-auto leading-relaxed drop-shadow">
                {{ subtitle }}
            </p>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    title: String,
    subtitle: String,
    bgImage: String,
    bgColor: { type: String, default: 'transparent' },
    padding: { type: String, default: 'py-32' },
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});
</script>
