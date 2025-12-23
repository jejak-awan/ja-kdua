<template>
  <AccordionRoot v-bind="$attrs">
    <slot />
  </AccordionRoot>
</template>

<script setup>
import { AccordionRoot } from 'radix-vue';
</script>
