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
          :stroke-width="normalizedThickness"
        />
        <!-- Progress Circle -->
        <circle
          class="circle-progress"
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          :stroke="color"
          :stroke-width="normalizedThickness"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="dashOffset"
          stroke-linecap="round"
        />
      </svg>
      
      <!-- Inner Content -->
      <div class="circle-content">
        <span v-if="showValue" class="circle-percentage" :style="valueStyles">
          {{ value }}%
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
const thickness = computed(() => getResponsiveValue(settings.value, 'thickness', device.value) || 10)
const value = computed(() => Math.min(100, Math.max(0, getResponsiveValue(settings.value, 'value', device.value) || 75)))
const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value))
const showValue = computed(() => getResponsiveValue(settings.value, 'showValue', device.value) !== false)

const normalizedThickness = computed(() => (thickness.value / size.value) * 100)
const radius = computed(() => 50 - normalizedThickness.value / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const dashOffset = computed(() => circumference.value * (1 - value.value / 100))

const color = computed(() => getResponsiveValue(settings.value, 'color', device.value) || '#2059ea')
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

const valueStyles = computed(() => getTypographyStyles(settings.value, 'value_', device.value))
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
