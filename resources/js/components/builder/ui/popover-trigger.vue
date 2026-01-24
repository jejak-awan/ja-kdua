<script setup>
import { PopoverTrigger } from 'radix-vue'

const props = defineProps({
  asChild: { type: Boolean, required: false, default: false },
  as: { type: null, required: false, default: 'button' },
})
</script>

<template>
  <PopoverTrigger v-bind="props">
    <slot />
  </PopoverTrigger>
</template>
