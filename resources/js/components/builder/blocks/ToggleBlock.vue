<template>
  <div class="toggle-block" :style="wrapperStyles">
    <!-- Header -->
    <button 
      class="toggle-header"
      :class="{ 'toggle-header--open': isOpen }"
      :style="headerStyles"
      @click="toggleOpen"
      type="button"
    >
      <component 
        :is="ChevronDown"
        v-if="toggleIconValue !== 'none' && iconPositionValue === 'left'"
        class="toggle-icon toggle-icon--left"
        :class="{ 'toggle-icon--rotated': isOpen }"
        :style="iconStyles"
      />
      <span class="toggle-title" :style="titleStyles">{{ titleValue }}</span>
      <component 
        :is="ChevronDown"
        v-if="toggleIconValue !== 'none' && iconPositionValue === 'right'"
        class="toggle-icon"
        :class="{ 'toggle-icon--rotated': isOpen }"
        :style="iconStyles"
      />
    </button>
    
    <!-- Content -->
    <div v-show="isOpen" class="toggle-content" :style="contentStyles">
      <div v-html="settings.content" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject, watch } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
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

const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value) || 'Toggle Title')
const toggleIconValue = computed(() => getResponsiveValue(settings.value, 'toggleIcon', device.value) || 'chevron')
const iconPositionValue = computed(() => getResponsiveValue(settings.value, 'iconPosition', device.value) || 'right')
const iconColorValue = computed(() => getResponsiveValue(settings.value, 'iconColor', device.value) || '#333333')
const defaultOpenValue = computed(() => getResponsiveValue(settings.value, 'defaultOpen', device.value))

const isOpen = ref(!!defaultOpenValue.value)

// Watch for defaultOpen change to update isOpen only if it changes? 
// Or just let user control it. Usually defaultOpen is initial only. 
// But in builder, if user toggles "Default Open", they expect it to open.
watch(defaultOpenValue, (newVal) => {
  isOpen.value = !!newVal
})

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

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
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    border: 'none',
    cursor: 'pointer',
    backgroundColor: getResponsiveValue(settings.value, 'headerBackgroundColor', device.value) || '#f5f5f5',
    textAlign: 'left',
    transition: 'background-color 0.2s ease'
  }
  
  Object.assign(styles, getTypographyStyles(settings.value, 'header_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'headerPadding', device.value, 'padding'))
  
  return styles
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: getResponsiveValue(settings.value, 'contentBackgroundColor', device.value) || '#ffffff',
    borderTop: '1px solid #e0e0e0'
  }
  
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})

const iconStyles = computed(() => ({
  color: iconColorValue.value,
  // If we had icon size, we would add it here
}))

const titleStyles = computed(() => {
    // header_ typography covers the button text, but if we need specific title style?
    // usually header_ covers it.
    return {}
})
</script>

<style scoped>
.toggle-block {
  width: 100%;
}

.toggle-header:hover {
  opacity: 0.9;
}

.toggle-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.toggle-icon--left {
  margin-right: 12px;
}

.toggle-icon--rotated {
  transform: rotate(180deg);
}

.toggle-title {
  flex: 1;
}

.toggle-content {
  animation: slideDown 0.2s ease;
}

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
