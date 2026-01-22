<template>
  <div class="number-counter-block" :style="containerStyles">
    <div class="counter-wrapper" :style="wrapperStyles">
      <div class="number-display">
        <span v-if="settings.prefix" class="number-prefix" :style="prefixStyles">{{ settings.prefix }}</span>
        <span class="number-value" :style="numberStyles">{{ displayNumber }}</span>
        <span v-if="settings.suffix" class="number-suffix" :style="suffixStyles">{{ settings.suffix || '%' }}</span>
      </div>
      <div class="counter-text">
        <h4 v-if="settings.title" class="counter-title" :style="titleStyles">{{ settings.title || 'Completion' }}</h4>
        <p v-if="settings.description" class="counter-description" :style="descriptionStyles">{{ settings.description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
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

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const animatedValue = ref(0)
const targetNumber = computed(() => parseFloat(getResponsiveValue(settings.value, 'number', device.value)) || 100)
const decimals = computed(() => getResponsiveValue(settings.value, 'decimals', device.value) || 0)
const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'vertical')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')

const displayNumber = computed(() => {
  let num = animatedValue.value
  const useSeparator = getResponsiveValue(settings.value, 'separator', device.value) !== false
  if (useSeparator) {
    return num.toLocaleString('en-US', { minimumFractionDigits: decimals.value, maximumFractionDigits: decimals.value })
  }
  return num.toFixed(decimals.value)
})

onMounted(() => {
  const duration = getResponsiveValue(settings.value, 'duration', device.value) || 2000
  const easing = getResponsiveValue(settings.value, 'easing', device.value) || 'easeOut'
  const start = performance.now()
  const animate = (now) => {
    const progress = Math.min((now - start) / duration, 1)
    const eased = easing === 'linear' ? progress : 
                  easing === 'easeIn' ? progress * progress :
                  easing === 'easeInOut' ? (progress < 0.5 ? 2 * progress * progress : 1 - Math.pow(-2 * progress + 2, 2) / 2) :
                  1 - Math.pow(1 - progress, 3) // easeOut default
    animatedValue.value = eased * targetNumber.value
    if (progress < 1) requestAnimationFrame(animate)
  }
  requestAnimationFrame(animate)
})

const containerStyles = computed(() => {
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

const wrapperStyles = computed(() => {
  return {
    display: 'flex',
    flexDirection: layout.value === 'horizontal' ? 'row' : 'column',
    gap: layout.value === 'horizontal' ? '24px' : '0',
    textAlign: alignment.value,
    alignItems: alignment.value === 'left' ? 'flex-start' : (alignment.value === 'right' ? 'flex-end' : 'center')
  }
})

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const prefixStyles = computed(() => {
  const base = getTypographyStyles(settings.value, 'number_', device.value)
  return { ...base, fontWeight: getResponsiveValue(settings.value, 'number_fontWeight', device.value) || '600' }
})
const suffixStyles = computed(() => {
  const base = getTypographyStyles(settings.value, 'number_', device.value)
  return { ...base, fontWeight: getResponsiveValue(settings.value, 'number_fontWeight', device.value) || '600' }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-counter-block { width: 100%; }
.number-display { display: flex; align-items: baseline; justify-content: inherit; }
.counter-title { margin: 0; }
.counter-description { margin: 8px 0 0; }
</style>
