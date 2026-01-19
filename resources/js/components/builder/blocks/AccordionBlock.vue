<template>
  <div class="accordion-block" :style="wrapperStyles">
    <draggable
      v-model="module.children"
      item-key="id"
      group="accordion_item"
      class="accordion-items-container"
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

const accordionItems = computed(() => {
  return (props.module.children || []).map(child => ({
    id: child.id,
    title: child.settings.title || 'Accordion Item',
    content: child.settings.content || '',
    open: child.settings.open || false
  }))
})

// Initialize open items based on defaults
const openItems = ref(
  accordionItems.value.filter(i => i.open).map(i => i.id)
)

const toggleIcon = computed(() => settings.value.toggleIcon || 'chevron')
const iconPosition = computed(() => getResponsiveValue(settings.value, 'iconPosition', device.value) || 'right')

const toggleItem = (id) => {
  if (settings.value.allowMultiple) {
    if (openItems.value.includes(id)) {
      openItems.value = openItems.value.filter(i => i !== id)
    } else {
      openItems.value.push(id)
    }
  } else {
    openItems.value = openItems.value.includes(id) ? [] : [id]
  }
}

// Provide state to AccordionItemBlock
provide('accordionState', {
    openItems,
    toggleItem,
    parentSettings: settings
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const gap = getResponsiveValue(settings.value, 'itemGap', device.value) || 8
  const styles = {
    display: 'flex',
    flexDirection: 'column',
    gap: `${gap}px`,
    width: '100%'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const itemStyles = computed(() => {
  const styles = { overflow: 'hidden' }
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const headerStyles = computed(() => {
  const styles = {
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    border: 'none',
    cursor: 'pointer',
    backgroundColor: settings.value.headerBackgroundColor || '#f5f5f5',
    textAlign: 'left',
    transition: 'background-color 0.2s ease'
  }
  
  Object.assign(styles, getTypographyStyles(settings.value, 'header_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'headerPadding', device.value, 'padding'))
  
  return styles
})

const iconStyles = computed(() => {
  return {
    color: settings.value.iconColor || 'inherit'
  }
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: settings.value.contentBackgroundColor || '#ffffff'
  }
  
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})
</script>

<style scoped>
.accordion-block { width: 100%; }
.accordion-header:hover { opacity: 0.9; }
.accordion-icon { width: 20px; height: 20px; flex-shrink: 0; transition: transform 0.2s ease; }
.accordion-icon--left { margin-right: 12px; }
.accordion-icon--rotated { transform: rotate(180deg); }
.accordion-title { flex: 1; }
.accordion-content { animation: slideDown 0.2s ease; }

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
