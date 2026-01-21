<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="map-block" :style="mapBlockStyles">
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

          <!-- Child Pins (In Renderer Mode) -->
          <template v-if="mode === 'view'">
            <div v-for="(pin, index) in childPins" :key="index" class="map-marker-label" :style="{ top: `${50 + index * 40}px`, left: '20px' }">
               <MapPin class="marker-icon" />
               <span>{{ pin.title || 'Pin' }}</span>
            </div>
          </template>

          <!-- Children Slot (In Builder Mode) -->
          <slot v-else-if="mode === 'edit'"></slot>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import { MapPin } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, toCSS } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const mapUrl = computed(() => {
  const address = encodeURIComponent(settings.value.address || 'New York, NY')
  const zoom = getVal(settings.value, 'zoom', props.device) || 14
  return `https://maps.google.com/maps?q=${address}&z=${zoom}&output=embed`
})

const mapBlockStyles = computed(() => {
  return {
    width: '100%'
  }
})

const containerStyles = computed(() => {
  const height = getVal(settings.value, 'height', props.device) || 400
  return {
    height: toCSS(height),
    overflow: 'hidden',
    position: 'relative',
    backgroundColor: '#f0f0f0'
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
