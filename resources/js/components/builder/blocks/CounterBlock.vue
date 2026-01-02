<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width, alignmentClass]">
            <div 
                class="text-7xl md:text-8xl font-extrabold tabular-nums tracking-tight"
                :style="{ color: numberColor || 'inherit' }"
            >
                {{ prefix }}{{ displayNumber }}{{ suffix }}
            </div>
            <p 
                v-if="label" 
                class="mt-4 text-lg text-muted-foreground font-medium"
            >
                {{ label }}
            </p>
        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    number: { type: [Number, String], default: 100 },
    prefix: { type: String, default: '' },
    suffix: { type: String, default: '' },
    label: { type: String, default: '' },
    duration: { type: Number, default: 2000 },
    alignment: { type: String, default: 'center' },
    numberColor: { type: String, default: '' },
    width: { type: String, default: 'max-w-xl' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const displayNumber = ref(0);
const targetNumber = computed(() => parseInt(props.number) || 0);

const alignmentClass = computed(() => ({
    'left': 'text-left',
    'center': 'text-center',
    'right': 'text-right'
}[props.alignment] || 'text-center'));

const animateCounter = () => {
    const start = 0;
    const end = targetNumber.value;
    const duration = props.duration;
    const startTime = performance.now();
    
    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        displayNumber.value = Math.floor(start + (end - start) * easeOut);
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
};

onMounted(() => {
    animateCounter();
});

watch(() => props.number, () => {
    animateCounter();
});
</script>
