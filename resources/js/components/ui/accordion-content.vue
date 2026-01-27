<template>
  <AccordionContent
    :class="cn(
      'overflow-hidden text-sm',
      props.class
    )"
  >
    <div class="pb-4 pt-0">
      <slot />
    </div>
  </AccordionContent>
</template>

<script setup>
import { AccordionContent } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
