<template>
  <SelectPortal>
    <SelectContent
      :class="cn(
        'relative z-50 min-w-[8rem] overflow-hidden rounded-xl border border-border/60 bg-popover text-popover-foreground shadow-xl',
        props.class
      )"
      :position="position"
    >
      <SelectViewport class="p-1 max-h-60 overflow-y-auto">
        <slot />
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>

<script setup>
import { SelectPortal, SelectContent, SelectViewport } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
  position: {
    type: String,
    default: 'popper',
  },
});
</script>
