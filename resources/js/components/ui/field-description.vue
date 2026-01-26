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
  <p
    data-slot="field-description"
    :class="cn('text-[0.8rem] text-muted-foreground', props.class)"
  >
    <slot />
  </p>
</template>
