<template>
  <figure class="fullwidth-image-block" :style="wrapperStyles">
    <component :is="settings.link ? 'a' : 'div'" :href="settings.link || null" :target="settings.target || '_self'" class="image-container">
      <ImageIcon v-if="!settings.image" class="placeholder-icon" />
      <img v-else :src="settings.image" :alt="settings.alt" :style="imageStyles" />
      <div v-if="settings.showOverlay" class="image-overlay" :style="overlayStyles">
        <span v-if="settings.overlayText" class="overlay-text" :style="textStyles">{{ settings.overlayText }}</span>
      </div>
    </component>
    <figcaption v-if="settings.caption" class="image-caption" :style="captionStyles">{{ settings.caption }}</figcaption>
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
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { 
    margin: 0, 
    position: 'relative', 
    overflow: 'hidden', 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 500}px`
  }
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

const imageStyles = computed(() => ({ 
  width: '100%', 
  height: '100%', 
  objectFit: settings.value.objectFit || 'cover' 
}))

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)', 
  display: 'flex', 
  alignItems: 'center', 
  justifyContent: 'center' 
}))

const textStyles = computed(() => getTypographyStyles(settings.value, 'overlay_', device.value))
const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', device.value))
</script>

<style scoped>
.fullwidth-image-block { width: 100%; }
.image-container { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; position: relative; }
.placeholder-icon { width: 64px; height: 64px; color: #ccc; }
.image-caption { padding: 12px; }
</style>
