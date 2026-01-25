<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-map-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Map'"
    :style="cardStyles"
  >
    <div class="map-wrapper relative w-full overflow-hidden" :style="containerStyles">
      <div v-if="mode === 'edit'" class="builder-placeholder h-full bg-slate-50 dark:bg-slate-900 flex flex-col items-center justify-center p-12 text-center">
          <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mb-6 shadow-2xl">
              <MapPin class="w-10 h-10 text-primary animate-bounce" />
          </div>
          <h3 class="text-xl font-black tracking-tighter mb-3 uppercase">{{ settings.address || 'Global Hub' }}</h3>
          <p class="text-xs font-black tracking-widest text-slate-400 dark:text-slate-500 uppercase opacity-60">Interactive Map Preview</p>
          <div class="mt-8 flex gap-4 opacity-50">
              <div class="bg-slate-200 dark:bg-slate-800 h-10 w-32 rounded-full"></div>
              <div class="bg-slate-200 dark:bg-slate-800 h-10 w-10 rounded-full"></div>
          </div>
      </div>
      <div v-else class="map-container h-full" :class="{ 'grayscale': settings.grayscale }">
          <iframe 
            width="100%" 
            height="100%" 
            frameborder="0" 
            scrolling="no" 
            marginheight="0" 
            marginwidth="0" 
            :src="mapUrl"
          ></iframe>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { MapPin } from 'lucide-vue-next'
import { 
    getVal,
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

const mapUrl = computed(() => {
  const address = encodeURIComponent(settings.value.address || 'New York, NY')
  const zoom = settings.value.zoom || 14
  return `https://maps.google.com/maps?q=${address}&t=&z=${zoom}&ie=UTF8&iwloc=&output=embed`
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
    const height = getResponsiveValue(settings.value, 'height', props.device) || 500
    return { 
        ...layout,
        width: '100%', 
        height: `${height}px` 
    }
})
</script>

<style scoped>
.fullwidth-map-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-map-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.map-container.grayscale iframe {
    filter: grayscale(1) invert(0.1) contrast(1.1);
}
</style>
