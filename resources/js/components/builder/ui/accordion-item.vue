<template>
  <AccordionItem
    :class="cn('border-b', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </AccordionItem>
</template>

<script setup>
import { AccordionItem } from 'radix-vue';
import { cn } from '../../../lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
