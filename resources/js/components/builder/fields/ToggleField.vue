<template>
  <BaseToggle 
    :model-value="value" 
    :label="''"
    @update:model-value="$emit('update:value', $event)"
  />
</template>

<script setup lang="ts">
import { BaseToggle } from '../ui'

const props = defineProps<{
  field: any;
  value?: boolean;
}>()

const emit = defineEmits<{
  (e: 'update:value', val: boolean): void;
}>()
</script>
