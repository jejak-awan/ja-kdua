<template>
  <div class="feature-block" :class="`feature-block--${layout}`" :style="wrapperStyles">
    <div class="feature-icon" :style="iconWrapperStyles">
      <component :is="iconComponent" :style="iconStyles" />
    </div>
    <div class="feature-content">
      <h4 class="feature-title" :style="titleStyles">{{ settings.title || 'Feature Title' }}</h4>
      <p class="feature-description" :style="descriptionStyles">{{ settings.description || 'Description goes here.' }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, inject } from 'vue'
import { Zap } from 'lucide-vue-next'
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
const device = computed(() => builder?.device?.value || 'desktop')

const iconComponent = computed(() => {
  const iconName = settings.value.icon || 'Zap'
  try {
    return defineAsyncComponent(() => import('lucide-vue-next').then(m => m[iconName] || Zap))
  } catch { return Zap }
})

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'top')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')

const wrapperStyles = computed(() => {
  const styles = { 
    width: '100%', 
    display: 'flex',
    gap: layout.value === 'top' ? '16px' : '20px',
    flexDirection: layout.value === 'top' ? 'column' : (layout.value === 'right' ? 'row-reverse' : 'row'),
    alignItems: layout.value === 'top' ? 'center' : 'flex-start',
    textAlign: alignment.value
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

const iconWrapperStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 48
  const bgColor = getResponsiveValue(settings.value, 'iconBackgroundColor', device.value) || 'rgba(32, 89, 234, 0.1)'
  const borderRadius = getResponsiveValue(settings.value, 'iconBorderRadius', device.value) || 50
  
  return {
    width: `${size + 24}px`, 
    height: `${size + 24}px`,
    backgroundColor: bgColor,
    borderRadius: `${borderRadius}%`,
    display: 'inline-flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    flexShrink: 0
  }
})

const iconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 48
  const color = getResponsiveValue(settings.value, 'iconColor', device.value) || '#2059ea'
  return { 
    width: `${size}px`, 
    height: `${size}px`, 
    color: color
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.feature-block { width: 100%; }
.feature-content { flex: 1; }
</style>
