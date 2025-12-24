<template>
  <TabsContent
    :class="cn(
      'mt-4 ring-offset-background focus-visible:outline-none',
      props.class
    )"
    v-bind="$attrs"
  >
    <slot />
  </TabsContent>
</template>

<script setup>
import { TabsContent } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});
</script>
