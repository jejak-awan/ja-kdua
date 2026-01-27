<template>
  <RadixSelectTrigger
    data-slot="select-trigger"
    :class="cn(
      'flex h-8 w-full items-center justify-between rounded-lg border border-border/50 bg-transparent px-2.5 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50 disabled:cursor-not-allowed disabled:opacity-50 placeholder:text-muted-foreground',
      props.class
    )"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-4 w-4 opacity-50" />
    </SelectIcon>
  </RadixSelectTrigger>
</template>

<script setup>
import { SelectTrigger as RadixSelectTrigger, SelectIcon } from 'radix-vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
