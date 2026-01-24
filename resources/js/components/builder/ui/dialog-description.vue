<template>
  <RadixDialogDescription
    :class="cn('text-sm text-muted-foreground', props.class)"
  >
    <slot />
  </RadixDialogDescription>
</template>

<script setup>
import { DialogDescription as RadixDialogDescription } from 'radix-vue';
import { cn } from '../../../lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
