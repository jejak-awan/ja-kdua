<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto', width]">
            <div 
                :class="['relative', heightClass]"
            >
                <!-- Line Divider -->
                <div 
                    v-if="style === 'line'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2"
                    :class="[lineThickness]"
                    :style="{ backgroundColor: color }"
                ></div>
                
                <!-- Dashed Divider -->
                <div 
                    v-else-if="style === 'dashed'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2 border-t-2 border-dashed"
                    :style="{ borderColor: color }"
                ></div>
                
                <!-- Dotted Divider -->
                <div 
                    v-else-if="style === 'dotted'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2 border-t-2 border-dotted"
                    :style="{ borderColor: color }"
                ></div>
                
                <!-- Double Line -->
                <div 
                    v-else-if="style === 'double'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2 flex flex-col gap-1"
                >
                    <div class="h-0.5" :style="{ backgroundColor: color }"></div>
                    <div class="h-0.5" :style="{ backgroundColor: color }"></div>
                </div>
                
                <!-- Gradient Divider -->
                <div 
                    v-else-if="style === 'gradient'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-0.5"
                    :style="{ background: `linear-gradient(to right, transparent, ${color}, transparent)` }"
                ></div>
                
                <!-- Shadow Divider -->
                <div 
                    v-else-if="style === 'shadow'"
                    class="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-px bg-gradient-to-b from-border to-transparent shadow-lg"
                ></div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';

const props = defineProps({
    style: { type: String, default: 'line' },
    color: { type: String, default: 'hsl(var(--border))' },
    height: { type: String, default: 'medium' },
    width: { type: String, default: 'max-w-full' },
    padding: { type: String, default: 'py-6' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const heightClass = computed(() => ({
    'small': 'h-4',
    'medium': 'h-8',
    'large': 'h-16'
}[props.height] || 'h-8'));

const lineThickness = computed(() => ({
    'small': 'h-px',
    'medium': 'h-0.5',
    'large': 'h-1'
}[props.height] || 'h-0.5'));
</script>
