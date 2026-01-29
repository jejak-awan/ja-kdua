<template>
  <component 
    :is="iconComponent" 
    v-if="iconComponent" 
    :size="numericSize" 
    :stroke-width="strokeWidth" 
    :color="color" 
    :class="props.class" 
  />
</template>

<script setup lang="ts">
import { computed, shallowRef, watch, type HTMLAttributes, type Component } from 'vue'

const props = withDefaults(defineProps<{
  name: string;
  size?: number | string;
  strokeWidth?: number | string;
  color?: string;
  class?: HTMLAttributes['class'];
}>(), {
  size: 16,
  strokeWidth: 2,
  color: 'currentColor',
  class: '',
});

const numericSize = computed(() => {
    if (typeof props.size === 'string') {
        const parsed = parseInt(props.size);
        return isNaN(parsed) ? 16 : parsed;
    }
    return props.size;
})

// Icon cache to prevent re-importing
const iconCache = new Map<string, Component>();
const iconComponent = shallowRef<Component | null>(null);

// Convert PascalCase/camelCase to kebab-case for file names
const toKebabCase = (str: string): string => {
  return str
    .replace(/([a-z])([A-Z])/g, '$1-$2')
    .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
    .toLowerCase();
};

// Convert to PascalCase for exports
const toPascalCase = (str: string): string => {
  return str.replace(/(^|[-_])(\w)/g, (_, __, c) => c.toUpperCase());
};

// Load icon dynamically from per-icon file
watch(() => props.name, async (name) => {
  if (!name) {
    iconComponent.value = null;
    return;
  }
  
  const kebabName = toKebabCase(name);
  const pascalName = toPascalCase(name);
  
  // Check cache first
  if (iconCache.has(kebabName)) {
    iconComponent.value = iconCache.get(kebabName) || null;
    return;
  }
  
  try {
    // Dynamic import from per-icon file - enables tree-shaking
    const module = await import(`lucide-vue-next/dist/esm/icons/${kebabName}.js`);
    const icon = module.default || module[pascalName];
    
    if (icon) {
      iconCache.set(kebabName, icon);
      iconComponent.value = icon;
    } else {
      console.warn(`LucideIcon: Icon "${name}" not found`);
      iconComponent.value = null;
    }
  } catch (error) {
    console.warn(`LucideIcon: Failed to load icon "${name}"`, error);
    iconComponent.value = null;
  }
}, { immediate: true });
</script>

