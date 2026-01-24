<template>
  <SelectValue :placeholder="placeholder" />
</template>

<script setup lang="ts">
import { SelectValue } from 'radix-vue';

const props = defineProps<{
  placeholder?: string;
}>();
</script>
