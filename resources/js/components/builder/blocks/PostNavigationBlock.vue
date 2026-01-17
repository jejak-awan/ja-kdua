<template>
  <div class="post-nav-block" :style="wrapperStyles">
    <a href="#" class="post-nav-item post-nav-item--prev" :style="itemStyles">
      <ChevronLeft :style="iconStyles" />
      <div class="post-nav-content">
        <span v-if="settings.showLabels !== false" class="post-nav-label" :style="labelStyles">{{ settings.prevLabel || 'Previous Post' }}</span>
        <span class="post-nav-title" :style="titleStyles">How to Build a Website</span>
      </div>
    </a>
    <a href="#" class="post-nav-item post-nav-item--next" :style="itemStyles">
      <div class="post-nav-content" style="text-align: right">
        <span v-if="settings.showLabels !== false" class="post-nav-label" :style="labelStyles">{{ settings.nextLabel || 'Next Post' }}</span>
        <span class="post-nav-title" :style="titleStyles">10 Tips for SEO Success</span>
      </div>
      <ChevronRight :style="iconStyles" />
    </a>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const wrapperStyles = computed(() => {
  const styles = { 
    display: 'flex', 
    justifyContent: 'space-between', 
    gap: '20px', 
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
  return styles
})

const itemStyles = computed(() => {
    // Assuming text color generally handled by typography, but maybe not globally.
    // We can use a default color if title styles don't provide it, but titleStyles is separate.
    return { 
        display: 'flex', 
        alignItems: 'center', 
        gap: '12px', 
        textDecoration: 'none', 
        flex: 1 
    }
})

const iconStyles = computed(() => ({ 
    width: '24px', 
    height: '24px', 
    color: settings.value.label_color || '#2059ea', // Fallback to label color or blue
    flexShrink: 0 
}))

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.post-nav-block { width: 100%; }
.post-nav-item { transition: opacity 0.2s; }
.post-nav-item:hover { opacity: 0.8; }
.post-nav-content { display: flex; flex-direction: column; gap: 4px; }
</style>
