<template>
  <AccordionItem
    :class="cn('border-b', props.class)"
    v-bind="delegatedProps"
  >
    <slot />
  </AccordionItem>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { AccordionItem, type AccordionItemProps, useForwardProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<AccordionItemProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>
