<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <div v-if="label" class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium">{{ label }}</span>
                <span v-if="showValue" class="text-sm font-bold">{{ value }}%</span>
            </div>
            <div :class="['w-full overflow-hidden', heightClass, bgClass, radiusClass]">
                <div 
                    class="h-full transition-all duration-1000 ease-out"
                    :class="[barClass, radiusClass]"
                    :style="{ width: value + '%', backgroundColor: barColor }"
                ></div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    value: { type: [Number, String], default: 75 },
    label: { type: String, default: 'Progress' },
    showValue: { type: Boolean, default: true },
    height: { type: String, default: 'medium' },
    variant: { type: String, default: 'primary' },
    barColor: { type: String, default: '' },
    striped: { type: Boolean, default: false },
    animated: { type: Boolean, default: false },
    radius: { type: String, default: 'rounded-full' },
    width: { type: String, default: 'max-w-2xl' },
    padding: { type: String, default: 'py-6' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const heightClass = computed(() => ({
    'small': 'h-2',
    'medium': 'h-3',
    'large': 'h-5'
}[props.height] || 'h-3'));

const bgClass = computed(() => 'bg-muted');
const radiusClass = computed(() => props.radius);

const variantColors = {
    primary: 'hsl(var(--primary))',
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
    info: '#3b82f6'
};

const barClass = computed(() => {
    let classes = [];
    if (props.striped) {
        classes.push('bg-gradient-to-r from-transparent via-white/20 to-transparent bg-[length:20px_20px]');
    }
    if (props.animated) {
        classes.push('animate-pulse');
    }
    return classes.join(' ');
});
</script>
