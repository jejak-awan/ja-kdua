<template>
  <draggable 
    v-model="module.children"
    class="row-block"
    :id="settings.cssId"
    :style="rowStyles"
    :class="cssClass"
    item-key="id"
    group="column"
    ghost-class="ja-builder-ghost"
  >
    <BackgroundMedia :settings="settings" />
    
    <a 
      v-if="settings.link_url" 
      :href="settings.link_url" 
      :target="settings.link_target" 
      class="row-link-overlay"
      @click.prevent
    ></a>
    
    <!-- Columns -->
    <template #item="{ element: column, index }">
      <div 
        class="row-block__column"
        :style="getColumnStyle(index)"
      >
        <ModuleWrapper :module="column" :index="index" />
      </div>
    </template>
    
    <!-- No columns yet - show Add Column -->
    <template #footer>
      <div v-if="!hasChildren" class="row-block__empty">
        <button class="add-column-btn" @click.stop="addColumn">
          <Plus :size="16" />
          <span>Add Column</span>
        </button>
      </div>
    </template>
  </draggable>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Plus } from 'lucide-vue-next'
import draggable from 'vuedraggable'
import { 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSpacingStyles, 
  getBackgroundStyles,
  getSizingStyles,
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'
import { BackgroundMedia } from '../canvas'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'

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

// Computed
const settings = computed(() => props.module?.settings || {})

const hasChildren = computed(() => {
  return props.module?.children && props.module.children.length > 0
})

// Parse column structure (e.g., "1-1", "2-1", "1-1-1")
const columnWidths = computed(() => {
  const structure = settings.value.columns || '1'
  const parts = structure.split('-').map(Number)
  const total = parts.reduce((a, b) => a + b, 0)
  return parts.map(p => (p / total) * 100)
})

import { useResponsiveDevice } from '../core/useResponsiveDevice'

// Styles
const device = useResponsiveDevice()

const rowStyles = computed(() => {
  const gutterRaw = getResponsiveValue(settings.value, 'gutterWidth', device.value)
  const gutter = (gutterRaw !== undefined && gutterRaw !== null && gutterRaw !== '') ? gutterRaw : 30
  
  const styles = {
    gap: typeof gutter === 'number' ? `${gutter}px` : (String(gutter).match(/^\d+$/) ? `${gutter}px` : gutter),
    transition: 'background 0.2s ease, box-shadow 0.2s ease'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  // Custom maxWidth handling if not standard
  if (settings.value.maxWidth && !styles.maxWidth) {
    const val = settings.value.maxWidth
    styles.maxWidth = typeof val === 'number' ? `${val}px` : (String(val).match(/^\d+$/) ? `${val}px` : val)
    
    if (!styles.marginLeft) styles.marginLeft = 'auto'
    if (!styles.marginRight) styles.marginRight = 'auto'
  }

  // Mobile Stacking Logic
  if (device.value === 'mobile') {
    styles.flexDirection = 'column'
  }
  
  return styles
})

const getColumnStyle = (index) => {
  const col = props.module.children?.[index]
  const s = col?.settings || {}
  const styles = {}

  // Mobile: Force Full Width
  if (device.value === 'mobile') {
    styles.width = '100%'
    styles.flex = '0 0 100%'
  } else {
    // Desktop/Tablet: Use flexGrow from column settings, fallback to 1
    const flexGrow = s.flexGrow || 1
    styles.flex = flexGrow
  }
  
  // Layout
  if (s.verticalAlignment) {
      if(s.verticalAlignment === 'center') styles.alignSelf = 'center'
      else if(s.verticalAlignment === 'bottom') styles.alignSelf = 'flex-end'
      else styles.alignSelf = 'flex-start'
  }
  
  return styles
}

const cssClass = computed(() => settings.value.cssClass || '')

// Methods
const addColumn = () => {
  builder?.insertModule('column', props.module.id)
}
</script>

<style scoped>
.row-block {
  --gutter: 10px;
  display: flex;
  flex-wrap: nowrap;
  width: 100%;
  gap: var(--gutter);
  position: relative;
  overflow: hidden;
}

/* Hover Overlay Layer */
.row-block::before {
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

.row-block:hover::before {
    opacity: 1;
}

.row-link-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
    cursor: pointer;
}

.row-block__column {
  min-height: 50px;
  display: flex;
  flex-direction: column;
  flex: 1;
  position: relative;
  z-index: 6; /* Stay above link overlay */
}

.row-block__empty {
  flex: 1;
  display: flex;
  justify-content: center;
  padding: 15px;
  position: relative;
  z-index: 2;
}

.column-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px dashed var(--builder-column, #b3c3d6);
  border-radius: 4px;
  min-height: 60px;
}

.add-column-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 24px;
  background: white;
  border: 2px dashed var(--builder-row);
  border-radius: 8px;
  color: var(--builder-row);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.add-column-btn:hover {
  background: var(--builder-row);
  color: white;
  border-style: solid;
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
