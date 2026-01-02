<template>
  <div :class="containerClasses">
    <div :class="contentClasses">
      <!-- If post has builder blocks, render them -->
      <template v-if="postBlocks && postBlocks.length">
        <BlockRenderer :blocks="postBlocks" :context="context" />
      </template>

      <!-- Fallback to classic content (v-html) -->
      <div v-else v-html="displayContent"></div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';

// Lazy load BlockRenderer to avoid circular dependency loops
const BlockRenderer = defineAsyncComponent(() => import('./BlockRenderer.vue'));

const props = defineProps({
  config: {
    type: Object,
    default: () => ({
        max_width: 'max-w-4xl',
        font_size: 'text-base',
        line_height: 'leading-relaxed',
        padding: 'py-8',
        alignment: 'text-left'
    })
  },
  context: {
    type: Object,
    default: () => ({})
  }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.config?.padding || 'py-8'].filter(Boolean);
});

const contentClasses = computed(() => {
    const c = props.config || {};
    return [
        'prose prose-lg dark:prose-invert mx-auto',
        c.max_width || 'max-w-4xl',
        c.font_size || 'text-base',
        c.line_height || 'leading-relaxed',
        c.alignment || 'text-left'
    ].filter(Boolean);
});

const postBlocks = computed(() => {
    return props.context?.post?.blocks || null;
});

const displayContent = computed(() => {
    // 1. Get from context post
    if (props.context?.post?.body) return props.context.post.body;
    
    // 2. Demo content for builder
    if (props.context?.builderMode) {
        return `
            <p>This is a dynamic <strong>Post Content</strong> block. In live view, it displays the post body.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        `;
    }
    
    return '';
});
</script>
