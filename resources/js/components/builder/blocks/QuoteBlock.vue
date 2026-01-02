<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <blockquote :class="['relative', styleClass]">
                <Quote 
                    v-if="showQuoteIcon" 
                    class="absolute -top-2 -left-2 w-8 h-8 text-primary/20" 
                />
                <p :class="['text-lg md:text-xl leading-relaxed italic', quoteTextClass]">
                    "{{ quote || 'Your inspirational quote goes here...' }}"
                </p>
                <footer v-if="author || role" class="mt-4 flex items-center gap-4">
                    <img 
                        v-if="authorImage" 
                        :src="authorImage" 
                        :alt="author"
                        class="w-12 h-12 rounded-full object-cover"
                    />
                    <div>
                        <cite v-if="author" class="font-bold not-italic">{{ author }}</cite>
                        <p v-if="role" class="text-sm text-muted-foreground">{{ role }}</p>
                    </div>
                </footer>
            </blockquote>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { Quote } from 'lucide-vue-next';

const props = defineProps({
    quote: { type: String, default: 'The only way to do great work is to love what you do.' },
    author: { type: String, default: 'Steve Jobs' },
    role: { type: String, default: 'Co-founder, Apple Inc.' },
    authorImage: { type: String, default: '' },
    style: { type: String, default: 'border' },
    showQuoteIcon: { type: Boolean, default: true },
    alignment: { type: String, default: 'left' },
    width: { type: String, default: 'max-w-3xl' },
    padding: { type: String, default: 'py-12' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const styleClass = computed(() => ({
    'border': 'border-l-4 border-primary pl-6',
    'card': 'bg-muted/50 p-6 rounded-xl',
    'minimal': 'pl-4',
    'centered': 'text-center'
}[props.style] || 'border-l-4 border-primary pl-6'));

const quoteTextClass = computed(() => ({
    'centered': 'text-center'
}[props.style] || ''));
</script>
