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
    data-slot="field-group"
    :class="cn('grid gap-4', props.class)"
  >
    <slot />
  </div>
</template>
