<template>
  <div v-if="!dismissed" class="alert-block" :class="alertClass" :style="wrapperStyles">
    <component :is="alertIcon" v-if="showIcon" class="alert-icon" />
    <div class="alert-content">
      <div 
        v-if="title || builder?.isEditing" 
        class="alert-title" 
        :style="titleStyles"
        :contenteditable="builder?.isEditing"
        @blur="onTitleBlur"
        @input="onTitleInput"
      >{{ title }}</div>
      <div 
        class="alert-message" 
        :style="messageStyles"
        :contenteditable="builder?.isEditing"
        @blur="onContentBlur"
        @input="onContentInput"
      >{{ content }}</div>
    </div>
    <button v-if="settings.dismissible" class="alert-dismiss" @click="dismissed = true">
      <X class="dismiss-icon" />
    </button>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Info, CheckCircle, AlertTriangle, XCircle, X } from 'lucide-vue-next'
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

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const dismissed = ref(false)
const showIcon = computed(() => settings.value.showIcon !== false)
const alertType = computed(() => settings.value.type || 'info')
const alertClass = computed(() => `alert--${alertType.value}`)

const alertIcon = computed(() => {
  const icons = { info: Info, success: CheckCircle, warning: AlertTriangle, error: XCircle }
  return icons[alertType.value] || Info
})

const title = computed(() => getResponsiveValue(settings.value, 'title', device.value) || '')
const content = computed(() => getResponsiveValue(settings.value, 'content', device.value) || '')

const onTitleBlur = (e) => {
  const val = e.target.innerText
  updateResponsiveField('title', val)
}

const onTitleInput = (e) => {
  // Optional: real-time sync if needed
}

const onContentBlur = (e) => {
  const val = e.target.innerText
  updateResponsiveField('content', val)
}

const onContentInput = (e) => {
  // Optional
}

const updateResponsiveField = (fieldName, value) => {
  const current = settings.value[fieldName]
  let newValue
  
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [device.value]: value }
  } else {
    // If it was a string, convert to responsive object or just update if not in responsive mode
    // But since we enabled responsive: true, it should ideally be an object
    newValue = { [device.value]: value }
  }
  
  builder?.updateModuleSettings(props.module.id, {
    [fieldName]: newValue
  })
}

const themeColors = computed(() => {
  const colors = {
    info: { bg: '#eff6ff', border: '#3b82f6', text: '#1e40af' },
    success: { bg: '#f0fdf4', border: '#22c55e', text: '#166534' },
    warning: { bg: '#fffbeb', border: '#f59e0b', text: '#92400e' },
    error: { bg: '#fef2f2', border: '#ef4444', text: '#991b1b' }
  }
  return colors[alertType.value] || colors.info
})

const wrapperStyles = computed(() => {
  const styles = { 
    width: '100%',
    backgroundColor: themeColors.value.bg,
    color: themeColors.value.text,
    borderLeftColor: themeColors.value.border
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const messageStyles = computed(() => getTypographyStyles(settings.value, 'content_', device.value))
</script>

<style scoped>
.alert-block { display: flex; align-items: flex-start; gap: 12px; width: 100%; }
.alert-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 2px; }
.alert-content { flex: 1; }
.alert-title { margin-bottom: 4px; }
.alert-dismiss { background: none; border: none; cursor: pointer; padding: 4px; opacity: 0.6; transition: opacity 0.2s; color: inherit; }
.alert-dismiss:hover { opacity: 1; }
.dismiss-icon { width: 16px; height: 16px; }
</style>
