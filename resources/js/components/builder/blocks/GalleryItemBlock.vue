<template>
  <div 
    class="gallery-item-block"
    :class="hoverClass"
    :style="itemStyles"
  >
    <div class="gallery-image-wrapper" :style="imageWrapperStyles">
      <img 
        :src="settings.src || ''" 
        :alt="settings.alt || ''"
        class="gallery-image"
      />
      <div v-if="hoverEffect === 'overlay'" class="gallery-overlay" :style="overlayStyles">
        <ZoomIn class="overlay-icon" />
      </div>
    </div>
    <p v-if="showCaptions && settings.caption && captionPosition === 'below'" class="gallery-caption" :style="captionStyles">
      {{ settings.caption }}
    </p>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { ZoomIn, Image as ImageIcon } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  index: { type: Number, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

// Injected from GalleryBlock
const galleryState = inject('galleryState', {
    parentSettings: {}
})
const parentSettings = computed(() => galleryState.parentSettings.value || {})

const hoverEffect = computed(() => getResponsiveValue(parentSettings.value, 'hoverEffect', device.value) || 'zoom')
const showCaptions = computed(() => getResponsiveValue(parentSettings.value, 'showCaptions', device.value))
const captionPosition = computed(() => getResponsiveValue(parentSettings.value, 'captionPosition', device.value) || 'below')
const hoverClass = computed(() => `gallery-item--${hoverEffect.value}`)

const itemStyles = computed(() => {
  const styles = { overflow: 'hidden', cursor: 'pointer' }
  Object.assign(styles, getBorderStyles(parentSettings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(parentSettings.value, 'boxShadow', device.value))
  return styles
})

const imageWrapperStyles = computed(() => {
  const ratio = getResponsiveValue(parentSettings.value, 'aspectRatio', device.value) || '1:1'
  const ratioMap = {
    '1:1': '100%',
    '4:3': '75%',
    '16:9': '56.25%',
    'auto': 'auto'
  }
  
  // If auto and no image, create a square placeholder
  if (ratio === 'auto' && !settings.value.src) {
      return {
          position: 'relative',
          paddingTop: '100%',
          overflow: 'hidden',
          backgroundColor: '#f0f0f0'
      }
  }

  return {
    position: 'relative',
    paddingTop: ratio === 'auto' ? undefined : ratioMap[ratio],
    overflow: 'hidden',
    backgroundColor: '#f0f0f0'
  }
})

const overlayStyles = computed(() => {
  const color = getResponsiveValue(parentSettings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.5)'
  return {
    backgroundColor: color
  }
})

const captionStyles = computed(() => getTypographyStyles(parentSettings.value, 'caption_', device.value))
</script>

<style scoped>
.gallery-item-block { width: 100%; height: 100%; position: relative; }
.gallery-image-wrapper { background: #f0f0f0; width: 100%; }
.gallery-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease, filter 0.3s ease; }

.gallery-item--zoom:hover .gallery-image { transform: scale(1.1); }
.gallery-item--grayscale .gallery-image { filter: grayscale(100%); }
.gallery-item--grayscale:hover .gallery-image { filter: grayscale(0%); }

.gallery-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; z-index: 2; }
.gallery-item--overlay:hover .gallery-overlay { opacity: 1; }
.overlay-icon { width: 32px; height: 32px; color: white; }
.gallery-caption { margin: 8px 0 0; }
</style>
