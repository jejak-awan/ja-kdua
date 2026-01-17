<template>
  <div class="hero-block" :style="wrapperStyles">
    <div class="hero-container" :style="containerStyles">
      <h1 v-if="settings.title" class="hero-title" :style="titleStyles">{{ settings.title }}</h1>
      <h2 v-if="settings.subtitle" class="hero-subtitle" :style="subtitleStyles">{{ settings.subtitle }}</h2>
      
      <div v-if="settings.content" class="hero-content" :style="bodyStyles" v-html="settings.content"></div>
      
      <div class="hero-buttons" v-if="settings.showButton1 || settings.showButton2">
        <a 
          v-if="settings.showButton1" 
          :href="settings.buttonUrl" 
          class="hero-btn hero-btn-primary"
          :style="button1Styles"
        >
          {{ settings.buttonText }}
        </a>
        <a 
          v-if="settings.showButton2" 
          :href="settings.button2Url" 
          class="hero-btn hero-btn-secondary"
          :style="button2Styles"
        >
          {{ settings.button2Text }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { inject } from 'vue'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const styles = {
    position: 'relative',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    width: '100%'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  if (!styles.minHeight) styles.minHeight = '400px'
  
  return styles
})

const containerStyles = computed(() => {
  const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  const maxWidth = getResponsiveValue(settings.value, 'contentWidth', device.value) || 800
  
  return {
    position: 'relative',
    zIndex: 2,
    width: '100%',
    maxWidth: typeof maxWidth === 'number' ? `${maxWidth}px` : maxWidth,
    textAlign: align,
    display: 'flex',
    flexDirection: 'column',
    alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const bodyStyles = computed(() => getTypographyStyles(settings.value, 'body_', device.value))

const button1Styles = computed(() => ({
  // Fallback styles for hero buttons
  backgroundColor: '#2059ea',
  color: 'white',
  padding: '12px 32px',
  borderRadius: '4px',
  fontWeight: '600',
  textDecoration: 'none'
}))

const button2Styles = computed(() => ({
  backgroundColor: 'transparent',
  border: '2px solid white',
  color: 'white',
  padding: '10px 30px',
  borderRadius: '4px',
  fontWeight: '600',
  textDecoration: 'none'
}))

</script>

<style scoped>
.hero-title { margin: 0 0 16px; }
.hero-subtitle { margin: 0 0 24px; }
.hero-content { margin: 0 0 32px; }

.hero-buttons {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, opacity 0.2s;
}

.hero-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}
</style>
