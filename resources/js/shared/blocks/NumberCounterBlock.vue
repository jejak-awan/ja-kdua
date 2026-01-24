<template>
  <BaseBlock :module="module" :settings="settings" class="number-counter-block">
    <template #default="{ settings: blockSettings }">
      <Card class="number-counter-card group border-none shadow-xl rounded-[40px] overflow-hidden bg-white dark:bg-slate-900 transition-all duration-500 hover:-translate-y-2 p-10 flex flex-col items-center">
        <div 
            class="counter-wrapper flex transition-all duration-300 w-full" 
            :class="[
                layout === 'horizontal' ? 'flex-row items-center gap-12' : 'flex-col items-center gap-8',
                alignment === 'left' ? 'text-left items-start' : (alignment === 'right' ? 'text-right items-end' : 'text-center items-center')
            ]"
        >
          <div class="number-display flex items-baseline font-black tracking-tighter bg-primary/5 px-8 py-6 rounded-[24px] transition-all duration-500 group-hover:bg-primary/10 group-hover:scale-105" :style="wrapperStyles">
            <span v-if="blockSettings.prefix" class="number-prefix opacity-40 mr-2 text-2xl" :style="prefixStyles">{{ blockSettings.prefix }}</span>
            <span class="number-value bg-clip-text text-5xl lg:text-6xl tabular-nums" :style="numberStyles">{{ displayNumber }}</span>
            <span v-if="blockSettings.suffix" class="number-suffix opacity-40 ml-2 text-2xl" :style="suffixStyles">{{ blockSettings.suffix || '%' }}</span>
          </div>
          
          <div class="counter-text flex-1">
            <h4 v-if="blockSettings.title" class="counter-title text-[10px] font-black uppercase tracking-[0.2em] mb-3 text-slate-400 group-hover:text-primary transition-colors duration-300" :style="titleStyles">{{ blockSettings.title || 'Performance' }}</h4>
            <p v-if="blockSettings.description" class="counter-description text-slate-500 font-medium text-sm leading-relaxed max-w-sm mx-auto opacity-80" :style="descriptionStyles">{{ blockSettings.description }}</p>
          </div>
        </div>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card } from '../ui'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module.settings || {})

const animatedValue = ref(0)
const targetNumber = computed(() => parseFloat(getResponsiveValue(settings.value, 'number', currentDevice.value)) || 100)
const decimals = computed(() => getResponsiveValue(settings.value, 'decimals', currentDevice.value) || 0)
const layout = computed(() => getResponsiveValue(settings.value, 'layout', currentDevice.value) || 'vertical')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', currentDevice.value) || 'center')

const displayNumber = computed(() => {
  let num = animatedValue.value
  const useSeparator = getResponsiveValue(settings.value, 'separator', currentDevice.value) !== false
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
  
  const duration = getResponsiveValue(settings.value, 'duration', currentDevice.value) || 2000
  const easing = getResponsiveValue(settings.value, 'easing', currentDevice.value) || 'easeOut'
  const start = performance.now()
  
  const animate = (now) => {
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

const wrapperStyles = computed(() => ({
    color: settings.value.numberColor || 'currentColor'
}))

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', currentDevice.value))
const prefixStyles = computed(() => getTypographyStyles(settings.value, 'prefix_', currentDevice.value))
const suffixStyles = computed(() => getTypographyStyles(settings.value, 'suffix_', currentDevice.value))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', currentDevice.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', currentDevice.value))
</script>

<style scoped>
.number-counter-block { width: 100%; }
</style>
