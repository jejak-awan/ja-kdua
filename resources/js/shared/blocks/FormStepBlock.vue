<template>
  <div 
    v-show="isCurrentStep || mode === 'edit'" 
    class="form-step-block"
    :class="{ 'opacity-50 border-2 border-dashed border-primary/20 p-4 rounded-xl': mode === 'edit' }"
    :style="getLayoutStyles(settings, device)"
  >
    <div v-if="settings.title || settings.description" class="mb-6">
      <h3 v-if="settings.title" class="text-xl font-bold mb-1">{{ settings.title }}</h3>
      <p v-if="settings.description" class="text-muted-foreground text-sm">{{ settings.description }}</p>
    </div>

    <div class="step-content space-y-4">
      <slot />
    </div>

    <div v-if="mode === 'view' && !hideButtons" class="flex justify-between mt-8 pt-4 border-t border-border/50">
      <Button 
        v-if="currentStepIndex > 0" 
        type="button" 
        variant="outline" 
        @click="goToPrevStep"
      >
        <ChevronLeft class="w-4 h-4 mr-1" />
        {{ settings.prev_label || 'Previous' }}
      </Button>
      <div v-else></div>

      <Button 
        v-if="currentStepIndex < totalSteps - 1" 
        type="button" 
        @click="goToNextStep"
      >
        {{ settings.next_label || 'Next' }}
        <ChevronRight class="w-4 h-4 ml-1" />
      </Button>
      <Button v-else type="submit">
        {{ submitButtonText }}
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted } from 'vue'
import { Button } from '../ui'
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js'
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js'
import { getLayoutStyles } from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
  index?: number
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => props.module.settings || {})

// Inject from FormRenderer
const currentStep = inject<any>('currentStep', { value: 0 })
const totalSteps = inject<any>('totalSteps', { value: 1 })
const setTotalSteps = inject<any>('setTotalSteps', () => {})
const goToNextStep = inject<any>('goToNextStep', () => {})
const goToPrevStep = inject<any>('goToPrevStep', () => {})
const submitButtonText = inject<any>('submitButtonText', 'Submit')
const hideButtons = inject<any>('hideStepButtons', false)

// Find current index among siblings (handled by renderer)
const currentStepIndex = computed(() => props.index ?? 0)
const isCurrentStep = computed(() => currentStep.value === currentStepIndex.value)

onMounted(() => {
  // This is tricky because we don't know the exact index here easily without props from parent
})
</script>

<style scoped>
.form-step-block {
  width: 100%;
}
</style>
