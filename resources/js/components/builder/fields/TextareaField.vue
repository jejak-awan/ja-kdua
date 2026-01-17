<template>
  <BaseInput
    v-model="internalValue"
    type="textarea"
    :placeholder="field.placeholder || ''"
    :rows="field.rows || 4"
  />
</template>

<script setup>
import { computed } from 'vue'
import { BaseInput } from '../ui'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:value'])

const internalValue = computed({
  get: () => props.value,
  set: (val) => emit('update:value', val)
})
</script>
