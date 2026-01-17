<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent', color: textColor || 'inherit' }"
    >
        <div :class="['container mx-auto px-6 text-center space-y-8', width]">
            <h2 v-if="title" class="text-4xl md:text-6xl font-extrabold tracking-tight">{{ title }}</h2>
            <p v-if="subtitle" class="text-xl opacity-90 leading-relaxed max-w-2xl mx-auto">{{ subtitle }}</p>
            <div class="pt-4 mb-4">
                <a 
                    :href="buttonUrl || '#'" 
                    class="inline-flex items-center px-10 py-5 bg-primary text-primary-foreground rounded-full font-bold text-lg hover:shadow-2xl transition-all shadow-xl transform hover:-translate-y-1 active:scale-95"
                >
                    {{ buttonText || 'Get Started' }}
                    <ArrowRight class="ml-2 w-5 h-5" />
                </a>
            </div>

            <!-- Nested Blocks Area -->
            <div class="relative min-h-[50px]">
                <!-- Live Mode -->
                <div class="space-y-4">
                     <BlockRenderer 
                        :blocks="nestedBlocks" 
                        :context="context"
                        :is-preview="isPreview"
                    />
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import BlockRenderer from '../BlockRenderer.vue';

const props = defineProps({
    id: String,
    title: String,
    subtitle: String,
    buttonText: String,
    buttonUrl: String,
    padding: { type: String, default: 'py-24' },
    width: { type: String, default: 'max-w-4xl' },
    bgColor: String,
    textColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' },
    blocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

// Nested blocks logic
const nestedBlocks = computed(() => props.blocks || []);
</script>
