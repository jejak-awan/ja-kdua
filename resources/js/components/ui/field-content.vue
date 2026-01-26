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
  <div
    data-slot="field-content"
    :class="cn('grid gap-1', props.class)"
  >
    <slot />
  </div>
</template>
