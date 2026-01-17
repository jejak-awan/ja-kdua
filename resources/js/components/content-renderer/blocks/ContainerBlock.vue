<template>
    <div 
        class="relative min-h-[50px] transition-all duration-300 group/container h-full"
        :class="[
            displayClass,
            direction, justify, align, wrap, 
            radius,
            shadow !== 'none' ? shadow : ''
        ]"
        :style="containerStyle"
        v-bind="$attrs"
    >
        <!-- Render Content -->
        <div 
            class="flex w-full h-full"
            :class="[containerLayoutClasses, justify, align]"
            :style="{ gap: gap }"
        >
             <BlockRenderer 
                :blocks="nestedBlocks" 
                :context="context"
                :is-preview="isPreview"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import BlockRenderer from '../BlockRenderer.vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    id: String,
    // Settings
    layout: { type: [String, Object], default: 'stack' }, // stack | row | grid OR { mobile: 'stack', desktop: 'row' }
    direction: { type: String, default: 'flex-col' }, // Legacy/Override
    justify: { type: String, default: 'justify-start' },
    align: { type: String, default: 'items-start' },
    wrap: { type: String, default: 'flex-wrap' }, // Changed default to flex-wrap for Smart Layout capability
    gap: { type: String, default: 'gap-4' }, // proper gap class default
    
    width: { type: String, default: '100%' },
    maxWidth: { type: String, default: 'none' },
    minHeight: { type: String, default: '0' },
    
    padding: { type: Object, default: () => ({ top: '16px', right: '16px', bottom: '16px', left: '16px' }) },
    margin: { type: Object, default: () => ({ top: '0', right: '0', bottom: '0', left: '0' }) },
    
    bgColor: String,
    borderWidth: { type: Number, default: 0 },
    borderColor: String,
    radius: { type: String, default: 'rounded-none' },
    shadow: { type: String, default: 'none' },
    overflow: { type: String, default: 'visible' },
    
    blocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: { type: Boolean, default: false }
});

const nestedBlocks = computed(() => props.blocks || []);

// Helper to map layout type to classes
const getLayoutClasses = (type) => {
    if (type === 'row') return ['flex-row', 'flex-wrap'];
    if (type === 'stack') return ['flex-col'];
    if (type === 'grid') return ['grid']; // rudimentary grid support
    return [];
};

// Smart Layout Logic
const containerLayoutClasses = computed(() => {
    const classes = [];
    
    if (typeof props.layout === 'string') {
        classes.push(...getLayoutClasses(props.layout));
        if (!props.layout || props.layout === 'stack' || props.layout === 'row') {
             // Append legacy manual overrides only if using simple string layout
             classes.push(props.direction, props.wrap); 
        }
    } else if (typeof props.layout === 'object') {
        // Handle Responsive Object: { mobile: 'stack', tablet: 'row', desktop: 'row' }
        // Mobile (default)
        if (props.layout.mobile) {
             classes.push(...getLayoutClasses(props.layout.mobile));
        }
        
        // Tablet (md:)
        if (props.layout.tablet) {
            const tabletClasses = getLayoutClasses(props.layout.tablet);
            classes.push(...tabletClasses.map(c => `md:${c}`));
        }
        
        // Desktop (lg:)
        if (props.layout.desktop) {
            const desktopClasses = getLayoutClasses(props.layout.desktop);
            classes.push(...desktopClasses.map(c => `lg:${c}`));
        }
    }

    return classes;
});

const displayClass = computed(() => {
    const base = ['flex'];
    // Root should also reflect layout to hold structure
    base.push(...containerLayoutClasses.value);
    return base;
});


const containerStyle = computed(() => {
    const p = props.padding || {};
    const m = props.margin || {};
    
    const style = {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left,
        marginTop: m.top,
        marginRight: m.right,
        marginBottom: m.bottom,
        marginLeft: m.left,
        width: props.width,
        maxWidth: props.maxWidth,
        minHeight: props.minHeight,
        backgroundColor: props.bgColor,
        overflow: props.overflow
    };
    
    if (props.borderWidth > 0) {
        style.borderWidth = `${props.borderWidth}px`;
        style.borderColor = props.borderColor;
        style.borderStyle = 'solid';
    }
    
    return style;
});
</script>
