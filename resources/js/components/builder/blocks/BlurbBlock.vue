<template>
  <div class="blurb-block" :style="wrapperStyles" :class="layoutClass">
    <!-- Media (Icon/Image) -->
    <div v-if="mediaType !== 'none'" class="blurb-media" :style="mediaStyles">
      <component 
        v-if="mediaType === 'icon'"
        :is="iconComponent" 
        class="blurb-icon"
        :style="iconStyles"
      />
      <img 
        v-else-if="mediaType === 'image' && settings.image"
        :src="settings.image" 
        alt=""
        class="blurb-image"
      />
    </div>
    
    <!-- Content -->
    <div class="blurb-content" :style="contentWrapperStyles">
      <h3 v-if="settings.title" class="blurb-title" :style="titleStyles">
        {{ settings.title }}
      </h3>
      <div v-if="settings.content" class="blurb-text" :style="textStyles">
        {{ settings.content }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, inject } from 'vue'
import { Star } from 'lucide-vue-next'
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

const mediaType = computed(() => settings.value.mediaType || 'icon')

const layoutClass = computed(() => {
  const position = getResponsiveValue(settings.value, 'iconPosition', device.value) || 'top'
  return `blurb--${position}`
})

const iconComponent = computed(() => {
  const iconName = settings.value.icon || 'Star'
  try {
    return defineAsyncComponent(() => 
      import('lucide-vue-next').then(m => m[iconName] || Star)
    )
  } catch {
    return Star
  }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const position = getResponsiveValue(settings.value, 'iconPosition', device.value) || 'top'
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  
  const styles = { 
    width: '100%',
    display: 'flex',
    gap: position === 'top' ? '16px' : '20px',
    flexDirection: position === 'top' ? 'column' : (position === 'right' ? 'row-reverse' : 'row'),
    alignItems: position === 'top' ? (alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start')) : 'flex-start'
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

const contentWrapperStyles = computed(() => ({
  flex: 1,
  textAlign: getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
}))

const mediaStyles = computed(() => {
  const styles = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }
  
  const bgColor = getResponsiveValue(settings.value, 'iconBackgroundColor', device.value)
  const shape = getResponsiveValue(settings.value, 'iconBackgroundShape', device.value)
  
  if (bgColor && shape !== 'none') {
    styles.backgroundColor = bgColor
    styles.padding = '16px'
    
    if (shape === 'circle') {
      styles.borderRadius = '50%'
    } else if (shape === 'rounded') {
      styles.borderRadius = '12px'
    }
  }
  
  return styles
})

const iconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'iconSize', device.value) || 48
  const color = getResponsiveValue(settings.value, 'iconColor', device.value) || '#2059ea'
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: color
  }
})

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, 'title_', device.value)
})

const textStyles = computed(() => {
  return getTypographyStyles(settings.value, 'content_', device.value)
})
</script>

<style scoped>
.blurb-block { width: 100%; }
.blurb-image { max-width: 80px; height: auto; border-radius: 4px; }
</style>
