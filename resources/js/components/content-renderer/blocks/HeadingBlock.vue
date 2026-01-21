<template>
    <section 
        :class="['transition-all duration-500', animation]"
        :style="wrapperStyles"
    >
        <div :class="['mx-auto px-6', width]">
            <component 
                :is="tag"
                :class="[
                    'font-bold tracking-tight transition-all',
                    sizeClass,
                    // alignmentClass, // Handled by style now
                    decorationClass
                ]"
                :style="headingStyles"
            >
                {{ text || 'Heading Text' }}
            </component>
            <p 
                v-if="subtitle" 
                :class="['mt-4 opacity-80', subtitleSizeClass, alignmentClass]"
            >
                {{ subtitle }}
            </p>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getSizingStyles, getTransformStyles, getBackgroundStyles } from '../utils';

const props = defineProps({
    text: { type: String, default: 'Heading Text' },
    subtitle: { type: String, default: '' },
    tag: { type: String, default: 'h2' },
    size: { type: String, default: 'large' },
    alignment: { type: String, default: 'left' },
    decoration: { type: String, default: 'none' },
    textColor: { type: String, default: '' },
    // Container/Common Styles
    width: { type: String, default: 'max-w-4xl' },
    padding: Object,
    margin: Object,
    backgroundColor: String,
    border: Object,
    boxShadow: [String, Object],
    borderRadius: [String, Object],
    settings: { type: Object, default: () => ({}) },
    animation: { type: String, default: '' }
});

const wrapperStyles = computed(() => {
    const s = {
        textAlign: props.alignment
    };
    
    if (props.backgroundColor) s.backgroundColor = props.backgroundColor
    if (props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if (props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
    if (props.border) Object.assign(s, getBorderStyles(props.border))
    if (props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    if (props.settings) {
        Object.assign(s, getBackgroundStyles(props.settings))
        Object.assign(s, getSizingStyles(props.settings))
        Object.assign(s, getTransformStyles(props.settings))
    }
    
    return s
});

const headingStyles = computed(() => {
    const s = {
        color: props.textColor || 'inherit',
        textAlign: props.alignment
    }
    // Size logic
    const sizes = {
        'small': { fontSize: '1.25rem' },
        'medium': { fontSize: '1.875rem' },
        'large': { fontSize: '2.5rem' },
        'xlarge': { fontSize: '3.75rem' },
        'display': { fontSize: '4.5rem' }
    }
    if (sizes[props.size]) Object.assign(s, sizes[props.size])
    
    return s
});

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
