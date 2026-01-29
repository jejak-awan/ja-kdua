<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="progressbar-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Progress Bar'"
    :style="cardStyles"
  >
    <div class="progressbar-container w-full" :style="containerStyles">
      <!-- Title above -->
      <div v-if="titlePosition === 'above'" class="flex justify-between items-end mb-2">
        <span 
          class="font-semibold block" 
          :style="titleStyles"
          v-text="titleValue"
        ></span>
        <span v-if="showPercentage" class="font-bold block" :style="percentageStyles">{{ percentageValue }}%</span>
      </div>
      
      <!-- Progress Bar -->
      <Progress 
        :model-value="percentageValue" 
        :class="progressBarClass"
        :style="trackStyles"
      />
            
      <!-- Inside Text -->
      <div v-if="titlePosition === 'inside'" class="relative px-3 flex justify-between items-center text-xs font-bold text-white z-10 w-full" :style="insideStyles">
          <span>{{ titleValue }}</span>
          <span v-if="showPercentage">{{ percentageValue }}%</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Progress } from '../ui'
import { 
  getVal, 
  getLayoutStyles,
  getTypographyStyles 
} from '../utils/styleUtils'
import { cn } from '../../lib/utils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const percentageValue = computed(() => {
  const p = parseInt(getVal(settings.value, 'percentage', device.value)) || 75
  return Math.min(100, Math.max(0, p))
})

const showPercentage = computed(() => getVal(settings.value, 'showPercentage', device.value) !== false)
const titlePosition = computed(() => getVal(settings.value, 'titlePosition', device.value) || 'above')
const titleValue = computed(() => getVal(settings.value, 'title', device.value) || 'Progress')

const progressBarClass = computed(() => {
    return cn(
        "w-full overflow-hidden rounded-full bg-secondary",
        getVal(settings.value, 'striped', device.value) ? 'progress-striped' : '',
        getVal(settings.value, 'animated', device.value) ? 'progress-animated' : ''
    )
})

const containerStyles = computed(() => {
  const styles = getLayoutStyles(settings.value, device.value)
  const align = getVal(settings.value, 'alignment', device.value) || 'left'
  
  return {
    ...styles,
    textAlign: (align === 'center' ? 'center' : (align === 'right' ? 'right' : 'left')) as any
  }
})

const trackStyles = computed(() => {
  const height = parseInt(getVal(settings.value, 'height', device.value)) || 20
  const radius = parseInt(getVal(settings.value, 'borderRadius', device.value)) || 10
  const trackColor = getVal(settings.value, 'trackColor', device.value) || '#e0e0e0'
  const barColor = getVal(settings.value, 'barColor', device.value) || 'var(--primary)'
  
  return {
    height: `${height}px`,
    borderRadius: `${radius}px`,
    backgroundColor: trackColor,
    '--progress-background': barColor 
  }
})

const insideStyles = computed(() => {
  const height = parseInt(getVal(settings.value, 'height', device.value)) || 20
  return {
    marginTop: `-${height / 2 + 6}px`
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const percentageStyles = computed(() => getTypographyStyles(settings.value, 'percentage_', device.value))

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

</script>

<style scoped>
.progressbar-block {
  width: 100%;
  transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.progressbar-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
/* Custom styles to override shadcn Progress defaults using vars */
:deep(.bg-primary) {
    background-color: var(--progress-background);
}
</style>

