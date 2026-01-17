<template>
  <component 
    v-if="BlockComponent"
    :is="BlockComponent"
    :module="resolvedModule"
    :is-preview="true"
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

const resolvedSettings = computed(() => {
  const settings = props.module.settings || {}
  const resolved = {}
  
  // 1. Get all unique base keys (removing _hover, _tablet, and _phone suffixes)
  const baseKeys = new Set()
  Object.keys(settings).forEach(key => {
    const baseKey = key.replace(/(_hover|_tablet|_phone)$/, '')
    baseKeys.add(baseKey)
  })

  // 2. Resolve each base key for the current device and hover
  baseKeys.forEach(key => {
    // Standard Responsive Resolution
    const desktopValue = settings[key]
    const tabletValue = settings[key + '_tablet']
    const phoneValue = settings[key + '_phone']
    
    // Hover Resolution (Hover can also be responsive, but usually treated as global first)
    // We prioritize key_hover, but could also check key_tablet_hover etc. if needed.
    const hoverValue = settings[key + '_hover']

    // Resolve current value based on device
    if (currentDevice.value === 'desktop') {
      resolved[key] = desktopValue
    } else if (currentDevice.value === 'tablet') {
      resolved[key] = (tabletValue !== undefined && tabletValue !== '') ? tabletValue : desktopValue
    } else if (currentDevice.value === 'mobile') {
      if (phoneValue !== undefined && phoneValue !== '') {
        resolved[key] = phoneValue
      } else if (tabletValue !== undefined && tabletValue !== '') {
        resolved[key] = tabletValue
      } else {
        resolved[key] = desktopValue
      }
    } else {
      resolved[key] = desktopValue
    }

    // Resolve hover value
    if (hoverValue !== undefined && hoverValue !== '') {
        resolved[key + '_hover'] = hoverValue
    }
  })

  return resolved
})

const resolvedModule = computed(() => {
  return {
    ...props.module,
    settings: resolvedSettings.value
  }
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
