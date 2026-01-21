<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['flex', alignmentClass]">
            <div 
                :class="[
                    'flex items-center justify-center transition-all',
                    sizeClass,
                    shapeClass
                ]"
                :style="{ 
                    backgroundColor: iconBgColor || 'transparent',
                    color: iconColor || 'currentColor',
                    width: !isNaN(size) ? `${size}px` : undefined,
                    height: !isNaN(size) ? `${size}px` : undefined
                }"
            >
                <LucideIcon 
                    :name="icon" 
                    :class="iconSizeClass" 
                    :size="numericSize"
                    :style="{
                        width: !isNaN(size) ? '50%' : undefined,
                        height: !isNaN(size) ? '50%' : undefined
                    }"
                />
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import LucideIcon from '../../ui/LucideIcon.vue';

const props = defineProps({
    icon: { type: String, default: 'star' },
    size: { type: [String, Number], default: 'medium' },
    shape: { type: String, default: 'none' },
    alignment: { type: String, default: 'center' },
    iconColor: { type: String, default: '' },
    iconBgColor: { type: String, default: '' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const alignmentClass = computed(() => ({
    'left': 'justify-start',
    'center': 'justify-center',
    'right': 'justify-end'
}[props.alignment] || 'justify-center'));

const sizeClass = computed(() => {
    // If size is a number (from builder), return empty and handle in style
    if (!isNaN(props.size)) return '';
    
    return {
        'small': 'w-12 h-12',
        'medium': 'w-16 h-16',
        'large': 'w-24 h-24',
        'xlarge': 'w-32 h-32'
    }[props.size] || 'w-16 h-16';
});

const iconSizeClass = computed(() => {
    if (!isNaN(props.size)) return '';
    
    return {
        'small': 'w-6 h-6',
        'medium': 'w-8 h-8',
        'large': 'w-12 h-12',
        'xlarge': 'w-16 h-16'
    }[props.size] || 'w-8 h-8';
});

const shapeClass = computed(() => ({
    'none': '',
    'circle': 'rounded-full',
    'rounded': 'rounded-2xl',
    'square': 'rounded-none'
}[props.shape] || ''));

const numericSize = computed(() => {
    return !isNaN(props.size) ? props.size : 24;
});
</script>
