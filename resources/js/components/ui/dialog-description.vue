<template>
  <DialogDescription
    :class="cn('text-sm text-muted-foreground', props.class)"
  >
    <slot />
  </DialogDescription>
</template>

<script setup>
import { DialogDescription } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
