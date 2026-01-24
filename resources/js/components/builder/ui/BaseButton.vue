<template>
  <Button
    :variant="mappedVariant"
    :size="mappedSize"
    :disabled="disabled || loading"
    v-bind="$attrs"
    :class="[
        { 'opacity-50 pointer-events-none': loading },
        active && 'ring-2 ring-primary ring-offset-2',
        props.class
    ]"
  >
    <template v-if="loading">
      <Loader2 class="w-4 h-4 animate-spin mr-2" />
      <slot />
    </template>
    <slot v-else />
  </Button>
</template>

<script setup>
import { computed } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import Button from './Button.vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'secondary', // primary, secondary, danger, ghost
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  active: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: ''
  }
})

const mappedVariant = computed(() => {
  const map = {
    'primary': 'default',
    'secondary': 'secondary',
    'danger': 'destructive',
    'ghost': 'ghost'
  }
  return map[props.variant] || 'default'
})

const mappedSize = computed(() => {
    const map = {
        'sm': 'sm',
        'md': 'default',
        'lg': 'lg'
    }
    return map[props.size] || 'default'
})
</script>
