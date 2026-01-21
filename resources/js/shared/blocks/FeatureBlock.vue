<template>
  <BaseBlock :module="module" :settings="settings" class="feature-block">
    <div 
        class="feature-inner flex transition-all duration-300 group" 
        :class="[
            layout === 'top' ? 'flex-col items-center text-center gap-6' : 
            layout === 'right' ? 'flex-row-reverse items-start gap-6' : 
            'flex-row items-start gap-6',
            alignment === 'left' ? 'text-left' : (alignment === 'right' ? 'text-right' : 'text-center')
        ]"
    >
      <div class="feature-icon-wrap relative flex-shrink-0" :style="iconWrapperStyles">
        <div class="icon-glow absolute inset-0 opacity-20 blur-xl group-hover:opacity-40 transition-opacity" :style="{ backgroundColor: settings.iconColor || '#3b82f6' }"></div>
        <div class="icon-container relative z-10 flex items-center justify-center" :style="iconStyles">
          <component :is="iconComponent" class="w-full h-full transition-transform duration-500 group-hover:scale-110" />
        </div>
      </div>
      
      <div class="feature-content flex-1 pt-1">
        <h4 class="feature-title text-xl font-bold mb-3 tracking-tight" :style="titleStyles">{{ settings.title || 'Feature Title' }}</h4>
        <p class="feature-description opacity-70 leading-relaxed text-sm" :style="descriptionStyles">{{ settings.description || 'Describe the unique value of this feature or service here.' }}</p>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, defineAsyncComponent, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import * as LucideIcons from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const iconComponent = computed(() => {
  const iconName = settings.value.icon || 'Zap'
  return LucideIcons[iconName] || LucideIcons.Zap
})

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'top')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')

const iconWrapperStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 48
  const bgColor = getResponsiveValue(settings.value, 'iconBackgroundColor', device.value) || 'rgba(59, 130, 246, 0.1)'
  const borderRadius = getResponsiveValue(settings.value, 'iconBorderRadius', device.value) || 24
  
  return {
    width: `${size + 32}px`, 
    height: `${size + 32}px`,
    backgroundColor: bgColor,
    borderRadius: typeof borderRadius === 'string' ? borderRadius : `${borderRadius}px`,
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center'
  }
})

const iconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 48
  const color = getResponsiveValue(settings.value, 'iconColor', device.value) || '#3b82f6'
  return { 
    width: `${size}px`, 
    height: `${size}px`, 
    color: color
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.feature-block { width: 100%; }
</style>
