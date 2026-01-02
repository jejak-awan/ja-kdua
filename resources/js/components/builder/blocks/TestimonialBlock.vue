<template>
    <div :class="containerClasses">
        <div class="flex flex-col gap-6" :class="itemsAlignClass">
            <!-- Quote Icon -->
            <div class="text-primary opacity-20">
                <QuoteIcon :size="48" />
            </div>

            <!-- Quote Text -->
            <blockquote 
                :class="['text-xl md:text-2xl font-medium leading-relaxed italic', alignment || 'text-left']"
                :style="{ color: quote_color || '' }"
            >
                "{{ quote || 'Add your testimonial quote here.' }}"
            </blockquote>

            <!-- Author Info -->
            <div class="flex items-center gap-4 mt-4" :class="alignment === 'text-right' ? 'flex-row-reverse' : ''">
                <!-- Avatar -->
                <div v-if="avatar" class="w-14 h-14 rounded-full overflow-hidden border-2 border-background shadow-md">
                    <img :src="avatar" :alt="author || 'Author'" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl border-2 border-background shadow-md">
                    {{ authorInitial }}
                </div>

                <!-- Text -->
                <div :class="alignment === 'text-right' ? 'text-right' : 'text-left'">
                    <h4 class="font-bold text-foreground">{{ author || 'Author Name' }}</h4>
                    <p class="text-xs text-muted-foreground uppercase tracking-widest font-semibold">{{ job_title || 'Position' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Quote as QuoteIcon } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    quote: { type: String, default: '' },
    author: { type: String, default: '' },
    job_title: { type: String, default: '' },
    avatar: { type: String, default: '' },
    alignment: { type: String, default: 'text-left' },
    style: { type: String, default: 'bg-card border shadow-sm' },
    padding: { type: String, default: 'p-8' },
    radius: { type: String, default: 'rounded-2xl' },
    quote_color: { type: String, default: '' }
});

const authorInitial = computed(() => {
    const name = props.author || 'A';
    return name.charAt(0).toUpperCase();
});

const containerClasses = computed(() => {
    return [
        'transition-all duration-300',
        props.style || '',
        props.padding || '',
        props.radius || '',
        props.alignment || ''
    ].filter(Boolean);
});

const itemsAlignClass = computed(() => {
    if (props.alignment === 'text-center') return 'items-center';
    if (props.alignment === 'text-right') return 'items-end';
    return 'items-start';
});
</script>
