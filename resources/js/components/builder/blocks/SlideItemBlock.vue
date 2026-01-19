<template>
  <div 
    class="slider-slide-block"
    :class="{ 'slider-slide-block--active': isActive }"
    :style="slideStyles"
  >
    <!-- Overlay -->
    <div v-if="overlayEnabled" class="slider-overlay" :style="overlayStyles" />
    
    <!-- Content -->
    <div class="slider-content" :style="contentStyles">
      <h2 v-if="settings.title" class="slider-title" :style="titleStyles">{{ settings.title }}</h2>
      <div v-if="settings.content" class="slider-text" :style="textStyles" v-html="settings.content"></div>
      <a v-if="settings.buttonText" :href="settings.buttonUrl" class="slider-button" :style="buttonStyles">{{ settings.buttonText }}</a>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({
  module: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    default: 0
  }
})

const settings = computed(() => props.module.settings || {})
const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

// Injected from SliderBlock
const sliderState = inject('sliderState', {
    currentSlide: 0,
    parentSettings: {}
})

const isActive = computed(() => sliderState.currentSlide === props.index)
const parentSettings = computed(() => sliderState.parentSettings || {})
const overlayEnabled = computed(() => parentSettings.value.overlayEnabled !== false)

const slideStyles = computed(() => ({
  backgroundImage: settings.value.image ? `url(${settings.value.image})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  position: 'absolute',
  inset: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  opacity: isActive.value ? 1 : 0,
  transition: 'opacity var(--transition-premium)',
  pointerEvents: isActive.value ? 'auto' : 'none'
}))

const overlayStyles = computed(() => ({
  backgroundColor: parentSettings.value.overlayColor || 'rgba(0,0,0,0.4)',
  position: 'absolute',
  inset: 0
}))

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(parentSettings.value, 'alignment', device.value) || 'center'
  return {
    position: 'relative',
    zIndex: 1,
    textAlign: alignment,
    maxWidth: '800px',
    padding: '40px'
  }
})

const titleStyles = computed(() => getTypographyStyles(parentSettings.value, 'title_', device.value))
const textStyles = computed(() => getTypographyStyles(parentSettings.value, 'content_', device.value))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(parentSettings.value, 'button_', device.value)
    return {
        ...styles,
        display: 'inline-block',
        marginTop: '20px',
        padding: '10px 20px',
        backgroundColor: parentSettings.value.buttonBackgroundColor || styles.color || '#ffffff',
        textDecoration: 'none',
        borderRadius: '4px'
    }
})
</script>

<style scoped>
.slider-slide-block { width: 100%; height: 100%; position: absolute; }
.slider-title { 
  margin: 0 0 16px; 
  font-weight: 700; 
  transition: var(--transition-premium);
  transform: translateY(20px);
  opacity: 0;
}
.slider-slide-block--active .slider-title {
  transform: translateY(0);
  opacity: 1;
}
.slider-text { 
  margin: 0; 
  opacity: 0; 
  transition: var(--transition-premium);
  transition-delay: 0.1s;
  transform: translateY(20px);
}
.slider-slide-block--active .slider-text {
  opacity: 0.9;
  transform: translateY(0);
}
.slider-button {
  transition: var(--transition-premium);
  transition-delay: 0.2s;
  transform: translateY(20px);
  opacity: 0;
}
.slider-slide-block--active .slider-button {
  transform: translateY(0);
  opacity: 1;
}
</style>
