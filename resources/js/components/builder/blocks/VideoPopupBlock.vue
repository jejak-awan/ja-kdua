<template>
  <div class="video-popup-block" :style="wrapperStyles">
    <div class="video-popup-container" :style="containerStyles">
      <div class="video-popup-overlay" :style="overlayStyles" />
      <button class="video-popup-button" :style="buttonStyles" @click="openPopup">
        <Play class="play-icon" :style="iconStyles" />
        <span v-if="settings.buttonStyle === 'text'" class="button-text" :style="buttonTextStyles">{{ settings.buttonText || 'Watch Video' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Play } from 'lucide-vue-next'
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
const settings = computed(() => props.module.settings || {})
const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const openPopup = () => {
  // In a real implementation, this would open a video modal
  // TODO: Implement video modal
}

const iconSizeValue = computed(() => getResponsiveValue(settings.value, 'iconSize', device.value) || 80)
const iconColorValue = computed(() => getResponsiveValue(settings.value, 'iconColor', device.value) || '#ffffff')
const iconBackgroundColorValue = computed(() => getResponsiveValue(settings.value, 'iconBackgroundColor', device.value) || 'rgba(32, 89, 234, 0.9)')
const overlayColorValue = computed(() => getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0, 0, 0, 0.4)')

const wrapperStyles = computed(() => {
  const s = { width: '100%' }
  Object.assign(s, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(s, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(s, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(s, getSizingStyles(settings.value, device.value)) // Handles outer sizing
  Object.assign(s, getFilterStyles(settings.value, device.value))
  Object.assign(s, getTransformStyles(settings.value, device.value))
  return s
})

const containerStyles = computed(() => {
  const s = {
    position: 'relative', 
    height: getResponsiveValue(settings.value, 'height', device.value) ? `${getResponsiveValue(settings.value, 'height', device.value)}px` : '400px', // Fallback if sizing not set on wrapper? Or here?
    // Actually sizing usually applies to wrapper. But for video popup, usually the container has the aspect ratio or height.
    // If we rely on getSizingStyles for wrapper, we might need height there. 
    // But let's keep height logic here if not present in sizing.
    display: 'flex', alignItems: 'center', justifyContent: 'center', overflow: 'hidden',
    backgroundImage: settings.value.thumbnailImage ? `url(${settings.value.thumbnailImage})` : undefined,
    backgroundSize: 'cover', backgroundPosition: 'center'
  }
  
  if (!s.backgroundImage) {
      // Fallback gradient if no image and no background set?
      // Check if backgroundSettings provides background.
      const bg = getBackgroundStyles(settings.value)
      if (bg.background || bg.backgroundColor || bg.backgroundImage) {
          Object.assign(s, bg)
      } else {
          s.backgroundImage = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
      }
  } else {
      // If thumbnail exists, we might still want other bg props?
      const bg = getBackgroundStyles(settings.value)
      if (bg.backgroundColor) s.backgroundColor = bg.backgroundColor
  }

  Object.assign(s, getBorderStyles(settings.value, 'border', device.value))
  return s
})

const overlayStyles = computed(() => ({ 
    position: 'absolute', 
    inset: 0, 
    backgroundColor: overlayColorValue.value 
}))

const buttonStyles = computed(() => ({
  position: 'relative', zIndex: 1, display: 'flex', alignItems: 'center', gap: '12px',
  background: iconBackgroundColorValue.value,
  border: 'none', 
  borderRadius: settings.value.buttonStyle === 'text' ? '8px' : '50%',
  padding: settings.value.buttonStyle === 'text' ? '16px 32px' : '0',
  width: settings.value.buttonStyle !== 'text' ? `${iconSizeValue.value}px` : 'auto',
  height: settings.value.buttonStyle !== 'text' ? `${iconSizeValue.value}px` : 'auto',
  minWidth: settings.value.buttonStyle !== 'text' ? `${iconSizeValue.value}px` : 'auto', // Ensure circle shape
  justifyContent: 'center',
  cursor: 'pointer', transition: 'transform 0.2s'
}))

const iconStyles = computed(() => ({ 
    width: `${iconSizeValue.value * 0.4}px`, 
    height: `${iconSizeValue.value * 0.4}px`, 
    color: iconColorValue.value 
}))

const buttonTextStyles = computed(() => getTypographyStyles(settings.value, 'button_', device.value))
</script>

<style scoped>
.video-popup-block { width: 100%; }
.video-popup-button:hover { transform: scale(1.05); }
.button-text { color: white; font-size: 16px; font-weight: 600; }
</style>
