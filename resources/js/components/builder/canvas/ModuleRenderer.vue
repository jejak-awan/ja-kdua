<template>
  <component 
    v-if="BlockComponent"
    :is="BlockComponent"
    :module="module"
    :is-preview="true"
    :mode="'edit'"
    :device="currentDevice"
  >
    <!-- Pass children through slot -->
    <slot />
  </component>
  <div v-else class="module-placeholder">
    <span>{{ module.type }}</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import ModuleRegistry from '../core/ModuleRegistry'

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

const builder = inject('builder')
const currentDevice = computed(() => builder?.device || 'desktop')

const BlockComponent = computed(() => {
  return ModuleRegistry.getComponent(props.module.type)
})
</script>

<style scoped>
.module-placeholder {
  padding: var(--spacing-lg);
  background: var(--builder-bg-tertiary);
  border-radius: var(--border-radius-sm);
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}
</style>
