<template>
  <div 
    :id="settings.cssId"
    class="column-block"
    :style="columnStyles"
    :class="cssClass"
  >
    <BackgroundMedia :settings="settings" />
    
    <a 
      v-if="settings.link_url" 
      :href="settings.link_url" 
      :target="settings.link_target" 
      class="column-link-overlay"
      @click.prevent
    ></a>
    
    <!-- Column Children (Modules) -->
    <slot />
    
    <!-- Empty State -->
    <div v-if="!hasChildren" class="column-block__empty">
      <button class="add-btn" @click.stop="addModule">
        <Plus :size="16" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Plus } from 'lucide-vue-next'
import { 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSpacingStyles, 
  getBackgroundStyles,
  getSizingStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'
import { BackgroundMedia } from '../canvas'

const props = defineProps({
  module: {
    type: Object,
    required: true
  },
  isPreview: {
    type: Boolean,
    default: false
  }
})

// Inject builder
const builder = inject('builder')

import { useResponsiveDevice } from '../core/useResponsiveDevice'

// Computed
const settings = computed(() => props.module?.settings || {})

const hasChildren = computed(() => {
  return props.module?.children && props.module.children.length > 0
})

const device = useResponsiveDevice()

const columnStyles = computed(() => {
  const styles = {
    transition: 'background 0.2s ease, box-shadow 0.2s ease'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value, device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))

  const verticalAlign = getResponsiveValue(settings.value, 'verticalAlignment', device.value) || 'top'
  const alignMap = {
    top: 'flex-start',
    center: 'center',
    bottom: 'flex-end'
  }
  styles.justifyContent = alignMap[verticalAlign]
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')

// Methods
const addModule = () => {
  builder?.openInsertModal(props.module.id)
}
</script>

<style scoped>
.column-block {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 40px;
  position: relative;
  overflow: hidden;
}

/* Hover Overlay Layer */
.column-block::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--hover-bg-color, transparent);
    background-image: var(--hover-bg-image, none);
    opacity: 0;
    transition: opacity 0.2s ease;
    pointer-events: none;
    z-index: 1;
}

.column-block:hover::before {
    opacity: 1;
}

.column-link-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
    cursor: pointer;
}

.column-block__empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 15px;
  position: relative;
  z-index: 6; /* Stay above link overlay */
}

.add-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  padding: 0;
  background: #1a1e25;
  border: none;
  border-radius: 50%;
  color: #8899a6;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.add-btn:hover {
  background: #2d323b;
  color: white;
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
