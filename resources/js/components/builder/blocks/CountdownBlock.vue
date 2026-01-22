<template>
  <div class="countdown-block" :style="wrapperStyles">
    <div class="countdown-items" :style="itemsStyles">
      <div v-if="settings.showDays !== false" class="countdown-item" :style="itemStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.days }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="builder?.isEditing"
          @blur="(e) => onLabelBlur('daysLabel', e)"
        >{{ daysLabel }}</div>
      </div>
      <div v-if="settings.showHours !== false" class="countdown-item" :style="itemStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.hours }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="builder?.isEditing"
          @blur="(e) => onLabelBlur('hoursLabel', e)"
        >{{ hoursLabel }}</div>
      </div>
      <div v-if="settings.showMinutes !== false" class="countdown-item" :style="itemStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.minutes }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="builder?.isEditing"
          @blur="(e) => onLabelBlur('minutesLabel', e)"
        >{{ minutesLabel }}</div>
      </div>
      <div v-if="settings.showSeconds !== false" class="countdown-item" :style="itemStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.seconds }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="builder?.isEditing"
          @blur="(e) => onLabelBlur('secondsLabel', e)"
        >{{ secondsLabel }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
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

const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
let interval = null

const daysLabel = computed(() => getResponsiveValue(settings.value, 'daysLabel', device.value) || 'Days')
const hoursLabel = computed(() => getResponsiveValue(settings.value, 'hoursLabel', device.value) || 'Hours')
const minutesLabel = computed(() => getResponsiveValue(settings.value, 'minutesLabel', device.value) || 'Minutes')
const secondsLabel = computed(() => getResponsiveValue(settings.value, 'secondsLabel', device.value) || 'Seconds')

const onLabelBlur = (field, e) => {
    updateResponsiveField(field, e.target.innerText)
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

const calculateTimeLeft = () => {
  const targetDate = settings.value.endDate || new Date().toISOString().split('T')[0]
  const targetTime = settings.value.endTime || '00:00'
  const target = new Date(`${targetDate}T${targetTime}:00`)
  const now = new Date()
  const diff = target - now

  if (diff <= 0) {
    timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 }
    return
  }

  timeLeft.value = {
    days: Math.floor(diff / (1000 * 60 * 60 * 24)),
    hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((diff % (1000 * 60)) / 1000)
  }
}

onMounted(() => {
  calculateTimeLeft()
  interval = setInterval(calculateTimeLeft, 1000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  
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

const itemsStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 24
  
  return {
    display: 'flex',
    gap: `${gap}px`,
    justifyContent: alignment === 'center' ? 'center' : 
                    alignment === 'right' ? 'flex-end' : 'flex-start',
    flexWrap: 'wrap'
  }
})

const itemStyles = computed(() => {
  const padding = getResponsiveValue(settings.value, 'itemPadding', device.value) || 20
  const radius = getResponsiveValue(settings.value, 'itemBorderRadius', device.value) || 8
  
  return {
    backgroundColor: settings.value.itemBackgroundColor || '#f5f5f5',
    borderRadius: `${radius}px`,
    padding: `${padding}px`,
    textAlign: 'center',
    minWidth: '80px'
  }
})

const numberStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'number_', device.value)
  return {
    ...styles,
    fontVariantNumeric: 'tabular-nums'
  }
})

const labelStyles = computed(() => {
  return getTypographyStyles(settings.value, 'label_', device.value)
})
</script>

<style scoped>
.countdown-block {
  width: 100%;
}
</style>
