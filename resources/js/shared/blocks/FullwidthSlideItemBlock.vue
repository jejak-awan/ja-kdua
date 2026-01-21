<template>
  <div 
    class="fullwidth-slide-item-block" 
    :class="{ 'slider-slide--active': isActive }"
    :style="slideStyles"
  >
    <div class="slide-overlay" :style="overlayStyles" />
    <div class="slide-content" :style="contentStyles">
      <h2 
        class="slide-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ settings.title || 'Slide Title' }}</h2>
      
      <p 
        v-if="settings.subtitle || mode === 'edit'" 
        class="slide-subtitle" 
        :style="subtitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('subtitle', $event)"
      >{{ settings.subtitle || 'Slide subtitle goes here' }}</p>
      
      <div v-if="settings.buttonText || mode === 'edit'" class="mt-8">
        <a 
          :href="settings.buttonUrl || '#'" 
          class="slide-button" 
          :style="buttonStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateText('buttonText', $event)"
          @click.prevent="handleLinkClick"
        >{{ settings.buttonText || 'Learn More' }}</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { getTypographyStyles, getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  index: { type: Number, required: true }
})

const builder = inject('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from FullwidthSliderBlock
const fullwidthSliderState = inject('fullwidthSliderState', {
    parentSettings: computed(() => ({})),
    currentSlide: computed(() => 0),
    mode: computed(() => 'view')
})

const parentSettings = computed(() => fullwidthSliderState.parentSettings.value || {})
const isActive = computed(() => fullwidthSliderState.currentSlide.value === props.index)
const mode = computed(() => fullwidthSliderState.mode.value)

const updateText = (key, event) => {
    if (mode.value !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = () => {
    if (mode.value === 'edit') return
    if (settings.value.buttonUrl) window.location.href = settings.value.buttonUrl
}

const slideStyles = computed(() => {
    const styles = {
        position: 'absolute',
        inset: 0,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'opacity 0.5s ease-in-out',
        opacity: isActive.value || mode.value === 'edit' ? 1 : 0,
        zIndex: isActive.value ? 10 : 1,
        backgroundColor: '#333'
    }
    
    // Slide specific background
    if (settings.value.backgroundImage) {
        styles.backgroundImage = `url(${settings.value.backgroundImage})`
        styles.backgroundSize = 'cover'
        styles.backgroundPosition = 'center'
    } else if (settings.value.backgroundColor) {
        styles.backgroundColor = settings.value.backgroundColor
    }
    
    // If in edit mode and not active, we might want to still show it but dimmed? 
    // Actually FullwidthSlider handles the absolute positioning and z-index.
    // In builder, usually only the active slide is shown.
    
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

const titleStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'title_', device.value)
  return {
    ...styles,
    margin: 0
  }
})

const subtitleStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'subtitle_', device.value)
  return {
    ...styles,
    marginTop: '16px'
  }
})

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
    transition: 'transform 0.2s',
  }
})
</script>

<style scoped>
.fullwidth-slide-item-block { width: 100%; height: 100%; }
.slide-button:hover { transform: translateY(-2px); }
[contenteditable]:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>
