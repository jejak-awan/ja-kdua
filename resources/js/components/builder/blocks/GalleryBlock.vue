<template>
  <div class="gallery-block" :style="wrapperStyles">
    <div class="gallery-grid" :style="gridStyles">
      <div 
        v-for="(image, index) in galleryImages" 
        :key="index"
        class="gallery-item group relative aspect-square overflow-hidden rounded-2xl bg-muted/50 border border-primary/5 cursor-pointer shadow-lg transition-all duration-500 hover:shadow-2xl hover:border-primary/20"
        :style="itemStyles"
      >
        <div class="gallery-image-wrapper w-full h-full" :style="imageWrapperStyles">
            <img 
                v-if="image.url"
                :src="image.url" 
                :alt="image.alt || 'Gallery Image'"
                class="gallery-image w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            >
             <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400">
                <ImageIcon :size="48" />
             </div>

            <div v-if="showCaptions && image.caption && captionPosition === 'overlay'" class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-6 opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0 text-white">
                <p class="text-sm font-semibold tracking-wide" :style="captionStyles">{{ image.caption }}</p>
            </div>
        </div>
        <p v-if="showCaptions && image.caption && captionPosition === 'below'" class="mt-2 text-center text-sm" :style="captionStyles">{{ image.caption }}</p>
      </div>
      
      <!-- Placeholder when empty -->
      <template v-if="galleryImages.length === 0">
        <div v-for="i in 3" :key="i" class="gallery-item gallery-placeholder" :style="itemStyles">
          <div class="gallery-image-wrapper p-8" :style="imageWrapperStyles">
            <ImageIcon class="placeholder-icon text-gray-300" :size="32" />
            <span class="text-xs text-gray-400 mt-2">Add images in settings</span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Image as ImageIcon } from 'lucide-vue-next'
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
const device = computed(() => builder?.device?.value || 'desktop')

const galleryImages = computed(() => {
  const imgs = getResponsiveValue(settings.value, 'images', device.value)
  return Array.isArray(imgs) ? imgs : []
})

const showCaptions = computed(() => getResponsiveValue(settings.value, 'showCaptions', device.value))
const captionPosition = computed(() => getResponsiveValue(settings.value, 'captionPosition', device.value) || 'below')

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
    // position: 'relative', // Controlled by class
    // paddingTop is tricky if we want img to cover. 
    // Using aspect-ratio utility in CSS is better but we are using inline styles for dynamic ratio?
    // Let's rely on wrapper aspect ratio if possible, or fallback to padding hack.
    // If ratio is auto, no padding.
  }
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', device.value))
</script>

<style scoped>
.gallery-block { width: 100%; box-sizing: border-box; }
.gallery-placeholder { display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.05); border-radius: 8px; aspect-ratio: 1; }
</style>
