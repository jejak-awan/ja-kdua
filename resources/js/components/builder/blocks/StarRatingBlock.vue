<script setup>
import { computed } from 'vue';
import { Star } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    rating: { type: Number, default: 4 },
    max: { type: Number, default: 5 },
    size: { type: String, default: 'medium' },
    color: { type: String, default: '' },
    show_value: { type: Boolean, default: true },
    label: { type: String, default: '' },
    alignment: { type: String, default: 'center' },
    padding: { type: String, default: 'py-4' }
});

const sizeClass = computed(() => {
    const sizes = {
        small: 'w-4 h-4',
        medium: 'w-6 h-6',
        large: 'w-8 h-8'
    };
    return sizes[props.size] || sizes.medium;
});

const starColor = computed(() => props.color || 'hsl(var(--primary))');

const stars = computed(() => {
    return Array.from({ length: props.max || 5 }, (_, i) => ({
        index: i,
        filled: i < Math.floor(props.rating || 0)
    }));
});

const alignmentClass = computed(() => {
    if (props.alignment === 'left') return 'text-left';
    if (props.alignment === 'right') return 'text-right';
    return 'text-center';
});

const itemsAlignClass = computed(() => {
    if (props.alignment === 'left') return 'items-start';
    if (props.alignment === 'right') return 'items-end';
    return 'items-center';
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding || '', alignmentClass.value].filter(Boolean);
});
</script>

<template>
    <div :class="containerClasses">
        <div :class="['inline-flex flex-col gap-2', itemsAlignClass]">
            <!-- Stars -->
            <div class="flex items-center gap-1">
                <Star 
                    v-for="star in stars" 
                    :key="star.index"
                    :class="sizeClass"
                    :style="{ color: star.filled ? starColor : 'hsl(var(--muted))' }"
                    :fill="star.filled ? starColor : 'none'"
                />
                
                <!-- Value -->
                <span 
                    v-if="show_value" 
                    class="ml-2 font-bold tabular-nums"
                    :style="{ color: starColor }"
                >
                    {{ (rating || 0).toFixed(1) }}
                </span>
            </div>
            
            <!-- Label -->
            <p v-if="label" class="text-sm text-muted-foreground">{{ label }}</p>
        </div>
    </div>
</template>
