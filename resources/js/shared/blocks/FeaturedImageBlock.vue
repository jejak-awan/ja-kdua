<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    tag="figure" 
    class="featured-image-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Featured Image'"
    :style="cardStyles"
  >
    <div 
        class="image-container relative overflow-hidden rounded-[2.5rem] shadow-2xl transition-colors duration-700 group" 
        :style="containerStyles"
    >
      <img 
        v-if="postFeaturedImage" 
        :src="postFeaturedImage" 
        :alt="settings.caption || 'Featured Image'" 
        class="featured-image object-cover w-full h-full transition-transform duration-1000 group-hover:scale-110" 
        :style="{ objectFit: getVal(settings.value, 'objectFit', device) || 'cover' }"
      />
      <div v-else class="absolute inset-0 bg-slate-50 dark:bg-slate-900 flex flex-col items-center justify-center gap-4 text-slate-300">
          <ImageIcon class="w-16 h-16 opacity-20" />
          <span class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">Creative Asset Pending</span>
      </div>
    </div>
    
    <figcaption 
      v-if="settings.showCaption !== false && (settings.caption || mode === 'edit')" 
      class="image-caption mt-6 font-medium text-center italic text-slate-500 outline-none" 
      :style="captionStyles"
      :contenteditable="mode === 'edit'"
      @blur="updateCaption"
    >
      {{ settings.caption || (mode === 'edit' ? 'Capture the essence of your story...' : '') }}
    </figcaption>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';import { 
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

// Dynamic data injection
const postFeaturedImage = inject<string | null>('postFeaturedImage', null)

const aspectRatios: Record<string, string> = { 
    '16:9': '56.25%', 
    '4:3': '75%', 
    '3:2': '66.67%', 
    '1:1': '100%', 
    'original': '0%' 
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
  const layout = getLayoutStyles(settings.value, props.device)
  const ratio = getVal(settings.value, 'aspectRatio', props.device) || '16:9'
  
  return {
    ...layout,
    paddingTop: ratio === 'original' ? '0' : (aspectRatios[ratio] || '56.25%'),
    height: ratio === 'original' ? 'auto' : '0'
  }
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', props.device))

const updateCaption = (event: FocusEvent) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { caption: (event.target as HTMLElement).innerText })
}
</script>

<style scoped>
.featured-image-block { 
    width: 100%; 
    margin: 0;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.featured-image-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.featured-image { position: absolute; inset: 0; }
.image-caption { word-wrap: break-word; }
</style>
