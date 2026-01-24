<template>
  <AvatarFallback :class="cn('flex h-full w-full items-center justify-center rounded-full bg-muted', props.class)" v-bind="$attrs">
    <slot />
  </AvatarFallback>
</template>

<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { AvatarFallback, type AvatarFallbackProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<AvatarFallbackProps & { class?: HTMLAttributes['class'] }>();
</script>
