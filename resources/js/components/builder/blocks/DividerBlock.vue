<template>
  <div class="divider-block" :style="wrapperStyles">
    <hr v-if="settings.visible !== false" class="divider-line" :style="lineStyles" />
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
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')

const wrapperStyles = computed(() => {
  const styles = {
    width: '100%',
    display: 'flex'
  }
  
  const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  if (align === 'center') {
    styles.justifyContent = 'center'
  } else if (align === 'right') {
    styles.justifyContent = 'flex-end'
  } else {
    styles.justifyContent = 'flex-start'
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

const lineStyles = computed(() => {
  const weight = getResponsiveValue(settings.value, 'weight', device.value) || 1
  const width = getResponsiveValue(settings.value, 'width', device.value) || '100%'
  const style = getResponsiveValue(settings.value, 'style', device.value) || 'solid'
  const color = getResponsiveValue(settings.value, 'color', device.value) || '#cccccc'
  
  return {
    border: 'none',
    borderTop: `${weight}px ${style} ${color}`,
    width: width,
    margin: 0
  }
})
</script>

<style scoped>
.divider-block { box-sizing: border-box; }
.divider-line { flex-shrink: 0; }
</style>
