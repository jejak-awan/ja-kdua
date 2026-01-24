<template>
  <Button
    :variant="mappedVariant"
    size="icon"
    :title="title"
    :disabled="disabled"
    @click="$emit('click', $event)"
    :class="[
        active && 'border border-white/20 shadow-sm',
        sizeClasses,
        props.class
    ]"
  >
    <component :is="icon" :size="iconSize" />
    <slot />
  </Button>
</template>

<script setup>
import { computed } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  icon: {
    type: [Object, Function, String],
    required: true
  },
  variant: {
    type: String,
    default: 'secondary', // ghost, secondary, primary
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
  active: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  class: {
    type: null,
    default: ''
  }
})

defineEmits(['click'])

const mappedVariant = computed(() => {
  if (props.active) return 'default'
  const map = {
    'primary': 'default',
    'secondary': 'secondary',
    'ghost': 'ghost'
  }
  return map[props.variant] || 'secondary'
})

const iconSize = computed(() => {
  switch (props.size) {
    case 'sm': return 14
    case 'lg': return 20
    default: return 16
  }
})

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'w-7 h-7'
        case 'lg': return 'w-10 h-10'
        default: return 'w-8 h-8'
    }
})
</script>
