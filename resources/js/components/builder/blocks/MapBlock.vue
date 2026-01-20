<template>
  <div class="map-block" :style="wrapperStyles">
    <div class="map-container" :style="containerStyles">
      <iframe
        :src="mapUrl"
        class="map-iframe"
        :class="{ 'map-iframe--grayscale': settings.grayscale }"
        frameborder="0"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      />
      
      <!-- Main Marker -->
      <div v-if="settings.showMarker !== false" class="map-marker-label">
        <MapPin class="marker-icon" />
        <span>{{ settings.markerTitle || 'Our Location' }}</span>
      </div>

      <!-- Child Pins -->
      <div v-for="(pin, index) in childPins" :key="index" class="map-marker-label" :style="{ top: `${50 + index * 40}px`, left: '20px' }">
         <MapPin class="marker-icon" />
         <span>{{ pin.title || 'Pin' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { MapPin } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue 
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const mapUrl = computed(() => {
  const address = encodeURIComponent(settings.value.address || 'New York, NY')
  const zoom = getResponsiveValue(settings.value, 'zoom', device.value) || 14
  return `https://maps.google.com/maps?q=${address}&z=${zoom}&output=embed`
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 400
  return {
    height: typeof height === 'number' ? `${height}px` : height,
    overflow: 'hidden',
    position: 'relative'
  }
})

const childPins = computed(() => {
  return (props.module.children || []).map(child => ({
    title: child.settings.title,
    address: child.settings.address
  }))
})
</script>

<style scoped>
.map-block {
  width: 100%;
}

.map-container {
  position: relative;
}

.map-iframe {
  width: 100%;
  height: 100%;
  border: 0;
}

.map-iframe--grayscale {
  filter: grayscale(100%);
}

.map-marker-label {
  position: absolute;
  top: 16px;
  left: 16px;
  background: white;
  padding: 6px 12px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  font-weight: 500;
  font-size: 13px;
  z-index: 10;
}

.marker-icon { width: 16px; height: 16px; color: #e53935; }
</style>
