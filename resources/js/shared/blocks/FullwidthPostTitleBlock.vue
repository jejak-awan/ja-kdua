<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    tag="header"
    class="fullwidth-post-title-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Post Title'"
    :style="cardStyles"
  >
    <div class="relative w-full h-full overflow-hidden flex items-center justify-center" :style="containerStyles">
      <!-- Background Layer -->
      <div v-if="settings.showFeaturedImage !== false" class="absolute inset-0 z-0 transition-transform duration-[2000ms] hover:scale-105" :style="featuredBgStyles" />
      
      <!-- Overlay Layer -->
      <div class="absolute inset-0 z-10 transition-opacity duration-700" :style="overlayStyles" />
      
      <!-- Content Layer -->
      <div class="relative z-20 w-full max-w-5xl px-10 py-20 flex flex-col transition-colors duration-700" :style="contentStyles">
        <!-- Post Meta -->
        <div v-if="settings.showMeta !== false" class="post-meta flex flex-wrap gap-4 mb-6 transition-colors duration-300 animate-in fade-in slide-in-from-bottom-4" :style="metaStyles">
          <span v-if="settings.showCategories !== false" class="meta-category bg-white/20 backdrop-blur-sm px-3 py-1 rounded-md text-sm font-medium">{{ postCategory }}</span>
          <span v-if="settings.showDate !== false" class="meta-date opacity-80 text-sm">{{ postDate }}</span>
          <span v-if="settings.showAuthor !== false" class="meta-author opacity-80 text-sm">by {{ postAuthor }}</span>
          <span v-if="settings.showCommentCount" class="meta-comments opacity-80 text-sm">{{ postComments }} Comments</span>
        </div>
        
        <!-- Post Title -->
        <component 
          :is="settings.tag || 'h1'" 
          class="post-title m-0 leading-tight tracking-tight animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('title', $event)"
        >
          {{ mode === 'edit' ? (settings.title || 'Amazing Post Title Goes Here') : postTitle }}
        </component>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
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

// Post data would normally come from context/prop
const postTitle = inject('postTitle', 'Amazing Post Title Goes Here')
const postCategory = inject('postCategory', 'Technology')
const postDate = inject('postDate', 'January 10, 2026')
const postAuthor = inject('postAuthor', 'John Doe')
const postComments = inject('postComments', 12)
const postFeaturedImage = inject('postFeaturedImage', '')

const updateText = (key: string, event: FocusEvent) => {
    if (props.mode !== 'edit' || !event.target || !builder) return
    const value = (event.target as HTMLElement).innerText
    builder.updateModuleSettings(props.module.id, { [key]: value })
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const height = getResponsiveValue(settings.value, 'height', props.device) || 400
    return { 
        ...layoutStyles,
        width: '100%', 
        minHeight: `${height}px`
    }
})

const featuredBgStyles = computed(() => ({
    backgroundImage: postFeaturedImage ? `url(${postFeaturedImage})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
}))

const overlayStyles = computed(() => {
    const opacity = (settings.value.overlayOpacity ?? 50) / 100
    const color = settings.value.overlayColor || 'rgba(0,0,0,0.5)'
    return {
        backgroundColor: color,
        opacity: opacity
    }
})

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'contentAlignment', props.device) || 'center'
  return {
    alignItems: alignment === 'left' ? 'flex-start' : alignment === 'right' ? 'flex-end' : 'center',
    textAlign: alignment as any,
    justifyContent: settings.value.contentPosition === 'top' ? 'flex-start' : 
                    settings.value.contentPosition === 'bottom' ? 'flex-end' : 'center'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))
</script>

<style scoped>
.fullwidth-post-title-block {
    width: 100%;
    position: relative;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-post-title-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.post-title {
    text-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>
