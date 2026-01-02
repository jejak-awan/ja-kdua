<script setup>
import { computed } from 'vue';
import { Calendar, User, Tag, Clock } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    show_date: { type: Boolean, default: true },
    show_author: { type: Boolean, default: true },
    show_category: { type: Boolean, default: true },
    show_reading_time: { type: Boolean, default: false },
    date: { type: String, default: '' },
    author: { type: String, default: '' },
    category: { type: String, default: '' },
    reading_time: { type: String, default: '' },
    title_size: { type: String, default: 'text-4xl md:text-5xl' },
    alignment: { type: String, default: 'text-center' },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding, props.alignment].filter(Boolean);
});

const demoDate = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
});
</script>

<template>
    <header 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6">
            <!-- Meta -->
            <div class="flex flex-wrap items-center justify-center gap-4 mb-6 text-sm text-muted-foreground">
                <span v-if="show_category" class="flex items-center gap-1.5">
                    <Tag class="w-4 h-4" />
                    {{ category || 'Category' }}
                </span>
                <span v-if="show_date" class="flex items-center gap-1.5">
                    <Calendar class="w-4 h-4" />
                    {{ date || demoDate }}
                </span>
                <span v-if="show_author" class="flex items-center gap-1.5">
                    <User class="w-4 h-4" />
                    {{ author || 'Author Name' }}
                </span>
                <span v-if="show_reading_time" class="flex items-center gap-1.5">
                    <Clock class="w-4 h-4" />
                    {{ reading_time || '5 min read' }}
                </span>
            </div>
            
            <!-- Title -->
            <h1 :class="['font-extrabold tracking-tight max-w-4xl mx-auto', title_size]">
                {{ title || 'Dynamic Post Title' }}
            </h1>
        </div>
    </header>
</template>
