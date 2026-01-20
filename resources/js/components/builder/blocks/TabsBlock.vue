<template>
  <div class="tabs-block" :style="wrapperStyles" :class="layoutClass">
    <!-- Tab Headers -->
    <div class="tabs-header" :style="headerStyles">
      <button 
        v-for="tab in tabItems" 
        :key="tab.id"
        class="tab-button"
        :class="{ 'tab-button--active': activeTabId === tab.id }"
        :style="getTabStyles(tab.id)"
        @click="activeTabId = tab.id"
      >
        {{ tab.title }}
      </button>
    </div>
    
    <!-- Tab Content -->
    <div class="tabs-content" :style="contentStyles">
      <draggable
        v-model="module.children"
        item-key="id"
        group="tab_item"
        class="tabs-items-container"
        ghost-class="ja-builder-ghost"
      >
        <template #item="{ element: child, index }">
          <ModuleWrapper
            :module="child"
            :index="index"
          />
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')

const tabItems = computed(() => {
  return (props.module.children || []).map(child => ({
    id: child.id,
    title: child.settings.title || 'Tab',
    content: child.settings.content || ''
  }))
})

const activeTabId = ref(settings.value.activeTab || tabItems.value[0]?.id || '')

// Provide state to TabItemBlock
provide('tabsState', {
    activeTabId,
    parentSettings: settings
})

const layoutClass = computed(() => {
  const position = getResponsiveValue(settings.value, 'tabPosition', device.value) || 'top'
  return `tabs--${position}`
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%', overflow: 'hidden' }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const headerStyles = computed(() => {
  const styles = {
    display: 'flex',
    gap: '2px'
  }
  
  const alignment = getResponsiveValue(settings.value, 'tabAlignment', device.value) || 'left'
  if (alignment === 'center') {
    styles.justifyContent = 'center'
  } else if (alignment === 'right') {
    styles.justifyContent = 'flex-end'
  } else if (alignment === 'fill') {
    styles.width = '100%'
  }
  
  return styles
})

const getTabStyles = (tabId) => {
  const isActive = activeTabId.value === tabId
  const alignment = getResponsiveValue(settings.value, 'tabAlignment', device.value) || 'left'
  
  const styles = {
    padding: '12px 24px',
    border: 'none',
    cursor: 'pointer',
    transition: 'var(--transition-premium)',
    flex: alignment === 'fill' ? '1' : 'none',
    position: 'relative',
    fontWeight: isActive ? '700' : '500',
    borderRadius: '12px 12px 0 0',
    fontSize: '14px',
    letterSpacing: '-0.01em'
  }
  
  const typographyPrefix = isActive ? 'tab_active_' : 'tab_'
  Object.assign(styles, getTypographyStyles(settings.value, typographyPrefix, device.value))
  
  const activeBg = getResponsiveValue(settings.value, 'tabActiveBackgroundColor', device.value) || '#ffffff'
  const normalBg = getResponsiveValue(settings.value, 'tabBackgroundColor', device.value) || 'transparent'
  
  styles.backgroundColor = isActive ? activeBg : normalBg
    
  return styles
}

const contentStyles = computed(() => {
  const bgColor = getResponsiveValue(settings.value, 'contentBackgroundColor', device.value) || '#ffffff'
  const styles = {
    backgroundColor: bgColor
  }
  
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})
</script>

<style scoped>
.tabs-block { 
  width: 100%; 
  overflow: visible; 
  background: white;
  border-radius: var(--border-radius-xl);
  box-shadow: var(--shadow-premium);
  transition: var(--transition-premium);
}

.tabs-header { 
  padding: 8px 8px 0;
  background: var(--builder-bg-secondary);
  border-bottom: 1px solid var(--builder-border-light);
}

.tab-button {
  color: var(--builder-text-secondary);
}

.tab-button:hover { 
  color: var(--builder-text-primary);
  background: rgba(0,0,0,0.03) !important;
}

.tab-button--active { 
  color: var(--builder-accent) !important;
  box-shadow: 0 -3px 0 0 var(--builder-accent) inset;
}

.tabs-content { 
  padding: 0;
  min-height: 100px;
}

.tabs--left .tabs-header { 
  padding: 8px;
  border-bottom: none;
  border-right: 1px solid var(--builder-border-light);
}

.tabs--left .tab-button {
  border-radius: 8px;
  margin-bottom: 4px;
}

.tabs--left .tab-button--active {
  background: white !important;
  box-shadow: var(--shadow-sm);
}
</style>
