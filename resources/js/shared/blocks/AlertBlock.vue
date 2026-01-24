<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :device="device"
    class="alert-block"
  >
    <template #default="{ settings }">
      <div v-if="!dismissed" class="w-full">
        <Alert 
           :class="[
             alertClasses(settings),
             getVal(settings, 'class')
           ]"
           :style="alertStyles(settings)"
        >
          <component 
            v-if="getVal(settings, 'showIcon') !== false"
            :is="getAlertIcon(settings)" 
            class="h-4 w-4" 
            :style="{ color: getVal(settings, 'iconColor') || textColors(settings).icon }"
          />
          
          <div class="flex-1">
            <AlertTitle 
              v-if="mode === 'edit' || getVal(settings, 'title')"
              :contenteditable="mode === 'edit'"
              @blur="onTitleBlur($event, settings)"
              class="mb-2"
              :style="getTypographyStyles(settings, 'title_')"
              v-html="getVal(settings, 'title') || (mode === 'edit' ? 'Alert Title' : '')"
            />
            
            <AlertDescription 
              :contenteditable="mode === 'edit'"
              @blur="onMessageBlur($event, settings)"
              :style="getTypographyStyles(settings, 'message_')"
              v-html="getVal(settings, 'message') || (mode === 'edit' ? 'Alert message goes here...' : '')"
            />
          </div>

          <button 
            v-if="getVal(settings, 'dismissible')" 
            class="absolute right-4 top-4 rounded-sm hover:opacity-100 opacity-60 transition-opacity"
            @click="dismissed = true"
          >
            <X class="h-4 w-4" />
          </button>
        </Alert>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Info, CheckCircle, AlertTriangle, XCircle, X } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Alert, AlertTitle, AlertDescription } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const dismissed = ref(false)

const getAlertIcon = (settings) => {
  const variant = getVal(settings, 'variant') || 'info'
  const icons = { 
    info: Info, 
    success: CheckCircle, 
    warning: AlertTriangle, 
    error: XCircle,
    default: Info,
    destructive: XCircle
  }
  return icons[variant] || Info
}

const themeColors = (settings) => {
  const variant = getVal(settings, 'variant') || 'info'
  const colors = {
    info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-900', icon: 'text-blue-600' },
    success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-900', icon: 'text-green-600' },
    warning: { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-900', icon: 'text-yellow-600' },
    error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-900', icon: 'text-red-600' },
    default: { bg: 'bg-background', border: 'border-border', text: 'text-foreground', icon: 'text-foreground' },
    destructive: { bg: 'bg-destructive/15', border: 'border-destructive/50', text: 'text-destructive', icon: 'text-destructive' }
  }
  return colors[variant] || colors.info
}

const textColors = (settings) => {
    return themeColors(settings)
}

const alertClasses = (settings) => {
    const colors = themeColors(settings)
    return `${colors.bg} ${colors.border} ${colors.text}`
}

const alertStyles = (settings) => {
    // Override with manual styles if provided in settings (advanced)
    return {}
}

const updateSettings = (settings, key, value) => {
  if (props.mode !== 'edit' || !builder) return
  // This logic assumes we can update the module. In standard BaseBlock pattern, we might need a specific update method
  // But usually we can trigger an update via builder
  builder.updateModuleSettings(props.module.id, { [key]: value })
}

const onTitleBlur = (e, settings) => {
    updateSettings(settings, 'title', e.target.innerText)
}

const onMessageBlur = (e, settings) => {
    updateSettings(settings, 'message', e.target.innerText)
}
</script>

<style scoped>
.alert-block { width: 100%; }
</style>
