<template>
  <div class="search-block" :style="wrapperStyles">
    <div class="search-form" :style="formStyles">
      <input 
        type="text" 
        class="search-input"
        :placeholder="placeholderValue"
        :style="inputStyles"
      />
      <button v-if="showButton" class="search-button" :style="buttonStyles">
        <SearchIcon v-if="currentButtonStyle !== 'text'" class="search-icon" />
        <span v-if="currentButtonStyle !== 'icon'">{{ buttonTextValue }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Search as SearchIcon } from 'lucide-vue-next'
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
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const currentButtonStyle = computed(() => getResponsiveValue(settings.value, 'buttonStyle', device.value) || 'icon')
const placeholderValue = computed(() => getResponsiveValue(settings.value, 'placeholder', device.value) || 'Search...')
const buttonTextValue = computed(() => getResponsiveValue(settings.value, 'buttonText', device.value) || 'Search')
const showButton = computed(() => getResponsiveValue(settings.value, 'showButton', device.value) !== false)

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const formStyles = computed(() => {
  const styles = {
    display: 'flex',
    overflow: 'hidden'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  
  const height = getResponsiveValue(settings.value, 'height', device.value) || 48
  styles.height = `${height}px`
  
  return styles
})

const inputStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'input_', device.value)
  return {
    ...styles,
    flex: 1,
    border: 'none',
    padding: '0 16px',
    backgroundColor: 'transparent',
    outline: 'none',
    height: '100%'
  }
})

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || '#2059ea'
  const textColor = getResponsiveValue(settings.value, 'buttonTextColor', device.value) || styles.color || '#ffffff'
  
  return {
    ...styles,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: '8px',
    padding: '0 20px',
    border: 'none',
    backgroundColor: bgColor,
    color: textColor,
    cursor: 'pointer',
    transition: 'opacity 0.2s'
  }
})
</script>

<style scoped>
.search-block { width: 100%; }
.search-form { width: 100%; }
.search-input { width: 100%; }
.search-input::placeholder { color: #999; }
.search-button:hover { opacity: 0.9; }
.search-icon { width: 20px; height: 20px; }
</style>
