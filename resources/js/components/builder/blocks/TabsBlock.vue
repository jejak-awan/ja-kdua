<template>
  <div class="tabs-block" :style="wrapperStyles" :class="layoutClass">
    <!-- Tab Headers -->
    <div class="tabs-header" :style="headerStyles">
      <button 
        v-for="(tab, index) in items" 
        :key="index"
        class="tab-button"
        :class="{ 'tab-button--active': activeTabIndex === index }"
        :style="getTabStyles(index)"
        @click="activeTabIndex = index"
      >
        <span class="flex items-center gap-2">
            <component v-if="tab.icon" :is="getIcon(tab.icon)" class="w-4 h-4" />
            {{ tab.title || 'Tab' }}
        </span>
      </button>
    </div>
    
    <!-- Tab Content -->
    <div class="tabs-content" :style="contentStyles">
        <div 
          v-for="(tab, index) in items" 
          v-show="activeTabIndex === index"
          :key="index"
          class="tab-pane prose prose-sm max-w-none"
          v-html="tab.content || 'Tab content...'"
        ></div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import * as Icons from 'lucide-vue-next'
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
  module: { type: Object, required: true }
})

const settings = computed(() => props.module.settings || {})
const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const activeTabIndex = ref(0)

const getIcon = (name) => {
    if (!name) return null;
    const normalized = name.charAt(0).toUpperCase() + name.slice(1);
    return Icons[normalized] || Icons.RectangleHorizontal;
}

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
  const styles = { display: 'flex' }
  const alignment = getResponsiveValue(settings.value, 'tabAlignment', device.value) || 'left'
  
  if (alignment === 'center') {
    styles.justifyContent = 'center'
  } else if (alignment === 'right') {
    styles.justifyContent = 'flex-end'
  }
  
  return styles
})

const getTabStyles = (index) => {
  const isActive = activeTabIndex.value === index
  const alignment = getResponsiveValue(settings.value, 'tabAlignment', device.value) || 'left'
  
  const styles = {
    padding: '14px 24px',
    border: 'none',
    cursor: 'pointer',
    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    flex: alignment === 'fill' ? '1' : 'none',
    position: 'relative'
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
  const styles = { backgroundColor: bgColor }
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'contentPadding', device.value, 'padding'))
  return styles
})
</script>

<style scoped>
.tabs-block { width: 100%; border-radius: 12px; }
.tabs-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }

.tab-button {
  color: #64748b;
  border-bottom: 2px solid transparent;
}

.tab-button:hover { background: rgba(0,0,0,0.02); color: #0f172a; }

.tab-button--active { 
  color: var(--builder-accent) !important;
  border-bottom-color: var(--builder-accent);
}

.tabs-content { padding: 24px; min-height: 100px; }

.tabs--left { display: flex; }
.tabs--left .tabs-header { flex-direction: column; border-bottom: none; border-right: 1px solid #e2e8f0; width: 220px; }
.tabs--left .tab-button { border-bottom: none; border-right: 2px solid transparent; text-align: left; }
.tabs--left .tab-button--active { border-right-color: var(--builder-accent); }
.tabs--left .tabs-content { flex: 1; }

.tabs--bottom { display: flex; flex-direction: column-reverse; }
.tabs--bottom .tabs-header { border-bottom: none; border-top: 1px solid #e2e8f0; }
.tabs--bottom .tab-button { border-bottom: none; border-top: 2px solid transparent; }
.tabs--bottom .tab-button--active { border-top-color: var(--builder-accent); }
</style>
