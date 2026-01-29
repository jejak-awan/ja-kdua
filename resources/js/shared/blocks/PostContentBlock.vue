<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="post-content-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Post Content'"
    :style="cardStyles"
  >
    <div 
        class="post-content-container w-full" 
        :style="containerStyles"
    >
      <div 
        v-if="mode === 'edit'" 
        class="builder-content-container"
      >
          <div v-if="!settings.content && !postContent" class="opacity-30 italic font-medium p-8 bg-slate-50 dark:bg-slate-900 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 text-center">
              Post content will appear here in the live article.
          </div>
          <div v-else class="post-content-inner prose prose-slate dark:prose-invert max-w-none" v-html="displayContent" />
      </div>
      <div v-else class="post-content-inner prose prose-slate dark:prose-invert max-w-none" v-html="postContent" />
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// Dynamic content injection
const postContent = inject<string>('postContent', '')

const displayContent = computed(() => {
    return settings.value.content || postContent || '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>'
})

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, props.device)
  const typo = getTypographyStyles(settings.value, '', props.device)
  
  const linkColors = getTypographyStyles(settings.value, 'link_', props.device)
  
  return {
    ...layout,
    ...typo,
    '--link-color': linkColors.color || 'var(--primary)',
    '--link-font-weight': linkColors.fontWeight || '700',
    '--link-decoration': linkColors.textDecoration || 'underline'
  } as any
})
</script>

<style scoped>
.post-content-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.post-content-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.post-content-inner:deep(p) { margin-bottom: 2rem; line-height: 1.8; }
.post-content-inner:deep(h2), .post-content-inner:deep(h3) { margin-top: 3rem; margin-bottom: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
.post-content-inner:deep(a) { 
  color: var(--link-color); 
  font-weight: var(--link-font-weight); 
  text-decoration: var(--link-decoration); 
}
.post-content-inner:deep(blockquote) { 
    margin: 3rem 0; 
    padding: 2rem 2.5rem; 
    border-left: 6px solid var(--primary); 
    background: rgba(var(--primary-rgb), 0.03);
    border-radius: 0 1.5rem 1.5rem 0;
    font-style: italic; 
    font-weight: 500;
}
</style>
