<template>
  <div 
    class="fullwidth-slide-item-block" 
    :class="{ 'slider-slide--active': isActive }"
    :style="slideStyles"
  >
    <div class="slide-overlay" :style="overlayStyles" />
    <div class="slide-content" :style="contentStyles">
      <h2 class="slide-title" :style="titleStyles">{{ settings.title || 'Slide Title' }}</h2>
      <p v-if="settings.subtitle" class="slide-subtitle" :style="subtitleStyles">{{ settings.subtitle }}</p>
      <a v-if="settings.buttonText" :href="settings.buttonUrl || '#'" class="slide-button" :style="buttonStyles">{{ settings.buttonText }}</a>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { getTypographyStyles, getResponsiveValue, getBackgroundStyles } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  index: { type: Number, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from FullwidthSliderBlock
const fullwidthSliderState = inject('fullwidthSliderState', {
    parentSettings: {},
    currentSlide: ref(0)
})
const parentSettings = computed(() => fullwidthSliderState.parentSettings.value || {})
const isActive = computed(() => fullwidthSliderState.currentSlide.value === props.index)

const slideStyles = computed(() => {
    const styles = {
        position: 'absolute',
        inset: 0,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'opacity 0.5s',
        opacity: isActive.value ? 1 : 0,
        zIndex: isActive.value ? 1 : 0,
        backgroundColor: '#333' // Default bg
    }
    
    // Slide specific background
    if (settings.value.backgroundImage) {
        styles.backgroundImage = `url(${settings.value.backgroundImage})`
        styles.backgroundSize = 'cover'
        styles.backgroundPosition = 'center'
    } else if (settings.value.backgroundColor) {
        styles.backgroundColor = settings.value.backgroundColor
    }
    
    return styles
})

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: parentSettings.value.overlayColor || 'rgba(0,0,0,0.4)',
  zIndex: 1
}))

const contentStyles = computed(() => ({ 
  position: 'relative', 
  zIndex: 2, 
  textAlign: getResponsiveValue(parentSettings.value, 'contentAlignment', device.value) || 'center', 
  width: '100%',
  maxWidth: '1200px', 
  margin: '0 auto',
  padding: '0 20px'
}))

const titleStyles = computed(() => getTypographyStyles(parentSettings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(parentSettings.value, 'subtitle_', device.value))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'button_', device.value)
  return {
    ...styles,
    display: 'inline-block',
    padding: '14px 32px',
    backgroundColor: parentSettings.value.buttonBackgroundColor || styles.backgroundColor || '#fff',
    color: parentSettings.value.buttonTextColor || styles.color || '#333',
    textDecoration: 'none',
    borderRadius: '6px',
    fontWeight: styles.fontWeight || 600,
    marginTop: '24px'
  }
})

</script>

<style scoped>
.fullwidth-slide-item-block { width: 100%; height: 100%; }
.slide-subtitle { margin: 16px 0 0; font-size: 20px; opacity: 0.9; }
.slide-button { transition: transform 0.2s; }
.slide-button:hover { transform: translateY(-2px); }
</style>
