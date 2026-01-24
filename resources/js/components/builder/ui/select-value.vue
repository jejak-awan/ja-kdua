<template>
  <SelectValue :placeholder="placeholder" />
</template>

<script setup>
import { SelectValue } from 'radix-vue';

const props = defineProps({
  placeholder: {
    type: String,
    default: '',
  },
});
</script>
