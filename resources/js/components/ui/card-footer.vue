<template>
  <div 
    data-slot="card-footer"
    :class="cn('bg-muted/50 rounded-b-xl border-t border-border/60 p-4 group-data-[size=sm]/card:p-3 flex items-center', props.class)"
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
