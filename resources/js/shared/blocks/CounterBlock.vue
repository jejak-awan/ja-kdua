<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="counter-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Number Counter'"
  >
    <div class="counter-container flex flex-col items-center" :style="containerStyles">
        <div 
          class="counter-number font-black text-5xl lg:text-6xl tracking-tighter text-primary bg-primary/5 w-full py-6 rounded-[24px] mb-6 text-center tabular-nums transition-[width] duration-500 hover:bg-primary/10 hover:scale-105" 
          :style="numberStyles"
        >
          {{ prefixValue }}{{ formattedDisplayNumber }}{{ suffixValue }}
        </div>
        <div 
          v-if="titleValue || mode === 'edit'" 
          class="counter-title uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 dark:text-slate-500 transition-colors duration-300" 
          :style="titleStyles"
        >
          {{ titleValue || 'Metric Label' }}
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

const targetNumber = computed(() => parseFloat(getVal(settings.value, 'number', device.value)) || 0)
const prefixValue = computed(() => getVal(settings.value, 'prefix', device.value) || '')
const suffixValue = computed(() => getVal(settings.value, 'suffix', device.value) || '')
const titleValue = computed(() => getVal(settings.value, 'title', device.value))

const displayNumber = ref(0)

const decimals = computed(() => parseInt(getVal(settings.value, 'decimals', device.value)) || 0)
const useSeparator = computed(() => getVal(settings.value, 'separator', device.value) !== false)

const formattedDisplayNumber = computed(() => {
    let num = displayNumber.value.toFixed(decimals.value)
    if (useSeparator.value) {
        const parts = num.split('.')
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        num = parts.join('.')
    }
    return num
})

onMounted(() => {
    animateNumber()
})

const animateNumber = () => {
    const duration = parseInt(getVal(settings.value, 'duration', device.value)) || 2000
    let start: number | null = null
    
    const step = (timestamp: number) => {
        if (!start) start = timestamp
        const progress = Math.min((timestamp - start) / duration, 1)
        
        // Quad ease out
        const eased = 1 - Math.pow(1 - progress, 2)
        
        displayNumber.value = eased * targetNumber.value
        
        if (progress < 1) {
            window.requestAnimationFrame(step)
        } else {
            displayNumber.value = targetNumber.value
        }
    }
    
    window.requestAnimationFrame(step)
}

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const align = getVal(settings.value, 'alignment', device.value) || 'center'
  
  return {
    ...layout,
    alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start'),
    textAlign: align
  }
})

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.counter-block { width: 100%; }
</style>

