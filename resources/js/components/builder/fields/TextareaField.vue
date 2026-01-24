<template>
  <BaseInput
    v-model="internalValue"
    type="textarea"
    :placeholder="field.placeholder || ''"
    :rows="field.rows || 4"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { BaseInput } from '../ui'

const props = defineProps<{
  field: any;
  value?: string;
}>()

const emit = defineEmits(['update:value'])

const internalValue = computed({
  get: () => props.value,
  set: (val) => emit('update:value', val)
})
</script>
