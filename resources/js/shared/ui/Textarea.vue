<template>
  <textarea
    :value="modelValue"
    @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-inset focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    v-bind="$attrs"
  />
</template>

<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '../../lib/utils';

const props = defineProps<{
  modelValue?: string | number;
  class?: HTMLAttributes['class'];
}>();

const emit = defineEmits(['update:modelValue']);
</script>
