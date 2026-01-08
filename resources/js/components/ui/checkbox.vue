<script setup>
import { computed } from "vue";
import { CheckboxRoot, CheckboxIndicator, useForwardPropsEmits } from "radix-vue";
import { cn } from "../../lib/utils";

const props = defineProps({
  checked: {
    type: [Boolean, String],
    default: undefined,
  },
  defaultChecked: {
    type: Boolean,
    default: undefined,
  },
  required: {
    type: Boolean,
    default: undefined,
  },
  name: {
    type: String,
    default: undefined,
  },
  disabled: {
    type: Boolean,
    default: undefined,
  },
  value: {
    type: String,
    default: undefined,
  },
  id: {
    type: String,
    default: undefined,
  },
  asChild: {
    type: Boolean,
    default: undefined,
  },
  as: {
    type: null,
    default: undefined,
  },
  class: {
    type: null,
    default: null,
  },
});

const emits = defineEmits(['update:checked']);

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <CheckboxRoot
    v-bind="forwarded"
    :class="cn(
      'peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-inset focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground',
      props.class
    )"
  >
    <CheckboxIndicator class="flex h-full w-full items-center justify-center text-current">
      <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4">
        <path d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path>
      </svg>
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
