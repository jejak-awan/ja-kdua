<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="number-counter-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Number Counter'"
    :style="cardStyles"
  >
    <div 
        class="counter-container flex flex-col items-center" 
        :style="containerStyles"
    >
      <div 
          class="counter-wrapper flex transition-colors duration-300 w-full" 
          :class="[
              layout === 'horizontal' ? 'flex-row items-center gap-12' : 'flex-col items-center gap-8',
              alignment === 'left' ? 'text-left items-start' : (alignment === 'right' ? 'text-right items-end' : 'text-center items-center')
          ]"
      >
        <div class="number-display flex items-baseline font-black tracking-tighter bg-primary/5 px-8 py-6 rounded-[24px] transition-colors" :style="wrapperStyles">
          <span v-if="settings.prefix" class="number-prefix opacity-40 mr-2 text-2xl" :style="prefixStyles">{{ settings.prefix }}</span>
          <span class="number-value bg-clip-text text-5xl lg:text-6xl tabular-nums" :style="numberStyles">{{ displayNumber }}</span>
          <span v-if="settings.suffix" class="number-suffix opacity-40 ml-2 text-2xl" :style="suffixStyles">{{ settings.suffix || '%' }}</span>
        </div>
        
        <div class="counter-text flex-1">
          <h4 v-if="settings.title" class="counter-title text-[10px] font-black uppercase tracking-[0.2em] mb-3 text-slate-400 dark:text-slate-500 transition-colors duration-300" :style="titleStyles">{{ settings.title || 'Performance' }}</h4>
          <p v-if="settings.description" class="counter-description text-slate-500 dark:text-slate-400 font-medium text-sm leading-relaxed max-w-sm opacity-80" :style="descriptionStyles">{{ settings.description }}</p>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const animatedValue = ref(0)
const targetNumber = computed(() => parseFloat(getVal(settings.value, 'number', device.value)) || 100)
const decimals = computed(() => parseInt(getVal(settings.value, 'decimals', device.value)) || 0)
const layout = computed(() => getVal(settings.value, 'layout', device.value) || 'vertical')
const alignment = computed(() => getVal(settings.value, 'alignment', device.value) || 'center')

const displayNumber = computed(() => {
  let num = animatedValue.value
  const useSeparator = getVal(settings.value, 'separator', device.value) !== false
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
  
  const duration = parseInt(getVal(settings.value, 'duration', device.value)) || 2000
  const easing = getVal(settings.value, 'easing', device.value) || 'easeOut'
  const start = performance.now()
  
  const animate = (now: number) => {
    const progress = Math.min((now - start) / duration, 1)
    const eased = easing === 'linear' ? progress : 
                  easing === 'easeIn' ? progress * progress :
                  easing === 'easeInOut' ? (progress < 0.5 ? 2 * progress * progress : 1 - Math.pow(-2 * progress + 2, 2) / 2) :
                  1 - Math.pow(1 - progress, 3) 
    animatedValue.value = eased * targetNumber.value
    if (progress < 1) requestAnimationFrame(animate)
  }
  requestAnimationFrame(animate)
})

const containerStyles = computed(() => {
  const styles = getLayoutStyles(settings.value, device.value)
  return styles
})

const wrapperStyles = computed(() => ({
    color: settings.value.numberColor || 'currentColor'
}))

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const prefixStyles = computed(() => getTypographyStyles(settings.value, 'prefix_', device.value))
const suffixStyles = computed(() => getTypographyStyles(settings.value, 'suffix_', device.value))

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-counter-block { width: 100%; }
.number-display {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease, background-color 0.3s ease;
}
.number-counter-block:hover .number-display {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
    background-color: rgba(var(--primary-rgb), 0.1);
}
</style>

