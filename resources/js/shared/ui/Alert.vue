<template>
  <div :class="cn('relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground', props.class)" role="alert">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '../../lib/utils';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>
