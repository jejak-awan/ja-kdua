<template>
  <div 
    class="accordion-block" 
    :style="wrapperStyles"
    @click="handleWrapperClick"
  >
    <draggable
      v-model="module.children"
      item-key="id"
      group="accordion_item"
      class="accordion-items-container"
      :style="containerStyles"
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

import { getLayoutStyles } from '../core/styleUtils'

const containerStyles = computed(() => {
    const layout = getLayoutStyles(settings.value, device.value)
    
    // Fallback for legacy 'standard' layout or if layout_type is missing
    if (!layout.display && !layout.gridTemplateColumns) {
        const gap = getResponsiveValue(settings.value, 'itemGap', device.value) || 24
        return {
            display: 'flex',
            flexDirection: 'column',
            gap: `${gap}px`,
            width: '100%'
        }
    }
    
    // Ensure width 100% if flex column to stretch items?
    // Or let alignment settings handle it. 
    // Usually container should accept full width.
    layout.width = '100%'
    
    return layout
})

const wrapperStyles = computed(() => {
  const styles = {
    position: 'relative', // Ensure containment
    width: '100%',
    transition: 'var(--transition-premium)'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  if (settings.value.link_url) {
    styles.cursor = 'pointer'
  }
  
  return styles
})

const handleWrapperClick = () => {
    if (settings.value.link_url) {
        if (settings.value.link_target === '_blank') {
            window.open(settings.value.link_url, '_blank')
        } else {
            window.location.href = settings.value.link_url
        }
    }
}

</script>

<style scoped>
.accordion-block { width: 100%; }
.accordion-items-container {
  display: flex;
  flex-direction: column;
  /* gap handled by inline style */
  width: 100%;
}
</style>
