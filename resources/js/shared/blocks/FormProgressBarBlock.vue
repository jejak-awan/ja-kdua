<template>
  <div 
    v-if="totalSteps > 1" 
    class="form-progress-bar-block"
    :style="getLayoutStyles(settings, device)"
  >
    <div class="flex justify-between items-center mb-2 px-1">
      <span v-if="settings.show_steps" class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
        Step {{ currentStep + 1 }} of {{ totalSteps }}
      </span>
      <span v-if="settings.show_percentage" class="text-xs font-bold text-primary">
        {{ progressPercentage }}% Complete
      </span>
    </div>
    
    <Progress 
      :model-value="progressPercentage" 
      class="w-full"
      :style="{ 
        height: (settings.height || 8) + 'px',
        '--primary': settings.bar_color || ''
      }"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Progress } from '../ui'
import { getLayoutStyles } from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => props.module.settings || {})

// Inject from FormRenderer
const currentStep = inject<any>('currentStep', { value: 0 })
const totalSteps = inject<any>('totalSteps', { value: 1 })

const progressPercentage = computed(() => {
    if (totalSteps.value <= 1) return 100
    return Math.round(((currentStep.value + 1) / totalSteps.value) * 100)
})
</script>

<style scoped>
.form-progress-bar-block {
  width: 100%;
}
</style>
