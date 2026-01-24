<template>
  <div 
    :class="cn(
        'bg-slate-200 dark:bg-slate-800 shrink-0',
        orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
        props.class
    )"
    :style="dividerStyle"
  ></div>
</template>

<script setup>
import { computed } from 'vue'
import { cn } from '../../../lib/utils'

const props = defineProps({
  orientation: {
    type: String,
    default: 'horizontal', // horizontal, vertical
  },
  margin: {
    type: [String, Number],
    default: 8
  },
  class: {
    type: String,
    default: ''
  }
})

const dividerStyle = computed(() => {
  const marginValue = typeof props.margin === 'number' ? `${props.margin}px` : props.margin
  return props.orientation === 'horizontal' 
    ? { marginTop: marginValue, marginBottom: marginValue }
    : { marginLeft: marginValue, marginRight: marginValue }
})
</script>
