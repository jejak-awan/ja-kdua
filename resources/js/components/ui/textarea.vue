<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: undefined,
  },
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <textarea
    :value="modelValue"
    @input="emit('update:modelValue', $event.target.value)"
    data-slot="input"
    :class="cn(
      'flex min-h-[80px] w-full rounded-lg border border-border/50 bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 disabled:cursor-not-allowed disabled:opacity-50 placeholder:text-muted-foreground outline-none',
      props.class
    )"
    v-bind="$attrs"
  />
</template>
