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
import { logger } from '@/utils/logger';
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

// Common icon aliases for backward compatibility (Renamed in Lucide v0.4xx+)
const ICON_ALIASES: Record<string, string> = {
    'HelpCircle': 'CircleHelp',
    'AlertCircle': 'CircleAlert',
    'PlusCircle': 'CirclePlus',
    'XCircle': 'CircleX',
    'CheckCircle2': 'CircleCheckBig',
    'Circle2': 'Circle',
    'MoreHorizontal': 'Ellipsis',
    'MoreVertical': 'EllipsisVertical',
    'ArrowUpCircle': 'CircleArrowUp',
    'ArrowDownCircle': 'CircleArrowDown',
    'ArrowLeftCircle': 'CircleArrowLeft',
    'ArrowRightCircle': 'CircleArrowRight',
    'Edit3': 'PenTool',
    'Edit': 'Pen',
    'Filter': 'ListFilter',
    'Sort': 'ArrowUpDown',
    'Grid': 'Grid2X2',
    'Layout': 'LayoutDashboard'
};

// Load icon dynamically - using main entry point for reliability with manual chunks
watch(() => props.name, async (name) => {
  if (!name) {
    iconComponent.value = null;
    return;
  }
  
  let targetName = toPascalCase(name);
  if (ICON_ALIASES[targetName]) {
      targetName = ICON_ALIASES[targetName];
  }
  
  const pascalName = targetName;
  
  // Check cache first
  if (iconCache.has(pascalName)) {
    iconComponent.value = iconCache.get(pascalName) || null;
    return;
  }
  
  try {
    // Import from main package - since we bundle all icons into vendor-icons, 
    // this is efficient and more reliable than direct file imports in production.
    const module = await import('lucide-vue-next');
    const icon = module[pascalName as keyof typeof module];
    
    if (icon) {
      iconCache.set(pascalName, icon as Component);
      iconComponent.value = icon as Component;
    } else {
      // Fallback: try kebab-case if PascalCase fails (some older icon lists might use kebab)
      const kebabName = toKebabCase(name);
      const kebabPascalName = toPascalCase(kebabName);
      const kebabIcon = module[kebabPascalName as keyof typeof module];
      
      if (kebabIcon) {
          iconCache.set(pascalName, kebabIcon as Component);
          iconComponent.value = kebabIcon as Component;
      } else {
          logger.warning(`LucideIcon: Icon "${name}" not found in lucide-vue-next`);
          iconComponent.value = null;
      }
    }
  } catch (error) {
    logger.warning(`LucideIcon: Failed to load icon "${name}"`, error);
    iconComponent.value = null;
  }
}, { immediate: true });
</script>

