<template>
  <figure class="featured-image-block" :style="wrapperStyles">
    <div class="image-container" :style="containerStyles">
      <ImageIcon class="placeholder-icon" />
    </div>
    <figcaption v-if="settings.showCaption" class="image-caption" :style="captionStyles">Featured image caption</figcaption>
  </figure>
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

const props = defineProps({ module: { type: Object, required: true } })
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const aspectRatios = { '16:9': '56.25%', '4:3': '75%', '3:2': '66.67%', '1:1': '100%', 'original': 'auto' }

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const containerStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    paddingTop: aspectRatios[settings.value.aspectRatio] || '56.25%',
    backgroundColor: '#f0f0f0', 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    overflow: 'hidden' 
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', device.value))
</script>

<style scoped>
.featured-image-block { width: 100%; }
.placeholder-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 48px; height: 48px; color: #ccc; }
.image-caption { margin-top: 8px; }
</style>
