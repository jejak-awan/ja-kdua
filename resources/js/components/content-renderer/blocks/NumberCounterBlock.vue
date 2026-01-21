<template>
    <section 
        ref="el"
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['flex', layoutClass, alignmentClass, 'gap-6']">
            <div class="number-display flex items-baseline justify-center" :class="alignmentClass">
                <span v-if="prefix" class="text-3xl md:text-5xl font-bold opacity-80">{{ prefix }}</span>
                <span class="text-4xl md:text-6xl font-bold tracking-tight text-primary mx-1">
                    {{ displayValue }}
                </span>
                <span v-if="suffix" class="text-3xl md:text-5xl font-bold opacity-80">{{ suffix }}</span>
            </div>
            
            <div class="counter-text max-w-lg" :class="alignmentClass">
                <h3 v-if="title" class="text-xl md:text-2xl font-bold mb-2">{{ title }}</h3>
                <p v-if="description" class="text-muted-foreground">{{ description }}</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useIntersectionObserver } from '@vueuse/core';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    number: { type: [Number, String], default: 100 },
    prefix: { type: String, default: '' },
    suffix: { type: String, default: '%' },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    duration: { type: [Number, String], default: 2000 },
    decimals: { type: [Number, String], default: 0 },
    separator: { type: Boolean, default: true },
    layout: { type: String, default: 'vertical' },
    alignment: { type: String, default: 'center' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const el = ref(null);
const currentValue = ref(0);
const hasAnimated = ref(false);

const targetValue = computed(() => Number(props.number) || 0);

const displayValue = computed(() => {
    let val = currentValue.value;
    if (props.separator) {
        return val.toLocaleString('en-US', { 
            minimumFractionDigits: Number(props.decimals), 
            maximumFractionDigits: Number(props.decimals) 
        });
    }
    return val.toFixed(Number(props.decimals));
});

const startAnimation = () => {
    if (hasAnimated.value) return;
    hasAnimated.value = true;
    
    const start = performance.now();
    const durationMs = Number(props.duration) || 2000;
    
    const animate = (now) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / durationMs, 1);
        
        // EaseOutCubic
        const ease = 1 - Math.pow(1 - progress, 3);
        
        currentValue.value = ease * targetValue.value;
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            currentValue.value = targetValue.value;
        }
    };
    
    requestAnimationFrame(animate);
};

useIntersectionObserver(el, ([entry]) => {
    if (entry.isIntersecting) {
        startAnimation();
    }
});

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding].filter(Boolean);
});

const layoutClass = computed(() => {
    return props.layout === 'horizontal' ? 'flex-row items-center text-left' : 'flex-col text-center';
});

const alignmentClass = computed(() => {
    if (props.layout === 'horizontal') return 'text-left'; // Horizontal implies left align usually
    
    return {
        'left': 'text-left items-start justify-start',
        'center': 'text-center items-center justify-center',
        'right': 'text-right items-end justify-end'
    }[props.alignment] || 'text-center items-center justify-center';
});
</script>
