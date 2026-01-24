<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :device="device"
    class="progressbar-block"
  >
    <div class="w-full">
      <!-- Title above -->
      <div v-if="titlePosition === 'above'" class="flex justify-between items-end mb-2">
        <span 
          class="font-semibold block" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('title', $event.target.innerText)"
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
            
      <!-- Inside Text (if implemented via Overlay, though standard Progress doesn't slot inside content easily without custom styling) -->
      <div v-if="titlePosition === 'inside'" class="relative -mt-5 px-3 flex justify-between items-center text-xs font-bold text-white z-10 w-full" :style="{ marginTop: `-${parseInt(getVal(settings, 'height') || 20) / 2 + 6}px` }">
          <span>{{ titleValue }}</span>
          <span v-if="showPercentage">{{ percentageValue }}%</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Progress } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'
import { cn } from '../../lib/utils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})

const percentageValue = computed(() => {
  const p = parseInt(getVal(settings.value, 'percentage')) || 75
  return Math.min(100, Math.max(0, p))
})

const showPercentage = computed(() => getVal(settings.value, 'showPercentage') !== false)
const titlePosition = computed(() => getVal(settings.value, 'titlePosition') || 'above')
const titleValue = computed(() => getVal(settings.value, 'title') || 'Progress')

const progressBarClass = computed(() => {
    return cn(
        "w-full overflow-hidden rounded-full bg-secondary",
        getVal(settings.value, 'striped') ? 'progress-striped' : '',
        getVal(settings.value, 'animated') ? 'progress-animated' : ''
    )
})

const trackStyles = computed(() => {
  const height = parseInt(getVal(settings.value, 'height')) || 20
  const radius = parseInt(getVal(settings.value, 'borderRadius')) || 10
  const trackColor = getVal(settings.value, 'trackColor') || '#e0e0e0'
  const barColor = getVal(settings.value, 'barColor') || 'var(--primary)'
  
  return {
    height: `${height}px`,
    borderRadius: `${radius}px`,
    backgroundColor: trackColor,
    '--progress-background': barColor 
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_'))
const percentageStyles = computed(() => getTypographyStyles(settings.value, 'percentage_'))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.progressbar-block {
  width: 100%;
}
/* Custom styles to override shadcn Progress defaults using vars */
:deep(.bg-primary) {
    background-color: var(--progress-background);
}
</style>
