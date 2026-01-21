<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto', maxWidth || 'max-w-4xl']">
            <div 
                v-if="embedType === 'code'" 
                class="embed-code-wrapper"
                v-html="embedCode"
            ></div>
            
            <div 
                v-else-if="embedType === 'url'" 
                class="embed-url-wrapper relative w-full overflow-hidden rounded-xl bg-muted"
                :style="{ paddingBottom: aspectRatioPadding }"
            >
                <iframe 
                    :src="embedUrl" 
                    class="absolute inset-0 w-full h-full border-0"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    embedType: { type: String, default: 'code' },
    embedCode: { type: String, default: '' },
    embedUrl: { type: String, default: '' },
    aspectRatio: { type: String, default: '16:9' },
    maxWidth: { type: String, default: '100%' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding, props.animation].filter(Boolean);
});

const aspectRatioPadding = computed(() => {
    if (props.aspectRatio === 'auto') return '56.25%'; // Default fallback
    const [w, h] = props.aspectRatio.split(':').map(Number);
    if (!w || !h) return '56.25%'; // 16:9
    return `${(h / w) * 100}%`;
});
</script>

<style scoped>
.embed-code-wrapper :deep(iframe) {
    max-width: 100%;
}
</style>
