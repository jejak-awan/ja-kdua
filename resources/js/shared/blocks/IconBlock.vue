<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="icon-block" 
        :id="(getVal<string>(settings, 'html_id', blockDevice) as string)"
        :style="iconBlockStyles(settings, blockDevice)"
      >
        <component
          :is="getVal(settings, 'linkUrl') ? 'a' : 'div'"
          class="icon-wrapper transition-colors duration-300 group"
          :href="getVal(settings, 'linkUrl') || undefined"
          :target="getVal(settings, 'linkTarget') || '_self'"
          :aria-label="getVal<string>(settings, 'aria_label', blockDevice) || undefined"
          :role="getVal<string>(settings, 'aria_label', blockDevice) ? 'img' : undefined"
          :style="iconWrapperStyles(settings, blockDevice)"
        >
          <LucideIcon 
            :name="iconName(settings, blockDevice)" 
            :size="iconSize(settings, blockDevice)" 
            :style="iconStyles(settings, blockDevice)"
            class="transition-transform duration-500 linear group-hover:hover-scale"
          />
        </component>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import BaseBlock from '../components/BaseBlock.vue'
import { LucideIcon } from '@/components/ui';
import type { CSSProperties } from 'vue'
import { 
    getVal, 
    getTextGradientStyles,
    getMaskStyles,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockProps, ModuleSettings } from '@/types/builder'

const _props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})


const iconName = (settings: ModuleSettings, device: string) => {
    const icon = getVal<string>(settings, 'icon', device) || 'Star'
    return typeof icon === 'string' ? icon.replace('lucide:', '') : 'Star'
}
const iconSize = (settings: ModuleSettings, device: string) => {
    const size = getVal<string | number>(settings, 'size', device) || 48
    return typeof size === 'number' ? size : parseInt(size as string) || 48
}

const iconBlockStyles = (settings: ModuleSettings, device: string): CSSProperties => {
  const layoutStyles = getLayoutStyles(settings, device)
  const styles: Record<string, string | number> = {
    width: '100%',
    ...layoutStyles,
    textAlign: (getVal<string>(settings, 'alignment', device) || 'center') as string
  }
  return styles as CSSProperties
}

const iconWrapperStyles = (settings: ModuleSettings, device: string): CSSProperties => {
  const styles: Record<string, string | number> = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    textDecoration: 'none',
    position: 'relative',
    transition: 'all 0.3s ease-in-out'
  }

  // Set CSS Variables for Hover States
  const hoverScale = getVal<number>(settings, 'hover_scale', device) || 1.1;
  styles['--hover-scale'] = `scale(${hoverScale})`;
  
  const hoverColor = getVal<string>(settings, 'hover_color', device);
  if (hoverColor) {
      styles['--hover-color'] = hoverColor;
  }

  const hoverBgColor = getVal<string>(settings, 'hover_background_color', device);
  if (hoverBgColor) {
      styles['--hover-bg-color'] = hoverBgColor;
  }

  // 1. Background Shape
  if (getVal<boolean>(settings, 'use_background', device)) {
      styles.backgroundColor = getVal<string>(settings, 'background_color', device) || '#f3f4f6'
      styles.padding = '20px'
      Object.assign(styles, getMaskStyles(settings, 'background', device))
  }

  // 2. Glow Effect
  if (getVal<boolean>(settings, 'use_glow', device)) {
      const glow = getVal<string>(settings, 'glow_color', device) || 'rgba(32, 89, 234, 0.5)'
      styles.filter = `drop-shadow(0 0 15px ${glow})`
  }

  return styles as CSSProperties
}

const iconStyles = (settings: ModuleSettings, device: string): CSSProperties => {
    const styles: Record<string, string | number> = {
        display: 'block',
        transition: 'color 0.3s ease-in-out'
    }

    if (getVal<boolean>(settings, 'use_gradient', device)) {
        Object.assign(styles, getTextGradientStyles(settings, '', device))
    } else {
        styles.color = getVal<string>(settings, 'color', device) || '#2059ea'
    }

    return styles as CSSProperties
}
</script>

<style scoped>
.icon-block { 
  width: 100%; 
}

.icon-wrapper {
    cursor: default;
}

a.icon-wrapper {
    cursor: pointer;
}

.icon-wrapper:hover {
    background-color: var(--hover-bg-color) !important;
}

.icon-wrapper:hover :deep(svg) {
    color: var(--hover-color) !important;
}

.group-hover\:hover-scale {
    transition: transform 0.3s ease-in-out;
}

.group:hover .group-hover\:hover-scale {
    transform: var(--hover-scale);
}
</style>
