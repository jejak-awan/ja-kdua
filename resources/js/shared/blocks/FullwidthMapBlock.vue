<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-map-block" :style="wrapperStyles">
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
      <div 
        v-if="settings.showMarker !== false || mode === 'edit'" 
        class="map-marker-label"
      >
        <MapPin class="marker-icon" />
        <span 
          :contenteditable="mode === 'edit'"
          @blur="updateText('markerTitle', $event)"
        >{{ settings.markerTitle || 'Our Location' }}</span>
      </div>
      
      <!-- Child Pins -->
      <div 
        v-for="(pin, index) in childPins" 
        :key="index" 
        class="map-marker-label" 
        :style="{ top: `${60 + index * 50}px`, left: '16px' }"
      >
         <MapPin class="marker-icon" />
         <span>{{ pin.title || 'Pin' }}</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { MapPin } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const mapUrl = computed(() => {
  const address = encodeURIComponent(settings.value.address || 'New York, NY')
  const zoom = settings.value.zoom || 14
  return `https://maps.google.com/maps?q=${address}&z=${zoom}&output=embed`
})

const wrapperStyles = computed(() => {
  return { width: '100%' }
})

const childPins = computed(() => {
  return (props.module.children || []).map(child => ({
    title: child.settings?.title,
    address: child.settings?.address
  }))
})

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 500
  return {
    height: `${height}px`,
    overflow: 'hidden',
    position: 'relative'
  }
})
</script>

<style scoped>
.fullwidth-map-block { width: 100%; }
.map-container { position: relative; }
.map-iframe { width: 100%; height: 100%; border: 0; }
.map-iframe--grayscale { filter: grayscale(100%); }
.map-marker-label {
  position: absolute;
  top: 16px;
  left: 16px;
  background: white;
  padding: 8px 16px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  font-weight: 500;
  z-index: 10;
  color: #333;
}
.marker-icon { width: 18px; height: 18px; color: #e53935; }
[contenteditable]:focus {
  outline: none;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
