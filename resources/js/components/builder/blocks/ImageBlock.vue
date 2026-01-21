<template>
  <div class="image-block" :style="wrapperStyles">
    <component
      :is="settings.linkUrl ? 'a' : 'div'"
      class="image-wrapper"
      :href="settings.linkUrl || undefined"
      :target="settings.linkUrl ? (settings.linkNewTab ? '_blank' : '_self') : undefined"
      :style="imageWrapperStyles"
    >
      <img 
        v-if="settings.url"
        :src="settings.url" 
        :alt="settings.alt || ''"
        :style="imageStyles"
        class="image-element"
      />
      <div v-else class="image-placeholder">
        <ImageIcon class="placeholder-icon" />
        <span>Click to add image</span>
      </div>
      
      <!-- Overlay -->
      <div v-if="overlayEnabled" class="image-overlay" :style="overlayStyles">
        <ZoomIn class="overlay-icon" />
      </div>
    </component>
    
    <!-- Caption -->
    <p v-if="settings.caption" class="image-caption" :style="captionStyles">{{ settings.caption }}</p>
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

const overlayEnabled = computed(() => getResponsiveValue(settings.value, 'overlayEnabled', device.value))

const wrapperStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  const styles = { 
    width: '100%',
    textAlign: alignment
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value, device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const imageWrapperStyles = computed(() => {
  const styles = {
    position: 'relative',
    display: 'inline-block',
    overflow: 'hidden',
    verticalAlign: 'top',
    textDecoration: 'none'
  }
  
  return styles
})

const imageStyles = computed(() => {
  const hoverFit = getResponsiveValue(settings.value, 'objectFit', device.value) || 'cover'
  return {
    display: 'block',
    width: '100%',
    height: getResponsiveValue(settings.value, 'height', device.value) || 'auto',
    objectFit: hoverFit
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
.image-block { width: 100%; }
.image-wrapper { line-height: 0; }
.image-element { transition: transform 0.3s ease; }
.image-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #f0f0f0; color: #999; padding: 40px; min-height: 150px; }
.placeholder-icon { width: 48px; height: 48px; opacity: 0.5; }
.image-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.image-wrapper:hover .image-overlay { opacity: 1; }
.overlay-icon { width: 32px; height: 32px; color: white; }
.image-caption { margin-top: 8px; }
</style>
