<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="alert-block"
  >
    <div 
      v-if="!dismissed" 
      class="alert-container" 
      :class="[`alert--${alertType}`]"
      :style="alertStyles"
    >
      <component :is="alertIcon" v-if="showIcon" class="alert-icon" />
      
      <div class="alert-content">
        <div 
          v-if="hasTitle" 
          class="alert-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('title', $event.target.innerText)"
          v-text="getVal(settings, 'title')"
        ></div>
        
        <div 
          class="alert-message" 
          :style="messageStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('message', $event.target.innerText)"
          v-text="getVal(settings, 'message')"
        ></div>
      </div>
      
      <button 
        v-if="getVal(settings, 'dismissible')" 
        class="alert-dismiss" 
        @click="dismissed = true"
      >
        <X class="dismiss-icon" />
      </button>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Info, CheckCircle, AlertTriangle, XCircle, X } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)
const dismissed = ref(false)

const alertType = computed(() => getVal(props.settings, 'variant') || 'info')
const showIcon = computed(() => getVal(props.settings, 'showIcon') !== false)

const alertIcon = computed(() => {
  const icons = { 
    info: Info, 
    success: CheckCircle, 
    warning: AlertTriangle, 
    error: XCircle 
  }
  return icons[alertType.value] || Info
})

const hasTitle = computed(() => {
  if (props.mode === 'edit') return true
  return !!getVal(props.settings, 'title')
})

const themeColors = computed(() => {
  const colors = {
    info: { bg: 'var(--theme-info-bg, #eff6ff)', border: 'var(--theme-info-color, #3b82f6)', text: 'var(--theme-info-text, #1e40af)' },
    success: { bg: 'var(--theme-success-bg, #f0fdf4)', border: 'var(--theme-success-color, #22c55e)', text: 'var(--theme-success-text, #166534)' },
    warning: { bg: 'var(--theme-warning-bg, #fffbeb)', border: 'var(--theme-warning-color, #f59e0b)', text: 'var(--theme-warning-text, #92400e)' },
    error: { bg: 'var(--theme-error-bg, #fef2f2)', border: 'var(--theme-error-color, #ef4444)', text: 'var(--theme-error-text, #991b1b)' }
  }
  return colors[alertType.value] || colors.info
})

const alertStyles = computed(() => ({
  backgroundColor: themeColors.value.bg,
  color: themeColors.value.text,
  borderLeft: `4px solid ${themeColors.value.border}`
}))

const titleStyles = computed(() => getTypographyStyles(props.settings, 'title_'))
const messageStyles = computed(() => getTypographyStyles(props.settings, 'message_'))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.alert-container {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  width: 100%;
  padding: 16px;
  border-radius: 4px;
}

.alert-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.alert-content {
  flex: 1;
}

.alert-title {
  margin-bottom: 4px;
  font-weight: 700;
}

.alert-message {
  font-size: 0.875rem;
  line-height: 1.5;
}

.alert-dismiss {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  opacity: 0.6;
  transition: opacity 0.2s;
  color: inherit;
  margin-left: auto;
}

.alert-dismiss:hover {
  opacity: 1;
}

.dismiss-icon {
  width: 16px;
  height: 16px;
}

/* Edit mode overrides */
.alert-title[contenteditable="true"]:empty:before {
  content: 'Alert Title';
  opacity: 0.5;
}
.alert-message[contenteditable="true"]:empty:before {
  content: 'Alert message goes here...';
  opacity: 0.5;
}
</style>
