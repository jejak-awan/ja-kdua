<template>
  <div 
    data-slot="card-header"
    :class="cn('gap-1.5 rounded-t-xl px-4 group-data-[size=sm]/card:px-3 grid auto-rows-min items-start', props.class)"
  >
    <slot />
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
