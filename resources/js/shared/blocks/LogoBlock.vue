<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="logo-block" 
        :id="getVal(settings, 'html_id', blockDevice)"
        :style="logoWrapperStyles(settings, blockDevice)"
      >
        <component
          :is="getVal(settings, 'link_url') ? 'a' : 'div'"
          :href="getVal(settings, 'link_url') || undefined"
          :target="getVal(settings, 'link_target') || '_self'"
          :aria-label="getVal(settings, 'aria_label', blockDevice) || undefined"
          :role="getVal(settings, 'aria_label', blockDevice) ? 'img' : undefined"
          class="logo-link group transition-colors duration-300"
          :style="logoLinkStyles(settings, blockDevice)"
          @click="onLinkClick"
        >
          <img 
            v-if="getVal(settings, 'image')" 
            :src="getVal(settings, 'image')" 
            :alt="getVal(settings, 'altText') || 'Logo'" 
            class="logo-image transition-[width] duration-500 ease-out"
            :style="imageStyles(settings, blockDevice)" 
          />
          <div v-else class="logo-placeholder" :style="placeholderStyles(settings, blockDevice)">
            <ImageIcon class="placeholder-icon" />
          </div>
        </component>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal, 
    toCSS,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockProps } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const logoWrapperStyles = (settings: any, device: string) => {
  const align = getVal(settings, 'alignment', device) || 'left'
  return {
    textAlign: align as any,
    width: '100%',
    display: 'flex',
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start',
    ...getLayoutStyles(settings, device)
  }
}

const logoLinkStyles = (settings: any, device: string) => {
  const styles: Record<string, any> = {
    display: 'inline-block',
    textDecoration: 'none',
    position: 'relative'
  }

  // Set CSS Variables for Hover States
  styles['--hover-scale'] = `scale(${getVal(settings, 'hover_scale', device) || 1.05})`;
  styles['--hover-opacity'] = getVal(settings, 'hover_opacity', device) ?? 1;

  return styles
}

const imageStyles = (settings: any, device: string) => {
  const maxWidth = getVal(settings, 'maxWidth', device) || 200
  return { 
    maxWidth: toCSS(maxWidth), 
    height: getVal(settings, 'height') || 'auto', 
    display: 'block'
  }
}

const placeholderStyles = (settings: any, device: string) => {
  const maxWidth = getVal(settings, 'maxWidth', device) || 200
  return { 
    width: toCSS(maxWidth), 
    height: '60px', 
    display: 'inline-flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    backgroundColor: 'rgba(0,0,0,0.05)', 
    borderRadius: '4px' 
  }
}

const onLinkClick = (e: MouseEvent) => {
  if (props.mode === 'edit') {
    e.preventDefault()
  }
}
</script>

<style scoped>
.logo-block { width: 100%; }

.logo-link {
    will-change: transform, opacity;
}

.logo-image {
    will-change: transform, opacity;
}

.logo-link:hover {
    transform: var(--hover-scale);
    opacity: var(--hover-opacity);
}

.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
</style>
