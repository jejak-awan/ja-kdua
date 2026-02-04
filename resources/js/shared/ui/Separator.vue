<template>
  <Separator
    v-bind="delegatedProps"
    :class="
      cn(
        'shrink-0 bg-border',
        props.orientation === 'vertical' ? 'h-full w-[1px]' : 'h-[1px] w-full',
        props.class,
      )
    "
  />
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { Separator, type SeparatorProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<SeparatorProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const delegated = { ...props };
  delete delegated.class;
  return delegated;
});
</script>
