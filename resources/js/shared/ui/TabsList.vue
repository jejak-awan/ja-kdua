<template>
  <TabsList
    v-bind="delegatedProps"
    :class="cn(
      'inline-flex flex-wrap h-auto items-center justify-start rounded-lg bg-muted border border-border p-1.5 text-muted-foreground gap-1',
      props.class
    )"
  >
    <slot />
  </TabsList>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { TabsList, type TabsListProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<TabsListProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>
