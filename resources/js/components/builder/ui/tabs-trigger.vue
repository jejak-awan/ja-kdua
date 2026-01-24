<template>
  <TabsTrigger
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium cursor-pointer transition-colors duration-150 outline-none disabled:pointer-events-none disabled:opacity-50 text-muted-foreground hover:text-foreground data-[state=active]:bg-card data-[state=active]:text-foreground data-[state=active]:font-semibold',
      props.class
    )"
    v-bind="$attrs"
  >
    <slot />
  </TabsTrigger>
</template>

<script setup>
import { TabsTrigger } from 'radix-vue';
import { cn } from '../../../lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
