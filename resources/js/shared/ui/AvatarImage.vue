<template>
  <AvatarImage
    v-bind="delegatedProps"
    :class="cn('aspect-square h-full w-full', props.class)"
  />
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { AvatarImage, type AvatarImageProps } from 'radix-vue';
import { cn } from '../../lib/utils';

const props = defineProps<AvatarImageProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>
