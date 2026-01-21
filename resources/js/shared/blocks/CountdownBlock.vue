<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="countdown-block"
  >
    <div class="countdown-items" :style="itemsStyles">
      <div v-if="getVal(settings, 'showDays') !== false" class="countdown-unit" :style="unitStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.days }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('daysLabel', $event.target.innerText)"
          v-text="getVal(settings, 'daysLabel') || 'Days'"
        ></div>
      </div>
      
      <div v-if="getVal(settings, 'showHours') !== false" class="countdown-unit" :style="unitStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.hours }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('hoursLabel', $event.target.innerText)"
          v-text="getVal(settings, 'hoursLabel') || 'Hours'"
        ></div>
      </div>
      
      <div v-if="getVal(settings, 'showMinutes') !== false" class="countdown-unit" :style="unitStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.minutes }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('minutesLabel', $event.target.innerText)"
          v-text="getVal(settings, 'minutesLabel') || 'Minutes'"
        ></div>
      </div>
      
      <div v-if="getVal(settings, 'showSeconds') !== false" class="countdown-unit" :style="unitStyles">
        <div class="countdown-number" :style="numberStyles">{{ timeLeft.seconds }}</div>
        <div 
          class="countdown-label" 
          :style="labelStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('secondsLabel', $event.target.innerText)"
          v-text="getVal(settings, 'secondsLabel') || 'Seconds'"
        ></div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)
const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
let interval = null

const calculateTimeLeft = () => {
  const targetDate = getVal(props.settings, 'endDate') || new Date().toISOString().split('T')[0]
  const targetTime = getVal(props.settings, 'endTime') || '00:00'
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

const itemsStyles = computed(() => ({
  display: 'flex',
  justifyContent: getVal(props.settings, 'alignment') || 'center',
  gap: `${getVal(props.settings, 'gap') || 24}px`,
  flexWrap: 'wrap',
  width: '100%'
}))

const unitStyles = computed(() => ({
  backgroundColor: getVal(props.settings, 'itemBackgroundColor') || '#f1f5f9',
  borderRadius: `${getVal(props.settings, 'itemBorderRadius') || 8}px`,
  padding: `${getVal(props.settings, 'itemPadding') || 16}px`,
  textAlign: 'center',
  minWidth: '90px'
}))

const numberStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'number_'),
  fontVariantNumeric: 'tabular-nums',
  lineHeight: '1.2'
}))

const labelStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'label_'),
  marginTop: '4px',
  outline: 'none'
}))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.countdown-block {
  width: 100%;
}

.countdown-unit {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.countdown-number {
  font-weight: 800;
  font-size: 2rem;
}

.countdown-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.7;
}
</style>
