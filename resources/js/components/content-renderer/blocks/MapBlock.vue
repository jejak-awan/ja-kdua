<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div :class="['overflow-hidden shadow-lg', radius]">
                <iframe 
                    v-if="finalSrc"
                    :src="finalSrc"
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
    address: { type: String, default: '' },
    embedUrl: { type: String, default: '' },
    zoom: { type: [Number, String], default: 14 },
    height: { type: [Number, String], default: 400 },
    caption: { type: String, default: '' },
    radius: { type: String, default: 'rounded-xl' },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const finalSrc = computed(() => {
    // 1. Explicit Embed URL
    if (props.embedUrl) return props.embedUrl;
    
    // 2. Generate from Address
    if (props.address) {
        const q = encodeURIComponent(props.address);
        const z = props.zoom || 14;
        return `https://maps.google.com/maps?q=${q}&z=${z}&output=embed`;
    }
    
    return '';
})
</script>
