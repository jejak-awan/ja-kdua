<template>
  <div class="logo-block" :style="wrapperStyles">
    <a v-if="settings.link" :href="settings.link" :target="settings.openInNewTab ? '_blank' : '_self'" class="logo-link">
      <img v-if="settings.image" :src="settings.image" :alt="settings.altText || 'Logo'" :style="imageStyles" />
      <div v-else class="logo-placeholder" :style="placeholderStyles"><ImageIcon class="placeholder-icon" /></div>
    </a>
    <template v-else>
      <img v-if="settings.image" :src="settings.image" :alt="settings.altText || 'Logo'" :style="imageStyles" />
      <div v-else class="logo-placeholder" :style="placeholderStyles"><ImageIcon class="placeholder-icon" /></div>
    </template>
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
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { textAlign: settings.value.alignment || 'left', width: '100%' }
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

const imageStyles = computed(() => {
  const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  const maxWidth = getResponsiveValue(settings.value, 'maxWidth', device.value) || 200
  return { 
    maxWidth: `${maxWidth}px`, 
    height: settings.value.height || 'auto', 
    display: 'block',
    margin: align === 'center' ? '0 auto' : align === 'right' ? '0 0 0 auto' : '0'
  }
})

const placeholderStyles = computed(() => {
  const maxWidth = getResponsiveValue(settings.value, 'maxWidth', device.value) || 200
  return { 
    width: `${maxWidth}px`, 
    height: '60px', 
    display: 'inline-flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    backgroundColor: '#f0f0f0', 
    borderRadius: '4px' 
  }
})
</script>

<style scoped>
.logo-block { width: 100%; }
.logo-link { display: inline-block; }
.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
</style>
