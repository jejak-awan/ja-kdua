<template>
  <div class="circle-counter-block" :style="wrapperStyles">
    <div class="circle-counter" :style="counterStyles">
      <svg :width="size" :height="size" viewBox="0 0 100 100">
        <!-- Background Circle -->
        <circle
          class="circle-track"
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          :stroke="trackColor"
          :stroke-width="normalizedStrokeWidth"
        />
        <!-- Progress Circle -->
        <circle
          class="circle-progress"
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          :stroke="circleColor"
          :stroke-width="normalizedStrokeWidth"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="dashOffset"
          stroke-linecap="round"
        />
      </svg>
      
      <!-- Inner Content -->
      <div class="circle-content">
        <span v-if="showPercentageValue" class="circle-percentage" :style="percentageStyles">
          {{ percentageValue }}%
        </span>
        <span v-if="titleValue" class="circle-title" :style="titleStyles">
          {{ titleValue }}
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
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const size = computed(() => getResponsiveValue(settings.value, 'size', device.value) || 150)
const strokeWidth = computed(() => getResponsiveValue(settings.value, 'strokeWidth', device.value) || 10)
const percentageValue = computed(() => Math.min(100, Math.max(0, getResponsiveValue(settings.value, 'percentage', device.value) || 75)))
const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value))
const showPercentageValue = computed(() => getResponsiveValue(settings.value, 'showPercentage', device.value) !== false)

const normalizedStrokeWidth = computed(() => (strokeWidth.value / size.value) * 100)
const radius = computed(() => 50 - normalizedStrokeWidth.value / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const dashOffset = computed(() => circumference.value * (1 - percentageValue.value / 100))

const circleColor = computed(() => getResponsiveValue(settings.value, 'circleColor', device.value) || '#2059ea')
const trackColor = computed(() => getResponsiveValue(settings.value, 'trackColor', device.value) || '#e0e0e0')

const wrapperStyles = computed(() => {
  const styles = { textAlign: 'center', width: '100%' }
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

const counterStyles = computed(() => ({
  display: 'inline-block',
  position: 'relative',
  width: `${size.value}px`,
  height: `${size.value}px`
}))

const percentageStyles = computed(() => getTypographyStyles(settings.value, 'percentage_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.circle-counter-block { width: 100%; }
.circle-counter svg { transform: rotate(-90deg); }
.circle-progress { transition: stroke-dashoffset 1s ease-out; }
.circle-content { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.circle-percentage { line-height: 1; }
.circle-title { margin-top: 4px; }
</style>
