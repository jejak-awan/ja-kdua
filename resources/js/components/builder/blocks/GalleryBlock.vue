<template>
  <div class="gallery-block" :style="wrapperStyles">
    <div class="gallery-grid" :style="gridStyles">
      <div 
        v-for="(image, index) in galleryImages" 
        :key="index"
        class="gallery-item"
        :class="hoverClass"
        :style="itemStyles"
      >
        <div class="gallery-image-wrapper" :style="imageWrapperStyles">
          <img 
            :src="image.src" 
            :alt="image.alt || ''"
            class="gallery-image"
          />
          <div v-if="hoverEffect === 'overlay'" class="gallery-overlay" :style="overlayStyles">
            <ZoomIn class="overlay-icon" />
          </div>
        </div>
        <p v-if="showCaptions && image.caption && captionPosition === 'below'" class="gallery-caption" :style="captionStyles">
          {{ image.caption }}
        </p>
      </div>
      
      <!-- Placeholder when empty -->
      <template v-if="galleryImages.length === 0">
        <div v-for="i in 6" :key="i" class="gallery-item gallery-placeholder" :style="itemStyles">
          <div class="gallery-image-wrapper" :style="imageWrapperStyles">
            <ImageIcon class="placeholder-icon" />
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Image as ImageIcon, ZoomIn } from 'lucide-vue-next'
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
  module: {
    type: Object,
    required: true
  }
})

const settings = computed(() => props.module.settings || {})
const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const galleryImages = computed(() => {
  return (props.module.children || []).map(child => ({
    src: child.settings.src || '',
    alt: child.settings.alt || '',
    caption: child.settings.caption || ''
  }))
})

const hoverEffect = computed(() => getResponsiveValue(settings.value, 'hoverEffect', device.value) || 'zoom')
const showCaptions = computed(() => getResponsiveValue(settings.value, 'showCaptions', device.value))
const captionPosition = computed(() => getResponsiveValue(settings.value, 'captionPosition', device.value) || 'below')
const hoverClass = computed(() => `gallery-item--${hoverEffect.value}`)

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const gridStyles = computed(() => {
  const cols = getResponsiveValue(settings.value, 'columns', device.value) || 3
  const g = getResponsiveValue(settings.value, 'gap', device.value) || 16
  return {
    display: 'grid',
    gridTemplateColumns: `repeat(${cols}, 1fr)`,
    gap: `${g}px`
  }
})

const itemStyles = computed(() => {
  const styles = { overflow: 'hidden' }
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const imageWrapperStyles = computed(() => {
  const ratio = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '1:1'
  const ratioMap = {
    '1:1': '100%',
    '4:3': '75%',
    '16:9': '56.25%',
    'auto': 'auto'
  }
  return {
    position: 'relative',
    paddingTop: ratio === 'auto' ? undefined : ratioMap[ratio],
    overflow: 'hidden'
  }
})

const overlayStyles = computed(() => {
  const color = getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.5)'
  return {
    backgroundColor: color
  }
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', device.value))
</script>

<style scoped>
.gallery-block { width: 100%; }
.gallery-item { cursor: pointer; }
.gallery-image-wrapper { background: #f0f0f0; }
.gallery-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease, filter 0.3s ease; }
.gallery-item--zoom:hover .gallery-image { transform: scale(1.1); }
.gallery-item--grayscale .gallery-image { filter: grayscale(100%); }
.gallery-item--grayscale:hover .gallery-image { filter: grayscale(0%); }
.gallery-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.gallery-item--overlay:hover .gallery-overlay { opacity: 1; }
.overlay-icon { width: 32px; height: 32px; color: white; }
.gallery-caption { margin: 8px 0 0; }
.gallery-placeholder { display: flex; align-items: center; justify-content: center; background: #f5f5f5; min-height: 120px; }
.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
</style>
