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
          :style="{ 
            color: getVal(settings, 'label_text_color'),
            fontSize: getVal(settings, 'label_font_size', blockDevice)
          }"
        >
          {{ getVal(settings, 'label') }}
          <span v-if="getVal(settings, 'is_required')" class="text-destructive">*</span>
        </label>
        
        <div class="relative">
          <input
            v-model="value"
            :type="getVal(settings, 'type') || 'text'"
            :placeholder="getVal(settings, 'placeholder')"
            class="w-full px-3 py-2 border border-input rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
            :style="{ 
              backgroundColor: getVal(settings, 'field_bg_color'),
              ...getTypographyStyles(settings, '', blockDevice)
            }"
            :disabled="mode === 'edit'"
            :required="getVal(settings, 'is_required')"
          />
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
import { getTypographyStyles, getVal, getLayoutStyles } from '../utils/styleUtils'
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

<style scoped>
.form-field-block {
  width: 100%;
}
</style>
