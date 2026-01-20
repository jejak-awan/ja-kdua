<template>
  <component
    :is="settings.link_url ? 'a' : 'div'"
    class="group-block"
    :href="settings.link_url || undefined"
    :target="settings.link_url ? (settings.link_target || '_self') : undefined"
    :style="containerStyles"
  >
    <div v-if="settings.overlayColor" class="group-overlay" :style="overlayStyles" />
    <div class="group-content" :style="contentStyles">
      <draggable
        v-model="module.children"
        item-key="id"
        group="module"
        class="group-items-container"
        ghost-class="ja-builder-ghost"
      >
        <template #item="{ element: child, index }">
          <ModuleWrapper
            :module="child"
            :index="index"
          />
        </template>
        <template #footer>
          <div v-if="!module.children?.length" class="group-placeholder">
            <Square class="placeholder-icon" />
            <span>Group Container</span>
            <small>Add modules inside this group</small>
          </div>
        </template>
      </draggable>
    </div>
  </component>
</template>

<script setup>
import { computed, inject } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
import { Square } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles,
  getSizingStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const containerStyles = computed(() => {
  const styles = {
    display: 'flex',
    position: 'relative',
    overflow: 'hidden',
    textDecoration: 'none',
    color: 'inherit'
  }
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

const overlayStyles = computed(() => ({
  position: 'absolute',
  inset: 0,
  backgroundColor: settings.value.overlayColor || 'transparent',
  pointerEvents: 'none',
  zIndex: 0
}))

const contentStyles = computed(() => {
  const direction = getResponsiveValue(settings.value, 'direction', device.value) || 'column'
  const alignItems = getResponsiveValue(settings.value, 'alignItems', device.value) || 'stretch'
  const justifyContent = getResponsiveValue(settings.value, 'justifyContent', device.value) || 'flex-start'
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 20
  const wrap = getResponsiveValue(settings.value, 'wrap', device.value)

  return {
    display: 'flex',
    flexDirection: direction,
    alignItems: alignItems,
    justifyContent: justifyContent,
    gap: `${gap}px`,
    flexWrap: wrap ? 'wrap' : 'nowrap',
    width: '100%',
    position: 'relative',
    zIndex: 1,
    minHeight: 'inherit'
  }
})
</script>

<style scoped>
.group-block { transition: transform 0.2s, box-shadow 0.2s; }
a.group-block:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
.group-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  border: 2px dashed #ddd;
  border-radius: 8px;
  color: #999;
  gap: 8px;
  min-height: 150px;
}
.placeholder-icon { width: 32px; height: 32px; opacity: 0.5; }
.group-placeholder span { font-weight: 500; }
.group-placeholder small { font-size: 12px; opacity: 0.7; }
</style>
