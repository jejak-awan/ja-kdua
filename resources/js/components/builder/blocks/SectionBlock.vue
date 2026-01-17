<template>
  <section 
    :id="settings.cssId"
    class="section-block"
    :style="sectionStyles"
    :class="cssClass"
  >
    <BackgroundMedia :settings="settings" />
    
    <a 
      v-if="settings.link_url" 
      :href="settings.link_url" 
      :target="settings.link_target" 
      class="section-link-overlay"
      @click.prevent
    ></a>
    
    <div class="section-block__container" :style="containerStyles">
      <!-- Children (Rows) -->
      <slot />
      
      <!-- Empty State - Show Add Row button when no children -->
      <div v-if="!hasChildren" class="section-block__empty">
        <button class="add-row-btn" @click.stop="addRow">
          <Plus :size="16" />
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Plus } from 'lucide-vue-next'
import { 
  getBorderStyles, 
  getBoxShadowStyles, 
  getBackgroundStyles, 
  getSpacingStyles,
  getSizingStyles,
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles
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

const sectionStyles = computed(() => {
  const styles = {}
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const containerStyles = computed(() => {
  const styles = {}
  
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  
  if (!settings.value.fullWidth) {
    styles.maxWidth = '1200px'
    styles.marginLeft = 'auto'
    styles.marginRight = 'auto'
  }
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')

// Methods
const addRow = () => {
  builder?.openInsertRowModal(props.module.id)
}
</script>

<style scoped>
.section-block {
  position: relative;
  width: 100%;
  overflow: hidden; /* Ensure overlay doesn't spill */
}

/* Hover Overlay Layer */
.section-block::before {
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

.section-block:hover::before {
    opacity: 1;
}

.section-link-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 5; /* Above background/overlay, but below container if needed */
    cursor: pointer;
}

/* In builder, we want to ensure inner elements are still selectable */
.section-link-overlay {
    pointer-events: auto;
}

.section-block__container {
  position: relative;
  z-index: 6; /* Stay above link overlay */
  padding: 20px 15px;
}

.section-block__empty {
  display: flex;
  justify-content: center;
  padding: 30px 0;
}

.add-row-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  padding: 0;
  background: var(--builder-row, #18b793);
  border: none;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.add-row-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>
