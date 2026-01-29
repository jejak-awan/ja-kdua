<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="circle-counter-wrapper transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Progress Circle'"
    :style="cardStyles"
  >
    <div class="circle-counter-container flex flex-col items-center" :style="containerStyles">
        <div class="circle-svg-container relative flex items-center justify-center" :style="svgContainerStyles">
          <svg :width="size" :height="size" viewBox="0 0 100 100" class="circle-svg -rotate-90">
            <!-- Background Circle -->
            <circle
              class="circle-bg"
              cx="50"
              cy="50"
              :r="radius"
              fill="none"
              :stroke="trackColor"
              :stroke-width="normalizedThickness"
              opacity="0.1"
            />
            <!-- Progress Circle -->
            <circle
              class="circle-fill"
              cx="50"
              cy="50"
              :r="radius"
              fill="none"
              :stroke="fillColor"
              :stroke-width="normalizedThickness"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="dashOffset"
              stroke-linecap="round"
            />
          </svg>
          
          <div class="circle-inner-content absolute inset-0 flex flex-col items-center justify-center text-center">
            <div 
              v-if="getVal(settings, 'showValue', device) !== false" 
              class="circle-value font-black text-4xl lg:text-5xl tracking-tighter tabular-nums" 
              :style="valueStyles"
            >
              {{ currentValue }}%
            </div>
          </div>
        </div>

        <div 
          v-if="hasTitle" 
          class="circle-title mt-8 uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
          :style="titleStyles"
          v-text="getVal(settings, 'title', device) || 'Project Velocity'"
        ></div>
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

const size = computed(() => parseInt(getVal(settings.value, 'size', device.value)) || 180)
const thickness = computed(() => parseInt(getVal(settings.value, 'thickness', device.value)) || 10)
const targetValue = computed(() => Math.min(100, Math.max(0, parseInt(getVal(settings.value, 'value', device.value)) || 75)))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(settings.value, 'title', device.value))

const normalizedThickness = computed(() => (thickness.value / size.value) * 100)
const radius = computed(() => 50 - normalizedThickness.value / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)

const currentValue = computed(() => props.mode === 'edit' ? targetValue.value : animatedValue.value)
const dashOffset = computed(() => circumference.value * (1 - currentValue.value / 100))

const fillColor = computed(() => getVal(settings.value, 'color', device.value) || 'currentColor')
const trackColor = computed(() => getVal(settings.value, 'trackColor', device.value) || 'currentColor')

onMounted(() => {
  if (props.mode !== 'edit') {
    let start: number | null = null
    const duration = parseInt(getVal(settings.value, 'duration', device.value)) || 2000
    const step = (timestamp: number) => {
      if (!start) start = timestamp
      const progress = Math.min((timestamp - start) / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3) 
      animatedValue.value = Math.floor(eased * targetValue.value)
      if (progress < 1) {
        window.requestAnimationFrame(step)
      } else {
        animatedValue.value = targetValue.value
      }
    }
    window.requestAnimationFrame(step)
  }
})

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1.05
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const align = getVal(settings.value, 'alignment', device.value) || 'center'
  
  return {
    ...layout,
    alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start'),
    textAlign: align
  }
})

const svgContainerStyles = computed(() => ({
  width: `${size.value}px`,
  height: `${size.value}px`,
  color: fillColor.value
}))

const valueStyles = computed(() => getTypographyStyles(settings.value, 'value_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))

</script>

<style scoped>
.circle-counter-wrapper { width: 100%; }
.circle-counter-wrapper:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.circle-fill { transition: stroke-dashoffset 2s cubic-bezier(0.1, 0.5, 0.5, 1); }
</style>

