<template>
  <div class="logo-grid-block" :style="wrapperStyles">
    <!-- Title -->
    <div 
      v-if="showTitle && title" 
      class="logo-grid-title" 
      :style="titleStyles"
    >
      {{ title }}
    </div>

    <!-- Grid -->
    <div class="logo-grid" :style="gridStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="logo-item"
        :class="{ 
          'logo-item--grayscale': grayscale, 
          'logo-item--hover-color': hoverColor 
        }"
      >
        <div class="logo-container" :style="logoContainerStyles">
          <img 
            v-if="item.image" 
            :src="item.image" 
            :alt="item.name" 
            :style="logoImgStyles"
          />
          <div v-else class="logo-placeholder" :style="placeholderStyles">
            <Building />
          </div>
        </div>
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
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const showTitle = computed(() => getResponsiveValue(settings.value, 'showTitle', device.value) !== false)
const title = computed(() => getResponsiveValue(settings.value, 'title', device.value))
const grayscale = computed(() => getResponsiveValue(settings.value, 'grayscale', device.value) !== false)
const hoverColor = computed(() => getResponsiveValue(settings.value, 'hoverColor', device.value) !== false)

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

const titleStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'title_', device.value)
    return {
        ...styles,
        marginBottom: '40px'
    }
})

const gridStyles = computed(() => {
    const cols = getResponsiveValue(settings.value, 'columns', device.value) || 4
    const gap = getResponsiveValue(settings.value, 'gap', device.value) || 40
    return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: `${gap}px`,
        alignItems: 'center',
        justifyItems: 'center'
    }
})

const logoContainerStyles = computed(() => {
    const size = getResponsiveValue(settings.value, 'logoSize', device.value) || 140
    const opacity = getResponsiveValue(settings.value, 'logoOpacity', device.value) || 0.6
    return {
        width: '100%',
        maxWidth: `${size}px`,
        opacity: opacity,
        transition: 'all 0.3s ease'
    }
})

const logoImgStyles = computed(() => ({
    width: '100%',
    height: 'auto',
    maxHeight: '80px',
    objectFit: 'contain'
}))

const placeholderStyles = computed(() => ({
    width: '100%',
    aspectRatio: '2/1',
    backgroundColor: '#f1f5f9',
    borderRadius: '8px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: '#cbd5e1'
}))
</script>

<style scoped>
.logo-item { width: 100%; transition: all 0.3s; }
.logo-item--grayscale .logo-container { filter: grayscale(100%); }
.logo-item--hover-color:hover .logo-container { filter: grayscale(0); opacity: 1 !important; transform: scale(1.05); }
</style>
