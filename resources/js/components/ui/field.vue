<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  orientation: {
    type: String,
    default: 'vertical', // 'vertical' | 'horizontal'
  },
  class: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <div
    data-slot="field"
    :class="cn(
      'group/field grid gap-2 data-[orientation=horizontal]:grid-cols-[auto_1fr] data-[orientation=horizontal]:items-center data-[orientation=horizontal]:gap-3 bg-transparent',
      props.class
    )"
    :data-orientation="orientation"
  >
    <slot />
  </div>
</template>
