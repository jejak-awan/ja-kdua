<template>
  <BaseToggle 
    :model-value="value" 
    @update:model-value="$emit('update:value', $event)"
  />
</template>

<script setup>
import { BaseToggle } from '../ui'

defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:value'])
</script>
