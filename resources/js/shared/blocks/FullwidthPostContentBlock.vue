<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    tag="article"
    class="fullwidth-post-content-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Fullwidth Post Content'"
    :style="cardStyles"
  >
    <div class="content-inner mx-auto px-6 transition-[width] duration-500" :style="innerStyles">
      <div v-if="mode === 'edit'" class="edit-placeholder opacity-70">
          <p :style="paragraphStyles">
            This is where the post content will be rendered. In the builder, we show this placeholder. 
            The actual post content is dynamically loaded in the live view.
          </p>
          <h2 :style="headingStyles">Section Heading</h2>
          <p :style="paragraphStyles">
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
          </p>
          <blockquote class="italic p-5 my-6 border-l-4" :style="blockquoteStyles">
            "Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit."
          </blockquote>
      </div>
      <div v-else class="post-content-render" v-html="(postContent as string)" />
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const postContent = inject<string>('postContent', '<p>Post content not found.</p>')

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const innerStyles = computed((): CSSProperties => {
  const layoutStyles = getLayoutStyles(settings.value, props.device)
  const maxWidth = getVal<number>(settings.value, 'maxWidth', props.device) || 1200
  return {
    ...layoutStyles,
    maxWidth: settings.value.contentWidth === 'boxed' ? `${maxWidth}px` : '100%',
    margin: '0 auto',
    width: '100%'
  } as CSSProperties
})

const paragraphStyles = computed((): CSSProperties => {
  const styles = getTypographyStyles(settings.value, 'text_', props.device)
  styles.marginBottom = `${(settings.value.paragraphSpacing as number) || 24}px`
  return styles as CSSProperties
})

const headingStyles = computed((): CSSProperties => {
  const styles = getTypographyStyles(settings.value, 'heading_', props.device)
  styles.marginTop = `${(settings.value.headingSpacing as number) || 32}px`
  styles.marginBottom = '16px'
  return styles as CSSProperties
})

const linkStyles = computed(() => getTypographyStyles(settings.value, 'link_', props.device) as CSSProperties)

const blockquoteStyles = computed((): CSSProperties => ({
  borderLeftColor: (settings.value.blockquoteBorderColor as string) || '#2059ea',
  backgroundColor: (settings.value.blockquoteBackgroundColor as string) || '#f8f9fa',
} as CSSProperties))
</script>

<style scoped>
.fullwidth-post-content-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-post-content-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.content-inner :deep(a) { 
  color: v-bind('linkStyles.color || "#2059ea"'); 
  text-decoration: v-bind('linkStyles.textDecoration || "underline"');
}
.content-inner :deep(a:hover) { opacity: 0.8; }
.content-inner :deep(img) { 
  max-width: 100%; 
  height: auto; 
  border-radius: v-bind('(settings.imageBorderRadius || 8) + "px"');
  transition: transform 0.3s ease;
}
.content-inner :deep(img:hover) { transform: scale(1.02); }
</style>
