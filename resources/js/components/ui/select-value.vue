<template>
  <RadixSelectValue :placeholder="placeholder" />
</template>

<script setup>
import { SelectValue as RadixSelectValue } from 'radix-vue';

const props = defineProps({
  placeholder: {
    type: String,
    default: '',
  },
});
</script>
