<template>
  <SelectTrigger
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-inset focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    v-bind="delegatedProps"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-4 w-4 opacity-50" />
    </SelectIcon>
  </SelectTrigger>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { SelectTrigger, type SelectTriggerProps, SelectIcon } from 'radix-vue';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { cn } from '../../lib/utils';

const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const delegated = { ...props };
  delete delegated.class;
  return delegated;
});
</script>
