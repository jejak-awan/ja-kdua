<template>
  <AccordionRoot v-bind="props">
    <slot />
  </AccordionRoot>
</template>

<script setup lang="ts">
import { AccordionRoot, type AccordionRootProps } from 'radix-vue';

const props = defineProps<AccordionRootProps>();
</script>
