<script setup>
import { cn } from '@/lib/utils';

// Actually, to be safe and dependency-free (if vueuse isn't verified), standard way:
const props = defineProps({
  defaultValue: {
    type: [String, Number],
    default: undefined,
  },
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
  <input
    :value="modelValue"
    @input="emit('update:modelValue', $event.target.value)"
    data-slot="input"
    :class="cn(
      'flex h-8 w-full rounded-lg border border-border/50 bg-transparent px-2.5 py-1 text-base file:h-6 file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:border-primary/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm placeholder:text-muted-foreground outline-none file:inline-flex file:border-0 file:bg-transparent',
      props.class
    )"
    v-bind="$attrs"
  />
</template>
