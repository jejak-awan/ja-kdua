<script setup>
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    content: { type: String, default: '' },
    max_width: { type: String, default: 'max-w-4xl' },
    font_size: { type: String, default: 'text-base' },
    line_height: { type: String, default: 'leading-relaxed' },
    padding: { type: String, default: 'py-8' },
    alignment: { type: String, default: 'text-left' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const contentClasses = computed(() => {
    return [
        'prose prose-lg dark:prose-invert mx-auto',
        props.max_width,
        props.font_size,
        props.line_height,
        props.alignment
    ].filter(Boolean);
});

const demoContent = `
<p>This is a dynamic <strong>Post Content</strong> block. When used on a single post page, it will automatically display the post's body content.</p>
<p>The content is rendered with proper typography styling, including:</p>
<ul>
<li>Headings with proper hierarchy</li>
<li>Paragraphs with comfortable line height</li>
<li>Lists, quotes, and code blocks</li>
<li>Images with captions</li>
</ul>
<p>Configure the maximum width, font size, and line height to match your design.</p>
`;
</script>

<template>
    <div :class="containerClasses">
        <div 
            :class="contentClasses"
            v-html="content || demoContent"
        ></div>
    </div>
</template>
