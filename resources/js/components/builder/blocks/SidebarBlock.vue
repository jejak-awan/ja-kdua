<template>
  <aside class="sidebar-block" :style="wrapperStyles">
    <!-- Dynamic Widgets -->
    <!-- Dynamic Widgets -->
    <draggable
      v-model="module.children"
      item-key="id"
      group="sidebar_widget"
      class="sidebar-widgets"
      ghost-class="ja-builder-ghost"
    >
      <template #item="{ element: child, index }">
        <ModuleWrapper
          :module="child"
          :index="index"
          class="sidebar-widget-wrapper"
        />
      </template>
    </draggable>
  </aside>
</template>

<script setup>
import { computed, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
import { Search as SearchIcon } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const showTitle = computed(() => getResponsiveValue(settings.value, 'showTitle', device.value) !== false)

// Provide state to SidebarWidgetBlock
provide('sidebarState', {
    parentSettings: settings
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const widgetStyles = computed(() => getTypographyStyles(settings.value, 'widget_', device.value))
</script>

<style scoped>
.sidebar-block { width: 100%; }
.sidebar-widget { margin-bottom: 24px; }
.sidebar-widget:last-child { margin-bottom: 0; }
.widget-title { margin: 0 0 12px; }
.search-widget { position: relative; }
.search-input { width: 100%; padding: 10px 40px 10px 14px; border: 1px solid #e0e0e0; border-radius: 6px; box-sizing: border-box; }
.search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #999; }
.widget-list { list-style: none; margin: 0; padding: 0; }
.widget-list li { padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
.widget-list li:last-child { border-bottom: none; }
.widget-list a { text-decoration: none; color: inherit; }
.tags-cloud { display: flex; flex-wrap: wrap; gap: 8px; }
.tag { display: inline-block; padding: 4px 12px; background: #e0e0e0; border-radius: 4px; text-decoration: none; color: inherit; }
</style>
