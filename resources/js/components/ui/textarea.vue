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
    :class="cn(
      'flex min-h-[80px] w-full rounded-xl border border-border/40 bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary/20 focus-visible:border-primary disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    v-bind="$attrs"
  />
</template>
