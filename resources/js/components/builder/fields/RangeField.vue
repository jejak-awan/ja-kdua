<template>
  <div class="range-field">
    <BaseSliderInput
      :model-value="Number(value)"
      :min="field.min"
      :max="field.max"
      :step="field.step"
      :unit="field.unit"
      :placeholder-value="placeholderValue !== null && placeholderValue !== undefined ? Number(placeholderValue) : undefined"
      @update:model-value="(val: number | string) => $emit('update:value', val)"
    />
  </div>
</template>

<script setup lang="ts">
import { BaseSliderInput } from '../ui'

const props = defineProps<{
  field: any;
  value?: number | string;
  placeholderValue?: number | string | null;
}>()

defineEmits(['update:value'])
</script>

<style scoped>
.range-field {
  width: 100%;
}
</style>
