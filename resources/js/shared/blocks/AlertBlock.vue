<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :device="device"
    class="alert-block"
  >
    <template #default="{ settings, device: blockDevice }">
      <div 
        v-if="!dismissed" 
        class="w-full"
        :id="getVal(settings, 'html_id', blockDevice)"
        :style="containerStyles(settings, blockDevice)"
      >
        <Alert 
           class="transition-colors duration-300 relative overflow-hidden"
           :class="[
             alertClasses(settings, blockDevice),
             getVal(settings, 'class')
           ]"
           :style="alertStyles(settings, blockDevice)"
           :aria-label="getVal(settings, 'aria_label', blockDevice) || undefined"
           :role="getVal(settings, 'aria_label', blockDevice) ? 'alert' : undefined"
        >
          <component 
            v-if="getVal(settings, 'showIcon', blockDevice) !== false"
            :is="getAlertIcon(settings, blockDevice)" 
            class="h-4 w-4 shrink-0" 
            :style="{ color: getVal(settings, 'iconColor') || themeColors(settings, blockDevice).icon }"
          />
          
          <div class="flex-1 min-w-0">
            <AlertTitle 
              v-if="mode === 'edit' || getVal(settings, 'title', blockDevice)"
              :contenteditable="mode === 'edit'"
              @blur="onTitleBlur($event)"
              class="mb-2 transition-colors duration-300"
              :style="getTypographyStyles(settings, 'title_', blockDevice)"
              v-html="getVal(settings, 'title', blockDevice) || (mode === 'edit' ? 'Alert Title' : '')"
            />
            
            <AlertDescription 
              :contenteditable="mode === 'edit'"
              @blur="onMessageBlur($event)"
              class="transition-colors duration-300"
              :style="getTypographyStyles(settings, 'message_', blockDevice)"
              v-html="getVal(settings, 'message', blockDevice) || (mode === 'edit' ? 'Alert message goes here...' : '')"
            />
          </div>

          <button 
            v-if="getVal(settings, 'dismissible', blockDevice)" 
            class="absolute right-4 top-4 rounded-sm hover:opacity-100 opacity-60 transition-opacity"
            @click="dismissed = true"
            aria-label="Dismiss alert"
          >
            <X class="h-4 w-4" />
          </button>
        </Alert>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import XCircle from 'lucide-vue-next/dist/esm/icons/circle-x.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';import BaseBlock from '../components/BaseBlock.vue'
import { Alert, AlertTitle, AlertDescription } from '../ui'
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles 
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, BlockProps } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)
const dismissed = ref(false)

const getAlertIcon = (settings: any, device: string) => {
  const variant = getVal(settings, 'variant', device) || 'info'
  const icons: Record<string, any> = { 
    info: Info, 
    success: CheckCircle, 
    warning: AlertTriangle, 
    error: XCircle,
    default: Info,
    destructive: XCircle
  }
  return icons[variant] || Info
}

const themeColors = (settings: any, device: string) => {
  const variant = getVal(settings, 'variant', device) || 'info'
  const colors: Record<string, any> = {
    info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-900', icon: 'text-blue-600' },
    success: { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-900', icon: 'text-green-600' },
    warning: { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-900', icon: 'text-yellow-600' },
    error: { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-900', icon: 'text-red-600' },
    default: { bg: 'bg-background', border: 'border-border', text: 'text-foreground', icon: 'text-foreground' },
    destructive: { bg: 'bg-destructive/15', border: 'border-destructive/50', text: 'text-destructive', icon: 'text-destructive' }
  }
  return colors[variant] || colors.info
}

const alertClasses = (settings: any, device: string) => {
    const colors = themeColors(settings, device)
    return `${colors.bg} ${colors.border} ${colors.text}`
}

const containerStyles = (settings: any, device: string) => {
  return {
    ...getLayoutStyles(settings, device)
  }
}

const alertStyles = (settings: any, device: string) => {
    const style: Record<string, any> = {
      '--hover-opacity': getVal(settings, 'hover_opacity', device) ?? 1
    }
    return style
}

const onTitleBlur = (e: any) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { title: e.target.innerText })
}

const onMessageBlur = (e: any) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { message: e.target.innerText })
}
</script>

<style scoped>
.alert-block { width: 100%; }

.alert-block :deep(.alert) {
  will-change: opacity, transform;
}

.alert-block :deep(.alert:hover) {
  opacity: var(--hover-opacity);
}
</style>
