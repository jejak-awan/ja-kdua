<template>
  <div 
    class="accordion-item-block"
    :class="{ 'accordion-item-block--open': isOpen }"
    :style="itemStyles"
  >
    <!-- Header -->
    <button 
      class="accordion-header"
      :class="{ 'accordion-header--open': isOpen }"
      :style="headerStyles"
      @click="toggle"
    >
      <ChevronDown 
        v-if="toggleIcon !== 'none' && iconPosition === 'left'"
        class="accordion-icon accordion-icon--left"
        :class="{ 'accordion-icon--rotated': isOpen }"
        :style="iconStyles"
      />
      <span class="accordion-title">{{ settings.title || 'Accordion Item' }}</span>
      <ChevronDown 
        v-if="toggleIcon !== 'none' && iconPosition === 'right'"
        class="accordion-icon"
        :class="{ 'accordion-icon--rotated': isOpen }"
        :style="iconStyles"
      />
    </button>
    
    <!-- Content -->
    <div 
      v-show="isOpen"
      class="accordion-content"
      :style="contentStyles"
    >
      <div v-html="settings.content" />
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue
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

// Injected from AccordionBlock
const accordionState = inject('accordionState', {
    openItems: [],
    toggleItem: () => {},
    parentSettings: {}
})

const isOpen = computed(() => accordionState.openItems.includes(props.module.id))
const parentSettings = computed(() => accordionState.parentSettings || {})

const toggleIcon = computed(() => parentSettings.value.toggleIcon || 'chevron')
const iconPosition = computed(() => getResponsiveValue(parentSettings.value, 'iconPosition', device.value) || 'right')

const toggle = () => {
    accordionState.toggleItem(props.module.id)
}

const itemStyles = computed(() => {
  const styles = { overflow: 'hidden' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
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
    backgroundColor: parentSettings.value.headerBackgroundColor || '#f5f5f5',
    textAlign: 'left',
    transition: 'background-color 0.2s ease'
  }
  
  Object.assign(styles, getTypographyStyles(parentSettings.value, 'header_', device.value))
  Object.assign(styles, getSpacingStyles(parentSettings.value, 'headerPadding', device.value, 'padding'))
  
  return styles
})

const iconStyles = computed(() => {
  return {
    color: parentSettings.value.iconColor || 'inherit'
  }
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: parentSettings.value.contentBackgroundColor || '#ffffff'
  }
  
  Object.assign(styles, getTypographyStyles(parentSettings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(parentSettings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})
</script>

<style scoped>
.accordion-item-block { width: 100%; position: relative; }
.accordion-header { border-radius: inherit; }
.accordion-header:hover { opacity: 0.9; }
.accordion-icon { width: 20px; height: 20px; flex-shrink: 0; transition: transform 0.2s ease; }
.accordion-icon--left { margin-right: 12px; }
.accordion-icon--rotated { transform: rotate(180deg); }
.accordion-title { flex: 1; pointer-events: none; }
.accordion-content { animation: slideDown 0.2s ease; }

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
