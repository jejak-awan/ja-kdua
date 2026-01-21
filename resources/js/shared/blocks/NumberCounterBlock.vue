<template>
  <BaseBlock :module="module" :settings="settings" class="number-counter-block">
    <div 
        class="counter-wrapper flex transition-all duration-300" 
        :class="[
            layout === 'horizontal' ? 'flex-row items-center gap-8' : 'flex-col items-center gap-4',
            alignment === 'left' ? 'text-left items-start' : (alignment === 'right' ? 'text-right items-end' : 'text-center items-center')
        ]"
    >
      <div class="number-display flex items-baseline font-black tracking-tight" :style="wrapperStyles">
        <span v-if="settings.prefix" class="number-prefix opacity-50 mr-1" :style="prefixStyles">{{ settings.prefix }}</span>
        <span class="number-value bg-clip-text" :style="numberStyles">{{ displayNumber }}</span>
        <span v-if="settings.suffix" class="number-suffix opacity-50 ml-1" :style="suffixStyles">{{ settings.suffix || '%' }}</span>
      </div>
      
      <div class="counter-text flex-1">
        <h4 v-if="settings.title" class="counter-title text-xl font-bold mb-2 uppercase tracking-wider" :style="titleStyles">{{ settings.title || 'Completion' }}</h4>
        <p v-if="settings.description" class="counter-description opacity-60 text-sm leading-relaxed max-w-md mx-auto" :style="descriptionStyles">{{ settings.description }}</p>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

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
  if (props.mode === 'edit') {
      animatedValue.value = targetNumber.value
      return
  }
  
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

const wrapperStyles = computed(() => ({}))

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const prefixStyles = computed(() => {
  const base = getTypographyStyles(settings.value, 'number_', device.value)
  return { ...base, fontSize: '0.6em' }
})
const suffixStyles = computed(() => {
  const base = getTypographyStyles(settings.value, 'number_', device.value)
  return { ...base, fontSize: '0.6em' }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-counter-block { width: 100%; }
</style>
