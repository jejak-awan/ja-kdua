<script setup>
import { ref, computed, onMounted, watch } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    value: { type: Number, default: 75 },
    max: { type: Number, default: 100 },
    title: { type: String, default: '' },
    label: { type: String, default: '' },
    size: { type: String, default: 'medium' },
    thickness: { type: Number, default: 8 },
    color: { type: String, default: '' },
    show_value: { type: Boolean, default: true },
    animate: { type: Boolean, default: true },
    padding: { type: String, default: 'py-8' },
    alignment: { type: String, default: 'center' }
});

const animatedValue = ref(0);
const hasAnimated = ref(false);

const percentage = computed(() => Math.min(100, Math.max(0, ((props.value || 0) / (props.max || 100)) * 100)));

const sizeConfig = computed(() => {
    const sizes = {
        small: { size: 100, fontSize: 'text-xl' },
        medium: { size: 150, fontSize: 'text-3xl' },
        large: { size: 200, fontSize: 'text-4xl' }
    };
    return sizes[props.size] || sizes.medium;
});

const circumference = computed(() => 2 * Math.PI * ((sizeConfig.value.size - (props.thickness || 8)) / 2));

const strokeDashoffset = computed(() => {
    const progress = props.animate ? animatedValue.value : percentage.value;
    return circumference.value - (progress / 100) * circumference.value;
});

const strokeColor = computed(() => props.color || 'hsl(var(--primary))');

const alignmentClass = computed(() => {
    if (props.alignment === 'left') return 'mx-0';
    if (props.alignment === 'right') return 'ml-auto mr-0';
    return 'mx-auto';
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding || ''].filter(Boolean);
});

onMounted(() => {
    if (props.animate) {
        const duration = 1500;
        const steps = 60;
        const increment = percentage.value / steps;
        let current = 0;
        
        const interval = setInterval(() => {
            current += increment;
            if (current >= percentage.value) {
                animatedValue.value = percentage.value;
                clearInterval(interval);
                hasAnimated.value = true;
            } else {
                animatedValue.value = current;
            }
        }, duration / steps);
    } else {
        animatedValue.value = percentage.value;
    }
});

watch(() => props.value, () => {
    if (hasAnimated.value) {
        animatedValue.value = percentage.value;
    }
});
</script>

<template>
    <div :class="containerClasses">
        <div 
            :class="['flex flex-col items-center gap-4', alignmentClass]"
            :style="{ width: sizeConfig.size + 'px' }"
        >
            <!-- Circle SVG -->
            <div class="relative" :style="{ width: sizeConfig.size + 'px', height: sizeConfig.size + 'px' }">
                <svg 
                    :width="sizeConfig.size" 
                    :height="sizeConfig.size"
                    class="transform -rotate-90"
                >
                    <!-- Background circle -->
                    <circle
                        :cx="sizeConfig.size / 2"
                        :cy="sizeConfig.size / 2"
                        :r="(sizeConfig.size - (thickness || 8)) / 2"
                        fill="none"
                        stroke="hsl(var(--muted))"
                        :stroke-width="thickness || 8"
                    />
                    <!-- Progress circle -->
                    <circle
                        :cx="sizeConfig.size / 2"
                        :cy="sizeConfig.size / 2"
                        :r="(sizeConfig.size - (thickness || 8)) / 2"
                        fill="none"
                        :stroke="strokeColor"
                        :stroke-width="thickness || 8"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="strokeDashoffset"
                        stroke-linecap="round"
                        class="transition-all duration-300"
                    />
                </svg>
                
                <!-- Center Content -->
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span 
                        v-if="show_value" 
                        :class="['font-extrabold tabular-nums', sizeConfig.fontSize]"
                        :style="{ color: strokeColor }"
                    >
                        {{ Math.round(animate ? animatedValue : percentage) }}%
                    </span>
                    <span v-if="title" class="text-xs text-muted-foreground font-medium uppercase tracking-wider mt-1">
                        {{ title }}
                    </span>
                </div>
            </div>
            
            <!-- Label below -->
            <p v-if="label" class="text-center text-sm text-muted-foreground">{{ label }}</p>
        </div>
    </div>
</template>
