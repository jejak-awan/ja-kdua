<template>
  <AvatarImage :class="cn('aspect-square h-full w-full', props.class)" v-bind="$attrs" />
</template>

<script setup>
import { AvatarImage } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
