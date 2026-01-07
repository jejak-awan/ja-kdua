<script setup>
import { DialogTrigger as RadixDialogTrigger } from 'radix-vue';
</script>

<template>
  <RadixDialogTrigger as-child>
    <slot />
  </RadixDialogTrigger>
</template>
