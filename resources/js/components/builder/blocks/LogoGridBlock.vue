<template>
  <div class="logo-grid-block" :style="wrapperStyles">
    <h4 v-if="settings.showTitle !== false && settings.title" class="logo-grid-title" :style="titleStyles">{{ settings.title }}</h4>
    <div class="logo-grid" :style="gridStyles">
      <div v-for="(logo, index) in logoList" :key="index" class="logo-item" :class="{ 'logo-item--grayscale': settings.grayscale !== false, 'logo-item--hover-color': settings.hoverColor !== false }">
        <a v-if="logo.url" :href="logo.url" target="_blank" class="logo-link">
          <img v-if="logo.image" :src="logo.image" :alt="logo.name" :style="logoStyles" />
          <div v-else class="logo-placeholder" :style="placeholderStyles"><Building /></div>
        </a>
        <template v-else>
          <img v-if="logo.image" :src="logo.image" :alt="logo.name" :style="logoStyles" />
          <div v-else class="logo-placeholder" :style="placeholderStyles"><Building /></div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Building } from 'lucide-vue-next'
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

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const logoList = computed(() => {
  return (props.module.children || []).map(child => ({
    image: child.settings.image || '',
    name: child.settings.name || '',
    url: child.settings.url || ''
  }))
})

const wrapperStyles = computed(() => {
  const styles = { textAlign: 'center', width: '100%' }
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

const gridStyles = computed(() => {
  const cols = getResponsiveValue(settings.value, 'columns', device.value) || 4
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 32
  return { 
    display: 'grid', 
    gridTemplateColumns: `repeat(${cols}, 1fr)`, 
    gap: `${gap}px`, 
    alignItems: 'center', 
    justifyItems: 'center' 
  }
})

const logoStyles = computed(() => {
  const logoSize = getResponsiveValue(settings.value, 'logoSize', device.value) || 120
  return { 
    maxWidth: `${logoSize}px`, 
    maxHeight: `${logoSize * 0.5}px`, 
    objectFit: 'contain' 
  }
})

const placeholderStyles = computed(() => {
  const logoSize = getResponsiveValue(settings.value, 'logoSize', device.value) || 120
  return { 
    width: `${logoSize}px`, 
    height: `${logoSize * 0.4}px`, 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    backgroundColor: '#f0f0f0', 
    borderRadius: '4px', 
    color: '#ccc' 
  }
})
</script>

<style scoped>
.logo-grid-block { width: 100%; }
.logo-grid-title { margin: 0 0 24px; }
.logo-item { transition: all 0.3s; }
.logo-item--grayscale img { filter: grayscale(100%); opacity: 0.6; }
.logo-item--grayscale.logo-item--hover-color:hover img { filter: grayscale(0); opacity: 1; }
.logo-link { display: block; }
</style>
