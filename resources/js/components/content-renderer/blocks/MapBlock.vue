<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div :class="['overflow-hidden shadow-lg', radius]">
                <iframe 
                    v-if="embedUrl"
                    :src="embedUrl"
                    :height="height"
                    class="w-full border-0"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
                <div 
                    v-else 
                    :style="{ height: height + 'px' }"
                    class="w-full bg-muted flex flex-col items-center justify-center text-muted-foreground gap-4"
                >
                    <MapPin class="w-12 h-12 opacity-20" />
                    <p class="text-xs font-bold opacity-40">Enter Map Embed URL</p>
                </div>
            </div>
            <p v-if="caption" class="mt-4 text-sm text-muted-foreground text-center">{{ caption }}</p>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { MapPin } from 'lucide-vue-next';

const props = defineProps({
    embedUrl: { type: String, default: '' },
    height: { type: [Number, String], default: 400 },
    caption: { type: String, default: '' },
    radius: { type: String, default: 'rounded-xl' },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});
</script>
