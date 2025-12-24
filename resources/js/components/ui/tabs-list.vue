<template>
  <TabsList
    :class="cn(
      'inline-flex flex-wrap h-auto items-center justify-start rounded-lg bg-muted border border-border p-1.5 text-muted-foreground gap-1',
      props.class
    )"
  >
    <slot />
  </TabsList>
</template>

<script setup>
import { TabsList } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
