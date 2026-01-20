<template>
  <div class="lottie-block" :style="containerStyles">
    <div class="lottie-wrapper" :style="wrapperStyles">
      <div class="lottie-player" :style="playerStyles">
        <!-- Lottie animation placeholder -->
        <div class="lottie-placeholder">
          <Film class="placeholder-icon" />
          <span>Lottie Animation</span>
          <small v-if="settings.animationUrl">{{ settings.animationUrl }}</small>
          <small v-else>Add animation URL or JSON</small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Film } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
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

const containerStyles = computed(() => {
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

const wrapperStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  return {
    display: 'flex',
    width: '100%',
    justifyContent: alignment === 'left' ? 'flex-start' : 
                    alignment === 'right' ? 'flex-end' : 'center'
  }
})

const playerStyles = computed(() => {
  const width = getResponsiveValue(settings.value, 'width', device.value) || 300
  const height = getResponsiveValue(settings.value, 'height', device.value) || 300
  const maxWidth = getResponsiveValue(settings.value, 'maxWidth', device.value) || 100
  return {
    width: `${width}px`,
    height: `${height}px`,
    maxWidth: `${maxWidth}%`
  }
})
</script>

<style scoped>
.lottie-block { width: 100%; }
.lottie-wrapper { width: 100%; }
.lottie-player { display: flex; align-items: center; justify-content: center; }
.lottie-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border-radius: 12px;
  color: white;
  gap: 8px;
}
.placeholder-icon { width: 48px; height: 48px; opacity: 0.8; }
.lottie-placeholder span { font-weight: 600; font-size: 16px; }
.lottie-placeholder small { opacity: 0.7; font-size: 12px; max-width: 80%; text-align: center; word-break: break-all; }
</style>
