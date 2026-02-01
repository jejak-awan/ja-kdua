<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="form-field-block"
        :style="[getLayoutStyles(settings, blockDevice)]"
      >
        <label 
          v-if="getVal(settings, 'label')"
          class="block text-sm font-medium mb-1.5"
        >
          {{ getVal(settings, 'label') }}
          <span v-if="getVal(settings, 'is_required')" class="text-destructive">*</span>
        </label>
        
        <div class="relative">
          <select
            v-model="value"
            class="w-full px-3 py-2 border border-input rounded-md bg-transparent appearance-none disabled:opacity-100"
            :disabled="mode === 'edit'"
            :required="getVal(settings, 'is_required')"
          >
            <option value="" disabled>{{ getVal(settings, 'placeholder') || 'Select an option' }}</option>
            <option v-for="option in getVal(settings, 'options')" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
              <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
          </div>
        </div>

        <p v-if="getVal(settings, 'help_text')" class="mt-1.5 text-xs text-muted-foreground">
          {{ getVal(settings, 'help_text') }}
        </p>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getLayoutStyles } from '../utils/styleUtils'
import type { BlockProps } from '../../types/builder'

const props = defineProps<BlockProps>()

const formState = inject<Record<string, any>>('formState', {})
const updateFormValue = inject<(id: string, val: any) => void>('updateFormValue', () => {})

const fieldId = computed(() => getVal(props.settings || {}, 'field_id'))

const value = computed({
  get: () => fieldId.value ? formState[fieldId.value] : '',
  set: (val) => {
    if (fieldId.value) {
      updateFormValue(fieldId.value, val)
    }
  }
})
</script>
