<template>
  <Label 
    class="flex items-center gap-1.5 text-[11px] font-semibold tracking-wider transition-colors"
    :class="[
        muted ? 'text-slate-500' : 'text-slate-900',
        uppercase && 'uppercase'
    ]"
  >
    <slot name="prefix" />
    <slot />
    <slot name="suffix" />
  </Label>
</template>

<script setup>
import Label from './Label.vue'

defineProps({
  muted: {
    type: Boolean,
    default: true
  },
  uppercase: {
    type: Boolean,
    default: false
  }
})
</script>
