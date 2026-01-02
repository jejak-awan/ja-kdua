<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    end_date: { type: String, default: '' },
    end_time: { type: String, default: '00:00' },
    title: { type: String, default: '' },
    expired_message: { type: String, default: 'Time is up!' },
    show_days: { type: Boolean, default: true },
    show_hours: { type: Boolean, default: true },
    show_minutes: { type: Boolean, default: true },
    show_seconds: { type: Boolean, default: true },
    digit_style: { type: String, default: 'boxed' },
    size: { type: String, default: 'large' },
    alignment: { type: String, default: 'text-center' },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' }
});

const now = ref(Date.now());
let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const targetTime = computed(() => {
    if (!props.end_date) return null;
    const dateStr = `${props.end_date}T${props.end_time || '00:00'}`;
    return new Date(dateStr).getTime();
});

const timeRemaining = computed(() => {
    if (!targetTime.value) return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    
    const diff = targetTime.value - now.value;
    
    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0, expired: true };
    }
    
    const seconds = Math.floor((diff / 1000) % 60);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    return { days, hours, minutes, seconds, expired: false };
});

const digitClass = computed(() => {
    const base = 'flex flex-col items-center justify-center font-bold tabular-nums';
    const styles = {
        boxed: 'bg-card border rounded-2xl shadow-lg',
        minimal: 'bg-transparent',
        gradient: 'bg-gradient-to-br from-primary to-primary/60 text-white rounded-2xl shadow-xl'
    };
    const sizes = {
        small: 'w-16 h-16 text-2xl',
        medium: 'w-20 h-20 text-3xl',
        large: 'w-24 h-24 text-4xl md:text-5xl'
    };
    return `${base} ${styles[props.digit_style] || styles.boxed} ${sizes[props.size] || sizes.large}`;
});

const labelClass = computed(() => {
    const sizes = {
        small: 'text-[9px]',
        medium: 'text-[10px]',
        large: 'text-xs'
    };
    return `font-semibold text-muted-foreground mt-2 ${sizes[props.size] || sizes.large}`;
});

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding, props.alignment].filter(Boolean);
});

const pad = (num) => String(num).padStart(2, '0');
</script>

<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <h2 v-if="title" class="text-3xl md:text-4xl font-extrabold mb-10 tracking-tight">{{ title }}</h2>
        
        <div v-if="!timeRemaining.expired" class="flex flex-wrap gap-4 md:gap-6" :class="{ 'justify-center': alignment === 'text-center', 'justify-start': alignment === 'text-left', 'justify-end': alignment === 'text-right' }">
            <!-- Days -->
            <div v-if="show_days" :class="digitClass">
                <span>{{ pad(timeRemaining.days) }}</span>
                <span :class="labelClass">Days</span>
            </div>
            
            <!-- Hours -->
            <div v-if="show_hours" :class="digitClass">
                <span>{{ pad(timeRemaining.hours) }}</span>
                <span :class="labelClass">Hours</span>
            </div>
            
            <!-- Minutes -->
            <div v-if="show_minutes" :class="digitClass">
                <span>{{ pad(timeRemaining.minutes) }}</span>
                <span :class="labelClass">Minutes</span>
            </div>
            
            <!-- Seconds -->
            <div v-if="show_seconds" :class="digitClass">
                <span>{{ pad(timeRemaining.seconds) }}</span>
                <span :class="labelClass">Seconds</span>
            </div>
        </div>
        
        <!-- Expired State -->
        <div v-else class="py-10">
            <p class="text-2xl md:text-3xl font-bold text-primary animate-pulse">{{ expired_message }}</p>
        </div>
    </section>
</template>
