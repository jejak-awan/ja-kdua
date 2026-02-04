<template>
  <DialogDescription
    v-bind="delegatedProps"
    :class="cn('text-sm text-muted-foreground', props.class)"
  >
    <slot />
  </DialogDescription>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { DialogDescription, type DialogDescriptionProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<DialogDescriptionProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const delegated = { ...props };
  delete delegated.class;
  return delegated;
});
</script>
