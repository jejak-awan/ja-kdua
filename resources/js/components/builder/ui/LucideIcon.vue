<template>
  <component :is="iconComponent" v-if="iconComponent" :size="size" :stroke-width="strokeWidth" />
</template>

<script setup>
import { computed } from 'vue'
import * as icons from 'lucide-vue-next'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: [Number, String],
    default: 16
  },
  strokeWidth: {
    type: [Number, String],
    default: 2
  }
})

const iconComponent = computed(() => {
  // Convert name to PascalCase if it's kebab-case or snake_case
  const pascalName = props.name
    .replace(/(^\w|-\w|_\w)/g, m => m.replace(/-|_/, '').toUpperCase())
  
  return icons[pascalName] || icons[props.name] || null
})
</script>
