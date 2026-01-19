<template>
  <div 
    class="contact-field-block"
    :class="{ 'field--textarea': settings.type === 'textarea' }"
    :style="itemStyles"
  >
      <label class="field-label" :style="labelStyles">
        {{ settings.label || 'Field Label' }}
        <span v-if="settings.required" class="field-required">*</span>
      </label>
      
      <textarea 
        v-if="settings.type === 'textarea'"
        class="field-input field-textarea"
        :placeholder="settings.placeholder"
        rows="5"
        disabled
      />
      <input 
        v-else
        :type="settings.type || 'text'"
        class="field-input"
        :placeholder="settings.placeholder"
        disabled
      />
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { getTypographyStyles } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from ContactFormBlock
const contactFormState = inject('contactFormState', {
    parentSettings: {}
})
const parentSettings = computed(() => contactFormState.parentSettings.value || {})

const labelStyles = computed(() => {
  return getTypographyStyles(parentSettings.value, 'label_', device.value)
})

const itemStyles = computed(() => {
    // We could add grid column spanning here if we supported width settings
    return {
        gridColumn: settings.value.type === 'textarea' ? '1 / -1' : 'auto'
    }
})
</script>

<style scoped>
.contact-field-block { display: flex; flex-direction: column; gap: 8px; width: 100%; }
.field-label { display: block; margin-bottom: 4px; font-weight: 500; }
.field-required { color: #ef4444; }
.field-input { 
    padding: 12px 16px; 
    border: 1px solid #e0e0e0; 
    border-radius: 6px; 
    outline: none; 
    width: 100%;
    background-color: #f9fafb;
    cursor: default;
}
.field-textarea { resize: vertical; min-height: 120px; }
</style>
