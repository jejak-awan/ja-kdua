<template>
  <div class="fullwidth-header-block" :style="wrapperStyles">
    <div v-if="settings.overlayColor" class="header-overlay" :style="overlayStyles" />
    <div class="header-content" :style="contentStyles">
      <h1 class="header-title" :style="titleStyles">{{ settings.title || 'Welcome' }}</h1>
      <p v-if="settings.subtitle" class="header-subtitle" :style="subtitleStyles">{{ settings.subtitle }}</p>
      <div class="header-buttons" :style="{ justifyContent: getResponsiveValue(settings, 'textAlignment', device) === 'right' ? 'flex-end' : getResponsiveValue(settings, 'textAlignment', device) === 'center' ? 'center' : 'flex-start' }">
        <a :href="settings.buttonUrl || '#'" class="header-button header-button--primary" :style="button1Styles">{{ settings.buttonText || 'Get Started' }}</a>
        <a v-if="settings.showButton2 !== false" :href="settings.button2Url || '#'" class="header-button header-button--secondary" :style="button2Styles">{{ settings.button2Text || 'Learn More' }}</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
  getTypographyStyles,
  getBorderStyles,
  getBoxShadowStyles,
  getSizingStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    display: 'flex', 
    flexDirection: 'column',
    overflow: 'hidden',
    minHeight: `${getResponsiveValue(settings.value, 'height', device.value) || 400}px`,
    textAlign: getResponsiveValue(settings.value, 'textAlignment', device.value) || 'center'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  const vAlign = settings.value.contentAlignment || 'center'
  styles.justifyContent = vAlign === 'top' ? 'flex-start' : vAlign === 'bottom' ? 'flex-end' : 'center'
  return styles
})

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'textAlignment', device.value) || 'center'
  return { 
    position: 'relative', 
    zIndex: 2, 
    width: '100%',
    maxWidth: '1200px',
    margin: alignment === 'center' ? '0 auto' : alignment === 'right' ? '0 0 0 auto' : '0 auto 0 0'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))

const button1Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button1_', device.value)
  return {
    ...styles,
    backgroundColor: settings.value.buttonBackgroundColor || styles.backgroundColor
  }
})

const button2Styles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button2_', device.value)
  return {
    ...styles,
    backgroundColor: settings.value.button2BackgroundColor || styles.backgroundColor
  }
})

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: settings.value.overlayColor,
  display: settings.value.overlayColor ? 'block' : 'none'
}))
</script>

<style scoped>
.fullwidth-header-block { width: 100%; }
.header-overlay { position: absolute; inset: 0; }
.header-buttons { display: flex; gap: 16px; justify-content: inherit; flex-wrap: wrap; margin-top: 24px; }
.header-button { display: inline-block; padding: 14px 32px; text-decoration: none; border-radius: 6px; transition: transform 0.2s, opacity 0.2s; }
.header-button:hover { transform: translateY(-2px); opacity: 0.9; }
</style>
