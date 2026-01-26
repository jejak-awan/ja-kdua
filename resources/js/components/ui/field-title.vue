<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <span
    data-slot="field-title"
    :class="cn('text-sm font-medium leading-none', props.class)"
  >
    <slot />
  </span>
</template>
