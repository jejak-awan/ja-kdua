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
    padding: { type: [String, Object], default: '' },
    margin: { type: [String, Object], default: '' },
    backgroundColor: { type: String, default: '' },
    backgroundImage: { type: String, default: '' },
    boxShadow: { type: String, default: 'none' },
    border: { type: String, default: '' },
    borderRadius: { type: String, default: '' },
    
    animation: { type: String, default: '' }
});

const wrapperStyles = computed(() => {
    // If props are passed as simple strings/numbers, use them
    // If they are passed as objects (responsive), we might need a utility or just take the default/desktop value
    // Assuming flat props for now for simplicity in this pass, or using what's available
    return {
        backgroundColor: props.backgroundColor || 'transparent',
        padding: typeof props.padding === 'string' ? props.padding : (props.padding?.desktop || props.padding),
        margin: typeof props.margin === 'string' ? props.margin : (props.margin?.desktop || props.margin),
        boxShadow: props.boxShadow,
        border: props.border,
        borderRadius: props.borderRadius,
        textAlign: props.alignment // Apply alignment to wrapper for block flow
    };
});

const headingStyles = computed(() => {
    return {
        color: props.textColor || 'inherit',
        textAlign: props.alignment
    }
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
