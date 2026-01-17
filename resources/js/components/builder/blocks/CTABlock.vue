<template>
  <div class="cta-block" :style="wrapperStyles">
    <div class="cta-content" :style="contentStyles">
      <h2 v-if="settings.title" class="cta-title" :style="titleStyles">
        {{ settings.title }}
      </h2>
      <p v-if="settings.content" class="cta-text" :style="textStyles">
        {{ settings.content }}
      </p>
    </div>
    
    <div class="cta-button-wrapper">
      <a 
        :href="settings.buttonUrl || '#'" 
        :target="settings.buttonTarget || '_self'"
        class="cta-button"
        :style="buttonStyles"
        @click.prevent
      >
        {{ settings.buttonText || 'Get Started' }}
      </a>
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
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
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

const layout = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'stacked')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'center')

const wrapperStyles = computed(() => {
  const styles = { 
    width: '100%',
    display: 'flex',
    gap: layout.value === 'stacked' ? '24px' : '32px',
    flexDirection: layout.value === 'stacked' ? 'column' : 'row',
    alignItems: layout.value === 'inline' ? 'center' : (alignment.value === 'center' ? 'center' : (alignment.value === 'right' ? 'flex-end' : 'flex-start')),
    textAlign: alignment.value
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

const contentStyles = computed(() => ({
  flex: layout.value === 'inline' ? 1 : 'none',
  textAlign: alignment.value
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const textStyles = computed(() => getTypographyStyles(settings.value, 'content_', device.value))

const buttonStyles = computed(() => {
  const styles = {
    display: 'inline-block',
    padding: '12px 28px',
    backgroundColor: settings.value.buttonBackgroundColor || '#ffffff',
    color: settings.value.buttonTextColor || '#2059ea',
    textDecoration: 'none',
    borderRadius: '6px',
    transition: 'transform 0.2s ease, box-shadow 0.2s ease',
    cursor: 'pointer'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'button_', device.value))
  return styles
})
</script>

<style scoped>
.cta-block { width: 100%; }
.cta-button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
</style>
