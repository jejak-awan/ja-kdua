<template>
  <div 
    v-show="isActive"
    class="tab-item-block"
    :class="{ 'tab-item-block--active': isActive }"
    :style="paneStyles"
  >
    <div v-html="settings.content" />
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getTypographyStyles,
  getSpacingStyles,
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

// Injected from TabsBlock
const tabsState = inject('tabsState', {
    activeTabId: null,
    parentSettings: {}
})

const isActive = computed(() => tabsState.activeTabId.value === props.module.id)
const parentSettings = computed(() => tabsState.parentSettings || {})

const paneStyles = computed(() => {
  const styles = {
    backgroundColor: getResponsiveValue(parentSettings.value, 'contentBackgroundColor', device.value) || '#ffffff'
  }
  
  Object.assign(styles, getTypographyStyles(parentSettings.value, 'content_', device.value))
  Object.assign(styles, getSpacingStyles(parentSettings.value, 'contentPadding', device.value, 'padding'))
  
  return styles
})
</script>

<style scoped>
.tab-item-block { width: 100%; border-radius: inherit; }
</style>
