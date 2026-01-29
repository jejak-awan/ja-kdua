<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="map-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Interactive Map'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="map-container relative overflow-hidden rounded-[24px]" 
        :style="containerStyles"
      >
        <iframe
          :src="mapUrl"
          class="map-iframe w-full h-full border-0"
          :class="{ 'map-iframe--grayscale': blockSettings.grayscale }"
          frameborder="0"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        />
        
        <!-- Main Marker -->
        <div v-if="blockSettings.showMarker !== false" class="map-marker-label absolute top-4 left-4 z-10 bg-white dark:bg-slate-900 p-2 px-3 rounded-lg shadow-lg flex items-center gap-2 font-bold text-sm">
          <MapPin class="marker-icon w-4 h-4 text-primary" />
          <span>{{ blockSettings.markerTitle || 'Our Location' }}</span>
        </div>

        <!-- Child Pins (In Renderer Mode) -->
        <template v-if="mode === 'view'">
          <div v-for="(pin, index) in childPins" :key="index" class="map-marker-label absolute z-10 bg-white dark:bg-slate-900 p-2 px-4 rounded-lg shadow-lg flex items-center gap-2 font-bold text-sm" :style="{ top: `${80 + index * 50}px`, left: '16px' }">
             <MapPin class="marker-icon w-4 h-4 text-primary" />
             <span>{{ pin.title || 'Pin' }}</span>
          </div>
        </template>

        <!-- Children Slot (In Builder Mode) -->
        <slot v-else-if="mode === 'edit'"></slot>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal, 
    getLayoutStyles,
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

const mapUrl = computed(() => {
  const address = encodeURIComponent(settings.value.address || 'New York, NY')
  const zoom = getVal(settings.value, 'zoom', props.device) || 14
  const mapType = settings.value.mapType === 'satellite' ? 'k' : (settings.value.mapType === 'terrain' ? 'p' : 'm')
  
  if (settings.value.embedUrl) return settings.value.embedUrl
  
  return `https://maps.google.com/maps?q=${address}&z=${zoom}&t=${mapType}&output=embed`
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
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const height = getResponsiveValue(settings.value, 'height', props.device) || 400
    
    return {
        ...layoutStyles,
        height: typeof height === 'number' ? `${height}px` : height,
        backgroundColor: '#f8fafc'
    }
})

const childPins = computed(() => {
  return (props.module.children || []).map(child => ({
    title: child.settings?.title,
    address: child.settings?.address
  }))
})
</script>

<style scoped>
.map-block { width: 100%; }
.map-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.map-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.map-iframe--grayscale {
    filter: grayscale(1);
    transition: filter 0.5s ease;
}
.map-block:hover .map-iframe--grayscale {
    filter: grayscale(0.5);
}
</style>
