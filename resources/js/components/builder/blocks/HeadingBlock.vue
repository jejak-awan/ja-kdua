<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <component 
                :is="tag"
                :class="[
                    'font-bold tracking-tight transition-all',
                    sizeClass,
                    alignmentClass,
                    decorationClass
                ]"
                :style="{ color: textColor || 'inherit' }"
            >
                {{ text || 'Heading Text' }}
            </component>
            <p 
                v-if="subtitle" 
                :class="['mt-4 text-muted-foreground', subtitleSizeClass, alignmentClass]"
            >
                {{ subtitle }}
            </p>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    text: { type: String, default: 'Heading Text' },
    subtitle: { type: String, default: '' },
    tag: { type: String, default: 'h2' },
    size: { type: String, default: 'large' },
    alignment: { type: String, default: 'left' },
    decoration: { type: String, default: 'none' },
    textColor: { type: String, default: '' },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const sizeClass = computed(() => ({
    'small': 'text-xl md:text-2xl',
    'medium': 'text-2xl md:text-3xl',
    'large': 'text-3xl md:text-4xl',
    'xlarge': 'text-4xl md:text-5xl',
    'display': 'text-5xl md:text-7xl'
}[props.size] || 'text-3xl md:text-4xl'));

const subtitleSizeClass = computed(() => ({
    'small': 'text-sm',
    'medium': 'text-base',
    'large': 'text-lg',
    'xlarge': 'text-xl',
    'display': 'text-xl'
}[props.size] || 'text-lg'));

const alignmentClass = computed(() => ({
    'left': 'text-left',
    'center': 'text-center mx-auto',
    'right': 'text-right ml-auto'
}[props.alignment] || 'text-left'));

const decorationClass = computed(() => ({
    'none': '',
    'underline': 'underline underline-offset-8 decoration-primary decoration-4',
    'highlight': 'bg-gradient-to-r from-primary/20 to-primary/10 px-4 py-2 rounded-lg inline-block',
    'gradient': 'bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent'
}[props.decoration] || ''));
</script>
