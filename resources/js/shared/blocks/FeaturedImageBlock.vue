<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="featured-image-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Featured Image'"
    :style="cardStyles"
  >
    <div class="image-wrapper relative w-full overflow-hidden" :style="containerStyles">
      <img 
        v-if="imageUrl" 
        :src="imageUrl" 
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
        :alt="(settings.alt_text as string) || 'Featured Image'"
        loading="lazy"
      />
      <div v-else-if="mode === 'edit'" class="flex items-center justify-center bg-slate-100 dark:bg-slate-900 w-full h-full text-slate-400 gap-3 min-h-[400px]">
        <ImageIcon class="w-8 h-8 opacity-40 text-primary" />
        <span class="font-bold tracking-tight">Featured Image Module</span>
      </div>
      
      <!-- Overlay -->
      <div 
        v-if="settings.overlayEnabled" 
        class="absolute inset-0 z-10 pointer-events-none" 
        :style="{ backgroundColor: (settings.overlayColor as string) || 'rgba(0,0,0,0.3)' }"
      ></div>

      <!-- Caption -->
      <div 
        v-if="settings.caption" 
        class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 text-white font-bold text-center px-6 py-3 bg-black/40 backdrop-blur-md rounded-full shadow-2xl animate-in fade-in slide-in-from-bottom-4 duration-500"
      >
        {{ settings.caption }}
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import { 
    getVal, 
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)
const imageUrl = computed(() => getVal<string>(settings.value, 'imageUrl', props.device))

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        height: `${getVal<number>(settings.value, 'height', props.device) || 600}px`
    } as CSSProperties
})
</script>

<style scoped>
.featured-image-block { width: 100%; position: relative; }
</style>
