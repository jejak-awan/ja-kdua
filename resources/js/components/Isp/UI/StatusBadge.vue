<script setup lang="ts">
defineProps<{
  status: 'online' | 'offline' | 'alert'
}>()
</script>

<template>
  <span 
    class="px-2 py-1 rounded text-xs font-bold uppercase transition-colors"
    :class="{
      'bg-green-100 text-green-800' : status === 'online',
      'bg-red-100 text-red-800' : status === 'offline',
      'bg-yellow-100 text-yellow-800' : status === 'alert'
    }"
  >
    {{ status }}
  </span>
</template>
