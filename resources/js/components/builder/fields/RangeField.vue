<template>
  <div class="range-field">
    <BaseSliderInput
      :model-value="Number(value)"
      :min="field.min"
      :max="field.max"
      :step="field.step"
      :unit="field.unit"
      :placeholder-value="placeholderValue !== null ? Number(placeholderValue) : null"
      @update:model-value="(val) => $emit('update:value', val)"
    />
  </div>
</template>

<script setup>
import { BaseSliderInput } from '../ui'

defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: [Number, String],
    default: 0
  },
  placeholderValue: {
    type: [Number, String],
    default: null
  }
})

defineEmits(['update:value'])
</script>

<style scoped>
.range-field {
  width: 100%;
}
</style>
