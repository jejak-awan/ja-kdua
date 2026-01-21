<script setup>
import { computed } from 'vue';
import { Quote as QuoteIcon } from 'lucide-vue-next';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    content: { type: String, default: '' },
    authorName: { type: String, default: '' },
    authorTitle: { type: String, default: '' },
    authorImage: { type: String, default: '' },
    alignment: { type: String, default: 'center' },
    layout: { type: String, default: 'default' },
    showQuoteIcon: { type: Boolean, default: true },
    quoteIconName: { type: String, default: 'Quote' },
    quoteIconColor: { type: String, default: '#e0e0e0' },
    quoteIconSize: { type: [Number, String], default: 48 },
    // Styling props for backward compatibility or direct overrides
    radius: { type: String, default: 'rounded-2xl' },
    animation: { type: String, default: '' }
});

const authorInitial = computed(() => {
    const name = props.authorName || 'A';
    return name.charAt(0);
});

const containerClasses = computed(() => {
    const layouts = {
        default: 'p-8',
        card: 'bg-card border shadow-sm p-8 rounded-2xl',
        minimal: 'p-4 border-l-4 border-primary'
    };
    
    return [
        'testimonial-renderer transition-all duration-300',
        layouts[props.layout] || layouts.default,
        props.animation
    ].filter(Boolean);
});

const itemsAlignClass = computed(() => {
    if (props.alignment === 'center') return 'text-center items-center';
    if (props.alignment === 'right') return 'text-right items-end';
    return 'text-left items-start';
});

const currentIcon = computed(() => {
    // Normalize icon name to PascalCase for Lucide
    const name = props.quoteIconName || 'Quote';
    const normalizedName = name.charAt(0).toUpperCase() + name.slice(1);
    return Icons[normalizedName] || Icons.Quote;
});

const iconStyles = computed(() => ({
    color: props.quoteIconColor,
    width: `${props.quoteIconSize}px`,
    height: `${props.quoteIconSize}px`
}));
</script>

<template>
    <div :class="containerClasses" class="relative overflow-hidden group">
        <!-- Floating Quote Icon (Background decor) -->
        <component 
            v-if="showQuoteIcon"
            :is="currentIcon" 
            class="absolute -top-4 -right-4 opacity-[0.03] scale-[3] rotate-[15deg] transition-transform group-hover:rotate-0 group-hover:scale-[3.5] duration-700"
            :style="{ color: quoteIconColor }"
        />

        <div class="flex flex-col gap-6 relative z-10" :class="itemsAlignClass">
            <!-- Small Quote Icon -->
            <div v-if="showQuoteIcon" class="transition-transform group-hover:scale-110 duration-300">
                <component :is="currentIcon" :style="iconStyles" />
            </div>

            <!-- Quote Text -->
            <blockquote 
                class="text-xl md:text-2xl font-medium leading-relaxed italic text-foreground/90 max-w-3xl"
            >
                "{{ content || 'Add your testimonial quote here.' }}"
            </blockquote>

            <!-- Author Info -->
            <div 
                class="flex items-center gap-4 mt-2" 
                :class="[
                    alignment === 'right' ? 'flex-row-reverse' : '',
                    alignment === 'center' ? 'justify-center' : ''
                ]"
            >
                <!-- Avatar -->
                <div v-if="authorImage" class="w-14 h-14 rounded-full overflow-hidden border-2 border-background shadow-md">
                    <img :src="authorImage" :alt="authorName || 'Author'" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl border-2 border-background shadow-md">
                    {{ authorInitial }}
                </div>

                <!-- Text -->
                <div :class="alignment === 'right' ? 'text-right' : 'text-left'">
                    <h4 class="font-bold text-foreground">{{ authorName || 'Author Name' }}</h4>
                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-widest">{{ authorTitle || 'Position' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
