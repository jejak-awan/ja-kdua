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
      <div 
        v-for="tab in tabItems" 
        :key="tab.id"
        class="tab-pane"
        :class="{ 'tab-pane--active': activeTabId === tab.id }"
      >
        <div v-html="tab.content" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
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
const device = computed(() => builder?.device || 'desktop')

const tabItems = computed(() => {
  return (props.module.children || []).map(child => ({
    id: child.id,
    title: child.settings.title || 'Tab',
    content: child.settings.content || ''
  }))
})

const activeTabId = ref(settings.value.activeTab || tabItems.value[0]?.id || '')

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
    padding: '10px 20px',
    border: 'none',
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    flex: alignment === 'fill' ? '1' : 'none',
    borderRadius: '4px 4px 0 0'
  }
  
  const typographyPrefix = isActive ? 'tab_active_' : 'tab_'
  Object.assign(styles, getTypographyStyles(settings.value, typographyPrefix, device.value))
  
  const activeBg = getResponsiveValue(settings.value, 'tabActiveBackgroundColor', device.value) || '#ffffff'
  const normalBg = getResponsiveValue(settings.value, 'tabBackgroundColor', device.value) || '#f0f0f0'
  
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
.tabs-block { width: 100%; overflow: hidden; }
.tabs--top { display: flex; flex-direction: column; }
.tabs--bottom { display: flex; flex-direction: column-reverse; }
.tabs--left { display: flex; flex-direction: row; }
.tabs--left .tabs-header { flex-direction: column; }
.tab-button:hover { opacity: 0.9; }
.tab-button--active { position: relative; z-index: 1; }
.tabs-content { border-top: 1px solid #e0e0e0; }
.tab-pane { display: none; }
.tab-pane--active { display: block; }
</style>
