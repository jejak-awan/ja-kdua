<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="video-popup-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Video Popup'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <Card 
          class="video-popup-container relative group h-[500px] rounded-[48px] overflow-hidden cursor-pointer shadow-2xl border-none transition-colors duration-700 hover:-translate-y-2 bg-slate-900" 
          :style="containerStyles"
          @click="openPopup"
      >
        <!-- Thumbnail Layer -->
        <div 
            v-if="blockSettings.thumbnailImage" 
            class="thumbnail-layer absolute inset-0 bg-cover bg-center transition-transform duration-1000 group-hover:scale-110" 
            :style="{ backgroundImage: `url(${blockSettings.thumbnailImage})` }"
        ></div>
        <div v-else class="absolute inset-0 flex items-center justify-center text-slate-800">
             <Film :size="120" class="opacity-10" />
        </div>
        
        <!-- Overlay -->
        <div class="video-popup-overlay absolute inset-0 transition-colors duration-700 bg-slate-900/40 group-hover:bg-primary/20 backdrop-blur-[2px] group-hover:backdrop-blur-none" :style="overlayStyles" />
        
        <!-- Content Wrap -->
        <div class="video-content-wrap relative z-10 flex flex-col items-center justify-center h-full gap-8 p-12 text-center">
            <!-- Pulsing Play Button -->
            <div class="relative">
                <div class="absolute inset-0 bg-white/20 rounded-full animate-ping group-hover:bg-primary/40"></div>
                <Button 
                    class="video-popup-button relative w-24 h-24 rounded-full bg-white text-primary hover:bg-white hover:scale-110 shadow-[0_0_50px_rgba(255,255,255,0.3)] transition-[width] duration-500 flex items-center justify-center p-0 border-none"
                    :style="buttonStyles"
                >
                  <Play class="play-icon fill-current translate-x-1" :size="32" />
                </Button>
            </div>
            
            <div class="flex flex-col items-center">
                <h4 v-if="blockSettings.buttonText" class="button-text font-black text-3xl md:text-4xl text-white tracking-tighter drop-shadow-2xl mb-2 group-hover:text-primary transition-colors duration-500" :style="buttonTextStyles">
                    {{ blockSettings.buttonText || 'Experience the Vision' }}
                </h4>
                <div class="w-12 h-1.5 bg-white/30 rounded-full group-hover:bg-primary group-hover:w-24 transition-colors duration-700"></div>
            </div>
        </div>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button } from '../ui'
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import Film from 'lucide-vue-next/dist/esm/icons/film.js';import { 
  getTypographyStyles,
  getLayoutStyles,
  getVal,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const openPopup = () => {
    if (props.mode === 'edit') return
    const videoUrl = settings.value.videoUrl
    if (!videoUrl) return
    logger.info('Opening video popup:', videoUrl)
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
    const height = getResponsiveValue(settings.value, 'height', props.device) || 500
    
    return {
        ...layoutStyles,
        height: typeof height === 'number' ? `${height}px` : height
    }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || '' 
}))

const buttonStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', props.device) || 80
  const color = settings.value.iconBackgroundColor || ''
  return {
    width: `${size}px`,
    height: `${size}px`,
    backgroundColor: color
  }
})

const buttonTextStyles = computed(() => getTypographyStyles(settings.value, 'button_', props.device))
</script>

<style scoped>
.video-popup-block { width: 100%; }
.video-popup-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.video-popup-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
