<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
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
            <option v-for="option in (getVal(settings, 'options') as { label: string, value: string }[])" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
            <ChevronDown class="h-4 w-4" />
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
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js'
import type { BlockProps } from '../../types/builder'

const props = defineProps<BlockProps>()

const formState = inject<Record<string, unknown>>('formState', {})
const updateFormValue = inject<(id: string, val: unknown) => void>('updateFormValue', () => {})

const fieldId = computed(() => getVal<string>(props.settings || {}, 'field_id'))

const value = computed({
  get: () => fieldId.value ? formState[fieldId.value] : '',
  set: (val) => {
    if (fieldId.value) {
      updateFormValue(fieldId.value, val)
    }
  }
})
</script>
