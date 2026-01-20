<template>
  <div class="number-box-block" :class="`number-box-block--${settings.layout || 'horizontal'}`" :style="wrapperStyles">
    <div class="number-box-number" :style="numberStyles">{{ settings.number || '01' }}</div>
    <div class="number-box-content">
      <h4 class="number-box-title" :style="titleStyles">{{ settings.title || 'Title' }}</h4>
      <p v-if="settings.description" class="number-box-description" :style="descriptionStyles">{{ settings.description }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

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

const numberStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'number_', device.value)
  const size = settings.value.number_fontSize ? parseInt(settings.value.number_fontSize) : 48
  return {
    ...styles,
    width: `${size * 1.5}px`,
    height: `${size * 1.5}px`,
    backgroundColor: settings.value.number_backgroundColor || 'rgba(32, 89, 234, 0.1)',
    borderRadius: '8px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-box-block { width: 100%; }
.number-box-block--horizontal { display: flex; align-items: center; gap: 20px; }
.number-box-block--vertical { display: flex; flex-direction: column; align-items: center; gap: 16px; text-align: center; }
.number-box-title { margin: 0 0 4px; }
.number-box-description { margin: 0; opacity: 0.8; }
</style>
