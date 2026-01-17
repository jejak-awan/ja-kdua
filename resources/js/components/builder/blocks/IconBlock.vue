<template>
  <div class="icon-block" :style="wrapperStyles">
    <component
      :is="settings.linkUrl ? 'a' : 'div'"
      class="icon-wrapper"
      :href="settings.linkUrl || undefined"
      :target="settings.linkUrl ? (settings.linkTarget || '_self') : undefined"
      :style="iconWrapperStyles"
    >
      <component 
        :is="iconComponent" 
        class="icon-element"
        :style="iconStyles"
      />
    </component>
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
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

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

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  const align = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  styles.textAlign = align
  
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

const iconWrapperStyles = computed(() => {
  const styles = {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    textDecoration: 'none',
    transition: 'transform 0.2s ease, box-shadow 0.2s ease'
  }
  
  return styles
})

const iconStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'size', device.value) || 48
  const color = getResponsiveValue(settings.value, 'color', device.value) || '#333333'
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: color,
    display: 'block'
  }
})
</script>

<style scoped>
.icon-block { width: 100%; }
.icon-wrapper:hover { transform: scale(1.05); }
</style>
