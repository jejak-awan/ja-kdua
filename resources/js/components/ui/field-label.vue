<script setup>
import { Label } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <Label
    data-slot="field-label"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-50 group-data-[disabled=true]/field:opacity-50 group-data-[disabled=true]/field:cursor-not-allowed bg-transparent',
      props.class
    )"
  >
    <slot />
  </Label>
</template>
