<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div class="icon-block" :style="iconBlockStyles(settings, blockDevice)">
        <component
          :is="getVal(settings, 'linkUrl') ? 'a' : 'div'"
          class="icon-wrapper transition-all duration-300 group"
          :href="getVal(settings, 'linkUrl') || undefined"
          :target="getVal(settings, 'linkTarget') || '_self'"
          :style="iconWrapperStyles(settings, blockDevice)"
        >
          <LucideIcon 
            :name="iconName(settings, blockDevice)" 
            :size="iconSize(settings, blockDevice)" 
            :style="iconStyles(settings, blockDevice)"
            class="transition-transform duration-300 group-hover:scale-110"
          />
        </component>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
    getVal, 
    getTextGradientStyles,
    getMaskStyles 
} from '../utils/styleUtils'

const props = withDefaults(defineProps<{
  module: any;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const iconName = (settings: any, device: string) => {
    const icon = getVal(settings, 'icon', device) || 'Star'
    return typeof icon === 'string' ? icon.replace('lucide:', '') : 'Star'
}
const iconSize = (settings: any, device: string) => {
    const size = getVal(settings, 'size', device) || 48
    return typeof size === 'number' ? size : parseInt(size) || 48
}

const iconBlockStyles = (settings: any, device: string) => {
  return {
    width: '100%',
    textAlign: (getVal(settings, 'alignment', device) || 'center') as any
  }
}

const iconWrapperStyles = (settings: any, device: string) => {
  const styles: Record<string, any> = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    textDecoration: 'none',
    position: 'relative'
  }

  // 1. Background Shape
  if (getVal(settings, 'use_background', device)) {
      styles.backgroundColor = getVal(settings, 'background_color', device) || '#f3f4f6'
      styles.padding = '20px'
      Object.assign(styles, getMaskStyles(settings, 'background', device))
  }

  // 2. Glow Effect
  if (getVal(settings, 'use_glow', device)) {
      const glow = getVal(settings, 'glow_color', device) || 'rgba(32, 89, 234, 0.5)'
      styles.filter = `drop-shadow(0 0 15px ${glow})`
  }

  return styles
}

const iconStyles = (settings: any, device: string) => {
    const styles: Record<string, any> = {
        display: 'block'
    }

    if (getVal(settings, 'use_gradient', device)) {
        Object.assign(styles, getTextGradientStyles(settings, '', device))
    } else {
        styles.color = getVal(settings, 'color', device) || '#2059ea'
    }

    return styles
}
</script>

<style scoped>
.icon-block { width: 100%; }
.icon-wrapper {
    cursor: default;
}
a.icon-wrapper {
    cursor: pointer;
}
</style>
