<template>
    <nav :class="containerClasses">
        <div class="container mx-auto px-6">
            <div :class="navLayoutClasses">
                <!-- Previous -->
                <a 
                    v-if="prev_url"
                    :href="prev_url"
                    :class="prevLinkClasses"
                >
                    <div :class="prevIconClasses">
                        <ChevronLeft class="w-5 h-5" />
                    </div>
                    <div :class="style === 'cards' ? 'flex-1' : ''">
                        <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ prev_label || 'Previous' }}</span>
                        <p v-if="show_titles" class="font-semibold text-foreground line-clamp-1 mt-1">{{ prev_title || 'Previous Post' }}</p>
                    </div>
                </a>
                <div v-else class="flex-1"></div>
                
                <!-- Divider (simple style only) -->
                <div v-if="style === 'simple'" class="hidden md:block w-px bg-border"></div>
                
                <!-- Next -->
                <a 
                    v-if="next_url"
                    :href="next_url"
                    :class="nextLinkClasses"
                >
                    <div :class="nextIconClasses">
                        <ChevronRight class="w-5 h-5" />
                    </div>
                    <div :class="style === 'cards' ? 'flex-1' : ''">
                        <span class="text-xs font-bold uppercase tracking-widest text-muted-foreground">{{ next_label || 'Next' }}</span>
                        <p v-if="show_titles" class="font-semibold text-foreground line-clamp-1 mt-1">{{ next_title || 'Next Post' }}</p>
                    </div>
                </a>
                <div v-else class="flex-1"></div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    prev_label: { type: String, default: 'Previous' },
    next_label: { type: String, default: 'Next' },
    prev_title: { type: String, default: 'Previous Post Title' },
    next_title: { type: String, default: 'Next Post Title' },
    prev_url: { type: String, default: '#' },
    next_url: { type: String, default: '#' },
    show_titles: { type: Boolean, default: true },
    style: { type: String, default: 'simple' },
    padding: { type: String, default: 'py-8' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const navLayoutClasses = computed(() => {
    return ['flex items-stretch gap-4', props.style === 'cards' ? 'flex-col md:flex-row' : 'justify-between'].filter(Boolean);
});

const prevLinkClasses = computed(() => {
    return [
        'group flex items-center gap-4 transition-all',
        props.style === 'cards' ? 'flex-1 p-6 bg-card border rounded-2xl hover:border-primary/50 hover:shadow-lg' : 'hover:text-primary',
        props.style === 'simple' ? 'pr-4' : ''
    ].filter(Boolean);
});

const prevIconClasses = computed(() => {
    return [
        'flex items-center justify-center transition-transform group-hover:-translate-x-1',
        props.style === 'cards' ? 'w-12 h-12 rounded-full bg-muted' : ''
    ].filter(Boolean);
});

const nextLinkClasses = computed(() => {
    return [
        'group flex items-center gap-4 transition-all text-right',
        props.style === 'cards' ? 'flex-1 p-6 bg-card border rounded-2xl hover:border-primary/50 hover:shadow-lg flex-row-reverse' : 'hover:text-primary',
        props.style === 'simple' ? 'pl-4' : ''
    ].filter(Boolean);
});

const nextIconClasses = computed(() => {
    return [
        'flex items-center justify-center transition-transform group-hover:translate-x-1',
        props.style === 'cards' ? 'w-12 h-12 rounded-full bg-muted' : ''
    ].filter(Boolean);
});
</script>
