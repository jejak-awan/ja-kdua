<template>
  <div class="contact-form-block" :style="wrapperStyles">
    <div class="contact-form" :style="formStyles">
      <!-- Header -->
      <div v-if="settings.title" class="form-header">
        <h2 class="form-title" :style="titleStyles">{{ settings.title }}</h2>
        <p v-if="settings.subtitle" class="form-subtitle">{{ settings.subtitle }}</p>
      </div>
      
      <!-- Fields -->
      <draggable
          v-model="module.children"
          item-key="id"
          group="contact_field"
          class="form-fields"
          ghost-class="ja-builder-ghost"
      >
          <template #item="{ element: child, index }">
              <ModuleWrapper
                  :module="child"
                  :index="index"
              />
          </template>
      </draggable>
      
      <!-- Button -->
      <button class="form-button" :style="buttonStyles">
        <Send class="button-icon" />
        {{ settings.buttonText || 'Send Message' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
import { Send } from 'lucide-vue-next'
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

// Provide state to ContactFieldBlock
provide('contactFormState', {
    parentSettings: settings
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const formStyles = computed(() => {
  const styles = {}
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  
  return styles
})

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, 'title_', device.value)
})

const labelStyles = computed(() => {
  return getTypographyStyles(settings.value, 'label_', device.value)
})

const buttonStyles = computed(() => {
  const styles = {
    display: 'inline-flex',
    alignItems: 'center',
    gap: '8px',
    backgroundColor: settings.value.buttonBackgroundColor || '#2059ea',
    color: settings.value.buttonTextColor || '#ffffff',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    transition: 'opacity 0.2s'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'button_', device.value))
  return styles
})
</script>

<style scoped>
.contact-form-block { width: 100%; }
.form-header { text-align: center; margin-bottom: 32px; }
.form-title { margin: 0 0 8px; }
.form-subtitle { margin: 0; }
.form-fields { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 24px; }
.form-field--full { grid-column: 1 / -1; }
.form-field { display: flex; flex-direction: column; gap: 8px; }
.field-required { color: #ef4444; }
.field-input { padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 6px; outline: none; transition: border-color 0.2s; font-family: inherit; }
.field-input:focus { border-color: #2059ea; }
.field-textarea { resize: vertical; min-height: 120px; }
.form-button:hover { opacity: 0.9; }
.button-icon { width: 18px; height: 18px; }
</style>
