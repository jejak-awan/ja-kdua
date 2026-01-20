<template>
  <div class="newsletter-block" :style="wrapperStyles">
    <div v-if="settings.title" class="newsletter-header">
      <h3 class="newsletter-title" :style="titleStyles">{{ settings.title }}</h3>
      <p v-if="settings.subtitle" class="newsletter-subtitle" :style="subtitleStyles">{{ settings.subtitle }}</p>
    </div>
    <div class="newsletter-form" :class="`newsletter-form--${layoutClass}`">
      <input type="email" class="newsletter-input" :style="inputStyles" :placeholder="settings.placeholder || 'Enter your email'" />
      <button class="newsletter-button" :style="buttonStyles">{{ settings.buttonText || 'Subscribe' }}</button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
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

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { width: '100%', textAlign: 'center' }
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))

const inputStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'input_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.inputBackgroundColor || '#ffffff'
    }
})

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#2059ea'
    }
})
const layoutClass = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'inline')
</script>

<style scoped>
.newsletter-block { width: 100%; }
.newsletter-header { margin-bottom: 20px; }
.newsletter-title { margin: 0 0 8px; }
.newsletter-subtitle { margin: 0; opacity: 0.7; }
.newsletter-form--inline { display: flex; gap: 12px; justify-content: center; max-width: 500px; margin: 0 auto; }
.newsletter-form--stacked { display: flex; flex-direction: column; gap: 12px; max-width: 400px; margin: 0 auto; }
.newsletter-input { flex: 1; padding: 14px 20px; border: 1px solid #e0e0e0; border-radius: 6px; outline: none; }
.newsletter-button { padding: 14px 28px; border: none; border-radius: 6px; cursor: pointer; white-space: nowrap; }
</style>
