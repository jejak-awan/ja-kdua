<template>
  <div class="spacer-block" :style="spacerStyles">
    <div class="spacer-label">
      <ArrowUpDown class="spacer-icon" />
      <span>{{ height }}px</span>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { ArrowUpDown } from 'lucide-vue-next'
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
  module: {
    type: Object,
    required: true
  }
})

const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const height = computed(() => {
    return getResponsiveValue(settings.value, 'height', device.value) || 50
})

const spacerStyles = computed(() => {
  const h = height.value
  const styles = {
    height: `${h}px`,
    minHeight: `${h}px`
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
</script>

<style scoped>
.spacer-block {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0, 0, 0, 0.03) 10px,
    rgba(0, 0, 0, 0.03) 20px
  );
  border: 1px dashed rgba(0, 0, 0, 0.1);
  transition: height 0.2s ease;
}

.spacer-label {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 4px;
  font-size: 11px;
  color: #666;
  pointer-events: none;
}

.spacer-icon {
  width: 12px;
  height: 12px;
  opacity: 0.5;
}
</style>
