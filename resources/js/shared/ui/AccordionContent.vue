<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { AccordionContent, type AccordionContentProps, useForwardProps } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionContentProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const delegated = { ...props };
  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <AccordionContent
    v-forward-props="forwardedProps"
    class="overflow-hidden text-sm transition-all data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down"
  >
    <div :class="cn('pb-4 pt-0', props.class)">
      <slot />
    </div>
  </AccordionContent>
</template>
