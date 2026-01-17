<template>
  <component 
    :is="tag"
    class="heading-block"
    :style="headingStyles"
    :class="cssClass"
  >
    {{ text }}
  </component>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getResponsiveValue, 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles 
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})
const device = computed(() => builder?.device || 'desktop')

const text = computed(() => settings.value.text || 'Your Heading Here')
const tag = computed(() => settings.value.tag || 'h2')

const headingStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  // Custom Typography mapping for Heading
  const typography = getTypographyStyles(settings.value, '', device.value)
  if (settings.value.alignment) {
    typography.textAlign = getResponsiveValue(settings.value, 'alignment', device.value)
  }
  Object.assign(styles, typography)
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')
</script>

<style scoped>
.heading-block {
  margin: 0;
  padding: 0;
}
</style>
