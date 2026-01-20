<template>
  <div class="progressbar-block" :style="wrapperStyles">
    <!-- Title above -->
    <div v-if="titlePosition === 'above'" class="progressbar-header">
      <span class="progressbar-title" :style="titleStyles">{{ titleValue }}</span>
      <span v-if="showPercentage" class="progressbar-percentage" :style="percentageStyles">{{ percentageValue }}%</span>
    </div>
    
    <!-- Bar -->
    <div class="progressbar-track" :style="trackStyles">
      <div class="progressbar-fill" :style="fillStyles">
        <span v-if="titlePosition === 'inside'" class="progressbar-inside-text">
          {{ titleValue }} {{ showPercentage ? percentageValue + '%' : '' }}
        </span>
      </div>
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
  getTransformStyles,
  getResponsiveValue
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

const percentageValue = computed(() => {
  const p = getResponsiveValue(settings.value, 'percentage', device.value) ?? 75
  return Math.min(100, Math.max(0, p))
})
const showPercentage = computed(() => getResponsiveValue(settings.value, 'showPercentage', device.value) !== false)
const titlePosition = computed(() => getResponsiveValue(settings.value, 'titlePosition', device.value) || 'above')
const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value) || 'Progress')

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

const trackStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) ?? 20
  const radius = getResponsiveValue(settings.value, 'borderRadius', device.value) ?? 10
  const trackColor = getResponsiveValue(settings.value, 'trackColor', device.value) || '#e0e0e0'
  return {
    backgroundColor: trackColor,
    height: `${height}px`,
    borderRadius: `${radius}px`,
    overflow: 'hidden'
  }
})

const fillStyles = computed(() => {
  const radius = getResponsiveValue(settings.value, 'borderRadius', device.value) ?? 10
  const barColor = getResponsiveValue(settings.value, 'barColor', device.value) || '#2059ea'
  return {
    backgroundColor: barColor,
    width: `${percentageValue.value}%`,
    height: '100%',
    borderRadius: `${radius}px`,
    transition: 'width 1s ease-out',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    paddingRight: '8px'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const percentageStyles = computed(() => getTypographyStyles(settings.value, 'percentage_', device.value))
</script>

<style scoped>
.progressbar-block { width: 100%; }
.progressbar-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
.progressbar-inside-text { color: white; font-size: 12px; font-weight: 600; white-space: nowrap; }
</style>
