<template>
  <div class="form-progress-bar-block py-8">
    <div class="flex justify-between items-center mb-4">
      <span class="text-xs font-black uppercase tracking-widest opacity-60">Progress</span>
      <span class="text-xs font-black uppercase tracking-widest text-primary">{{ Math.round(progress) }}%</span>
    </div>
    
    <div class="progress-track w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
      <div 
        class="progress-fill h-full bg-primary transition-all duration-700 ease-out shadow-[0_0_20px_rgba(var(--primary-rgb),0.4)]"
        :style="{ width: `${progress}%` }"
      />
    </div>

    <!-- Step Progress Dots -->
    <div v-if="totalStepsValue > 1" class="flex justify-between mt-6 px-1">
        <div 
            v-for="i in totalStepsValue" 
            :key="i"
            class="w-2.5 h-2.5 rounded-full transition-all duration-500 border-2"
            :class="[
                i - 1 <= currentStepValue ? 'bg-primary border-primary scale-125' : 'bg-transparent border-slate-300 dark:border-slate-700'
            ]"
        />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, type ComputedRef } from 'vue'

interface FormState {
  value: number
}

// Inject state from FormRenderer
const currentStep = inject<ComputedRef<number> | FormState>('currentStep', { value: 0 })
const totalSteps = inject<ComputedRef<number> | FormState>('totalSteps', { value: 1 })

const currentStepValue = computed(() => {
  return typeof currentStep === 'object' && 'value' in currentStep ? currentStep.value : 0
})

const totalStepsValue = computed(() => {
  return typeof totalSteps === 'object' && 'value' in totalSteps ? totalSteps.value : 1
})

const progress = computed(() => {
  if (totalStepsValue.value <= 1) return 100
  return ((currentStepValue.value) / (totalStepsValue.value - 1)) * 100
})
</script>
