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
      <div 
        v-if="toggleIcon !== 'none' && iconPosition === 'left'"
        class="accordion-icon-wrapper accordion-icon-wrapper--left"
        :class="{ 'accordion-icon-wrapper--rotated': isOpen && toggleIcon === 'chevron' }"
      >
        <LucideIcon 
          v-if="isLucideComponentName"
          :name="currentIcon"
          class="accordion-icon"
          :style="iconStyles"
        />
        <component 
          v-else
          :is="currentIcon" 
          class="accordion-icon"
          :style="iconStyles"
        />
      </div>
      
      <span 
        class="accordion-title"
        contenteditable="true"
        @blur="updateTitle"
        @keydown.enter.prevent="$event.target.blur()"
        @click.stop
      >
        {{ settings.title || 'Accordion Item' }}
      </span>
      
      <div 
        v-if="toggleIcon !== 'none' && iconPosition === 'right'"
        class="accordion-icon-wrapper"
        :class="{ 'accordion-icon-wrapper--rotated': isOpen && toggleIcon === 'chevron' }"
      >
        <LucideIcon 
          v-if="isLucideComponentName"
          :name="currentIcon"
          class="accordion-icon"
          :style="iconStyles"
        />
        <component 
          v-else
          :is="currentIcon" 
          class="accordion-icon"
          :style="iconStyles"
        />
      </div>
    </button>
    
    <!-- Content -->
    <div 
      v-show="isOpen"
      class="accordion-content"
      :class="{ 'accordion-content--open': isOpen }"
      :style="contentStyles"
    >
      <InlineRichtext 
        :model-value="settings.content" 
        @update:model-value="updateContent"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, inject, unref } from 'vue'
import { ChevronDown, Plus, Minus, ArrowRight, ArrowDown } from 'lucide-vue-next'
import LucideIcon from '../ui/LucideIcon.vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue
} from '../core/styleUtils'
import InlineRichtext from '../canvas/InlineRichtext.vue'

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

const isOpen = computed(() => (accordionState.openItems.value || []).includes(props.module.id))
const parentSettings = computed(() => unref(accordionState.parentSettings) || {})

const toggleIcon = computed(() => parentSettings.value.toggleIcon || 'chevron')
const iconPosition = computed(() => getResponsiveValue(parentSettings.value, 'iconPosition', device.value) || 'right')

const currentIcon = computed(() => {
  const icon = toggleIcon.value
  
  if (icon === 'plus') {
    return isOpen.value ? Minus : Plus
  }
  
  if (icon === 'chevron') {
    return ChevronDown
  }

  if (icon === 'arrow') {
    return isOpen.value ? ArrowDown : ArrowRight
  }
  
  // Custom Lucide Icon name
  return icon
})

const isLucideComponentName = computed(() => {
    return typeof currentIcon.value === 'string'
})

const toggle = () => {
    accordionState.toggleItem(props.module.id)
}

const updateContent = (content) => {
  builder?.updateModuleSettings(props.module.id, { content })
}

const updateTitle = (e) => {
  const title = e.target.innerText
  if (title !== settings.value.title) {
    builder?.updateModuleSettings(props.module.id, { title })
  }
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
    backgroundColor: isOpen.value 
      ? (getResponsiveValue(settings.value, 'openHeaderBackgroundColor', device.value) || getResponsiveValue(parentSettings.value, 'openHeaderBackgroundColor', device.value) || '#ffffff') 
      : (getResponsiveValue(settings.value, 'headerBackgroundColor', device.value) || getResponsiveValue(parentSettings.value, 'headerBackgroundColor', device.value) || '#f7f7f7'),
    textAlign: 'left',
    transition: 'var(--transition-premium)'
  }
  
  // Base text styles
  Object.assign(styles, getTypographyStyles(parentSettings.value, 'text_', device.value))
  Object.assign(styles, getTypographyStyles(settings.value, 'text_', device.value))
  
  // State specific title styles
  if (isOpen.value) {
    Object.assign(styles, getTypographyStyles(parentSettings.value, 'header_', device.value))
    Object.assign(styles, getTypographyStyles(settings.value, 'header_', device.value))
  } else {
    Object.assign(styles, getTypographyStyles(parentSettings.value, 'closed_header_', device.value))
    Object.assign(styles, getTypographyStyles(settings.value, 'closed_header_', device.value))
  }
  
  Object.assign(styles, getSpacingStyles(parentSettings.value, 'headerPadding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'headerPadding', device.value, 'padding'))
  
  return styles
})

const iconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || getResponsiveValue(parentSettings.value, 'iconSize', device.value) || 14
  return {
    color: getResponsiveValue(settings.value, 'iconColor', device.value) || getResponsiveValue(parentSettings.value, 'iconColor', device.value) || 'inherit',
    strokeWidth: toggleIcon.value === 'plus' ? 3 : 2,
    width: `${size}px`,
    height: `${size}px`
  }
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: getResponsiveValue(settings.value, 'contentBackgroundColor', device.value) || getResponsiveValue(parentSettings.value, 'contentBackgroundColor', device.value) || 'transparent'
  }
  
  Object.assign(styles, getTypographyStyles(parentSettings.value, 'content_', device.value))
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
  
  Object.assign(styles, getSpacingStyles(parentSettings.value, 'contentPadding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})
</script>

<style scoped>
.accordion-item-block { 
  width: 100%; 
  position: relative; 
  transition: var(--transition-premium);
  border: 1px solid var(--builder-border-light);
  border-radius: var(--border-radius-md);
}

.accordion-item-block--open {
  box-shadow: var(--shadow-premium);
  z-index: 10;
  border-radius: var(--border-radius-lg);
  border-color: transparent;
}

.accordion-header { 
  border-radius: inherit; 
  font-weight: 700 !important;
}

.accordion-icon-wrapper {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: var(--builder-bg-tertiary);
  flex-shrink: 0;
  transition: var(--transition-premium);
}

.accordion-icon-wrapper--left {
  margin-right: 16px;
}

.accordion-icon-wrapper--rotated {
  transform: rotate(180deg);
}

.accordion-icon { 
  transition: var(--transition-premium);
}

.accordion-title { 
  flex: 1; 
  outline: none;
  min-height: 1.2em;
}

/* Hide toolbar focus bridge */
.accordion-title[contenteditable="true"]:focus {
    background: rgba(var(--builder-accent-rgb, 32, 89, 234), 0.05);
    border-radius: 4px;
}

.accordion-content { 
  overflow: hidden;
  transition: var(--transition-premium);
}

.accordion-content--open {
  animation: slideDown var(--transition-premium);
}

@keyframes slideDown {
  from { opacity: 0; max-height: 0; transform: translateY(-4px); }
  to { opacity: 1; max-height: 1000px; transform: translateY(0); }
}
</style>
