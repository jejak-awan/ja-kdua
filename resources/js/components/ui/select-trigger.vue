<template>
  <SelectTrigger
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-xl border border-border/40 bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary/20 focus:border-primary disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-4 w-4 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>

<script setup>
import { SelectTrigger, SelectIcon } from 'radix-vue';
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
