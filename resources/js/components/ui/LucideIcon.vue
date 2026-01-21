<template>
  <component :is="iconComponent" v-if="iconComponent" :size="numericSize" :stroke-width="strokeWidth" :color="color" :class="customClass" />
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
  },
  color: {
    type: String,
    default: 'currentColor'
  },
  class: {
    type: String,
    default: ''
  }
})

const numericSize = computed(() => {
    // If it's a number string "24", parse it. If "24px", parse it.
    if (typeof props.size === 'string') {
        const parsed = parseInt(props.size);
        return isNaN(parsed) ? 16 : parsed;
    }
    return props.size;
})

const customClass = computed(() => props.class)

const iconComponent = computed(() => {
  if (!props.name) return null;
  
  // Convert name to PascalCase if it's kebab-case or snake_case
  // e.g. arrow-right -> ArrowRight
  const pascalName = props.name
    .replace(/(^\w|-\w|_\w)/g, m => m.replace(/-|_/, '').toUpperCase())
  
  // Handle edge cases or direct matches
  return icons[pascalName] || icons[props.name] || null
})
</script>
