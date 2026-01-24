<template>
  <TabsRoot v-bind="$attrs">
    <slot />
  </TabsRoot>
</template>

<script setup>
import { TabsRoot } from 'radix-vue';
</script>
