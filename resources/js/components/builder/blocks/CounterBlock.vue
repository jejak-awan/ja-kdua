<template>
  <div class="counter-block" :style="wrapperStyles">
    <div 
      class="counter-number" 
      :style="numberStyles"
      :contenteditable="builder?.isEditing"
      @blur="onNumberBlur"
      @input="onNumberInput"
    >
      {{ prefixValue }}{{ isEditing ? targetNumber : displayNumber }}{{ suffixValue }}
    </div>
    <div 
      v-if="titleValue || builder?.isEditing" 
      class="counter-title" 
      :style="titleStyles"
      :contenteditable="builder?.isEditing"
      @blur="onTitleBlur"
    >
      {{ titleValue }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
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

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const targetNumber = computed(() => parseInt(getResponsiveValue(settings.value, 'number', device.value)) || 0)
const prefixValue = computed(() => getResponsiveValue(settings.value, 'prefix', device.value) || '')
const suffixValue = computed(() => getResponsiveValue(settings.value, 'suffix', device.value) || '')
const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value))

const displayNumber = ref(0)
const isEditing = ref(false)

const onNumberBlur = (e) => {
    isEditing.value = false
    const val = parseInt(e.target.innerText.replace(prefixValue.value, '').replace(suffixValue.value, '')) || 0
    updateResponsiveField('number', val)
}

const onNumberInput = () => {
    isEditing.value = true
}

const onTitleBlur = (e) => {
    updateResponsiveField('title', e.target.innerText)
}

const updateResponsiveField = (fieldName, value) => {
    const current = settings.value[fieldName]
    let newValue
    if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
        newValue = { ...current, [device.value]: value }
    } else {
        newValue = { [device.value]: value }
    }
    builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}

onMounted(() => {
  if (getResponsiveValue(settings.value, 'animateOnView', device.value) !== false) {
    animateNumber()
  } else {
    displayNumber.value = targetNumber.value
  }
})

const animateNumber = () => {
  const duration = getResponsiveValue(settings.value, 'duration', device.value) || 2000
  const steps = 60
  const stepDuration = duration / steps
  const increment = targetNumber.value / steps
  let current = 0
  
  const timer = setInterval(() => {
    current += increment
    if (current >= targetNumber.value) {
      displayNumber.value = targetNumber.value
      clearInterval(timer)
    } else {
      displayNumber.value = Math.floor(current)
    }
  }, stepDuration)
}

const wrapperStyles = computed(() => {
  const styles = { 
    textAlign: getResponsiveValue(settings.value, 'alignment', device.value) || 'center',
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

const numberStyles = computed(() => {
  return getTypographyStyles(settings.value, 'number_', device.value)
})

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, 'title_', device.value)
})
</script>

<style scoped>
.counter-block { width: 100%; }
.counter-number { font-variant-numeric: tabular-nums; }
.counter-title { text-transform: uppercase; letter-spacing: 0.5px; }
</style>
