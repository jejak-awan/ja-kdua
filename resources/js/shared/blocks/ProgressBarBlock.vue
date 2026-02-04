<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="progressbar-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Progress Bar'"
    :style="(cardStyles as any)"
  >
    <div class="progressbar-container w-full" :style="(containerStyles as any)">
      <!-- Title above -->
      <div v-if="titlePosition === 'above'" class="flex justify-between items-end mb-2">
        <span 
          class="font-semibold block" 
          :style="(titleStyles as any)"
          v-text="titleValue"
        ></span>
        <span v-if="showPercentage" class="font-bold block" :style="(percentageStyles as any)">{{ percentageValue }}%</span>
      </div>
      
      <!-- Progress Bar -->
      <Progress 
        :model-value="percentageValue" 
        :class="progressBarClass"
        :style="(trackStyles as any)"
      />
            
      <!-- Inside Text -->
      <div v-if="titlePosition === 'inside'" class="relative px-3 flex justify-between items-center text-xs font-bold text-white z-10 w-full" :style="(insideStyles as any)">
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
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const percentageValue = computed(() => {
    const val = getVal<string | number>(settings.value, 'percentage', device.value)
    const p = (typeof val === 'number' ? val : parseInt(val as string)) || 75
    return Math.min(100, Math.max(0, p))
})

const showPercentage = computed(() => getVal<boolean>(settings.value, 'showPercentage', device.value) !== false)
const titlePosition = computed(() => getVal<string>(settings.value, 'titlePosition', device.value) || 'above')
const titleValue = computed(() => getVal<string>(settings.value, 'title', device.value) || 'Progress')

const progressBarClass = computed(() => {
    return cn(
        "w-full overflow-hidden rounded-full bg-secondary",
        getVal<boolean>(settings.value, 'striped', device.value) ? 'progress-striped' : '',
        getVal<boolean>(settings.value, 'animated', device.value) ? 'progress-animated' : ''
    )
})

const containerStyles = computed(() => {
  const styles = getLayoutStyles(settings.value, device.value)
  const align = getVal<string>(settings.value, 'alignment', device.value) || 'left'
  
  return {
    ...styles,
    textAlign: (align === 'center' ? 'center' : (align === 'right' ? 'right' : 'left'))
  }
})

const trackStyles = computed(() => {
  const hVal = getVal<string | number>(settings.value, 'height', device.value)
  const height = (typeof hVal === 'number' ? hVal : parseInt(hVal as string)) || 20
  const rVal = getVal<string | number>(settings.value, 'borderRadius', device.value)
  const radius = (typeof rVal === 'number' ? rVal : parseInt(rVal as string)) || 10
  const trackColor = getVal<string>(settings.value, 'trackColor', device.value) || '#e0e0e0'
  const barColor = getVal<string>(settings.value, 'barColor', device.value) || 'var(--primary)'
  
  const styles: Record<string, string | number> = {
    height: `${height}px`,
    borderRadius: `${radius}px`,
    backgroundColor: trackColor,
    '--progress-background': barColor 
  }
  return styles
})

const insideStyles = computed(() => {
  const hVal = getVal<string | number>(settings.value, 'height', device.value)
  const height = (typeof hVal === 'number' ? hVal : parseInt(hVal as string)) || 20
  const styles: Record<string, string | number> = {
    marginTop: `-${height / 2 + 6}px`
  }
  return styles
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const percentageStyles = computed(() => getTypographyStyles(settings.value, 'percentage_', device.value))

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', device.value) || 100
    
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

