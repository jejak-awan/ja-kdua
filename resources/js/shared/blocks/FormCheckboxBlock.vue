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
        
        <div class="space-y-2">
          <div v-for="option in getVal(settings, 'options')" :key="option.value" class="flex items-center space-x-2">
            <input 
              type="checkbox"
              :id="`check-${module.id}-${option.value}`"
              :value="option.value"
              v-model="value"
              :disabled="mode === 'edit'"
              class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
            />
            <label :for="`check-${module.id}-${option.value}`" class="text-sm cursor-pointer select-none">{{ option.label }}</label>
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
  get: () => {
    const val = fieldId.value ? formState[fieldId.value] : []
    return Array.isArray(val) ? val : [val].filter(Boolean)
  },
  set: (val) => {
    if (fieldId.value) {
      updateFormValue(fieldId.value, val)
    }
  }
})
</script>
