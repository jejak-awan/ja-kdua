<template>
  <div class="before-after-block" :style="wrapperStyles">
    <div class="comparison-container" :style="containerStyles" @mousemove="handleMove" @touchmove="handleTouch">
      <!-- Before Image -->
      <div class="comparison-before" :style="beforeStyles">
        <div v-if="!settings.beforeImage" class="placeholder-image"><ImageIcon /> Before</div>
        <span v-if="settings.showLabels !== false" class="comparison-label comparison-label--before" :style="labelStyles">{{ settings.beforeLabel || 'Before' }}</span>
      </div>
      
      <!-- After Image -->
      <div class="comparison-after">
        <div v-if="!settings.afterImage" class="placeholder-image"><ImageIcon /> After</div>
        <span v-if="settings.showLabels !== false" class="comparison-label comparison-label--after" :style="labelStyles">{{ settings.afterLabel || 'After' }}</span>
      </div>
      
      <!-- Slider -->
      <div class="comparison-slider" :style="sliderStyles">
        <div class="slider-line" :style="sliderLineStyles" />
        <div class="slider-handle" :style="handleStyles"><GripVertical /></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Image as ImageIcon, GripVertical } from 'lucide-vue-next'
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

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const position = ref(getResponsiveValue(settings.value, 'sliderPosition', device.value) || 50)

const handleMove = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  position.value = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100))
}

const handleTouch = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const touch = e.touches[0]
  position.value = Math.max(0, Math.min(100, ((touch.clientX - rect.left) / rect.width) * 100))
}

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
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

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 400
  const styles = { 
    position: 'relative', 
    height: `${height}px`, 
    overflow: 'hidden', 
    cursor: 'ew-resize', 
    background: '#f0f0f0' 
  }
  if (settings.value.afterImage) { 
    styles.backgroundImage = `url(${settings.value.afterImage})`
    styles.backgroundSize = 'cover'
    styles.backgroundPosition = 'center' 
  }
  return styles
})

const beforeStyles = computed(() => {
  const styles = { 
    position: 'absolute', 
    inset: 0, 
    width: `${position.value}%`, 
    overflow: 'hidden', 
    background: '#e0e0e0',
    zIndex: 1
  }
  if (settings.value.beforeImage) { 
    styles.backgroundImage = `url(${settings.value.beforeImage})`
    styles.backgroundSize = 'cover'
    styles.backgroundPosition = 'center' 
  }
  return styles
})

const sliderStyles = computed(() => ({ 
  position: 'absolute', 
  left: `${position.value}%`, 
  top: 0, 
  bottom: 0, 
  transform: 'translateX(-50%)', 
  display: 'flex', 
  flexDirection: 'column', 
  alignItems: 'center', 
  justifyContent: 'center',
  zIndex: 10
}))

const sliderLineStyles = computed(() => {
  const sliderWidth = getResponsiveValue(settings.value, 'sliderWidth', device.value) || 4
  return { 
    width: `${sliderWidth}px`, 
    height: '100%', 
    backgroundColor: settings.value.sliderColor || '#ffffff', 
    boxShadow: '0 0 4px rgba(0,0,0,0.3)' 
  }
})

const handleStyles = computed(() => ({ 
  position: 'absolute', 
  width: '40px', 
  height: '40px', 
  borderRadius: '50%', 
  backgroundColor: settings.value.sliderColor || '#ffffff', 
  display: 'flex', 
  alignItems: 'center', 
  justifyContent: 'center', 
  boxShadow: '0 2px 8px rgba(0,0,0,0.3)', 
  color: '#333' 
}))

const labelStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'label_', device.value)
  return {
    ...styles,
    position: 'absolute', 
    bottom: '16px', 
    padding: '6px 12px', 
    backgroundColor: styles.backgroundColor || 'rgba(0,0,0,0.6)', 
    textTransform: 'uppercase', 
    borderRadius: '4px'
  }
})
</script>

<style scoped>
.before-after-block { width: 100%; }
.placeholder-image { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #999; gap: 8px; }
.comparison-label--before { left: 16px; }
.comparison-label--after { right: 16px; }
</style>
