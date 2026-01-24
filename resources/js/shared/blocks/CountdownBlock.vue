<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="countdown-block"
  >
    <div class="countdown-items flex flex-wrap gap-6 w-full" :style="itemsStyles">
      <Card 
        v-if="getVal(settings, 'showDays') !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-transform duration-500 hover:-translate-y-2"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.days }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              :contenteditable="mode === 'edit'"
              @blur="updateField('daysLabel', $event.target.innerText)"
              v-text="getVal(settings, 'daysLabel') || 'Days'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showHours') !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-transform duration-500 hover:-translate-y-2"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.hours }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              :contenteditable="mode === 'edit'"
              @blur="updateField('hoursLabel', $event.target.innerText)"
              v-text="getVal(settings, 'hoursLabel') || 'Hours'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showMinutes') !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-transform duration-500 hover:-translate-y-2"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.minutes }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              :contenteditable="mode === 'edit'"
              @blur="updateField('minutesLabel', $event.target.innerText)"
              v-text="getVal(settings, 'minutesLabel') || 'Minutes'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showSeconds') !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-transform duration-500 hover:-translate-y-2"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center animate-pulse" :style="numberStyles">{{ timeLeft.seconds }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              :contenteditable="mode === 'edit'"
              @blur="updateField('secondsLabel', $event.target.innerText)"
              v-text="getVal(settings, 'secondsLabel') || 'Seconds'"
            ></div>
        </CardContent>
      </Card>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean,
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')

const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
let interval = null

const calculateTimeLeft = () => {
  const targetDate = getVal(props.settings, 'endDate', currentDevice.value) || new Date(Date.now() + 864000000).toISOString().split('T')[0]
  const targetTime = getVal(props.settings, 'endTime', currentDevice.value) || '00:00'
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

const itemsStyles = computed(() => {
  const alignment = getVal(props.settings, 'alignment', currentDevice.value) || 'center'
  return {
    justifyContent: alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start'),
  }
})

const unitBaseStyles = computed(() => ({
    backgroundColor: getVal(props.settings, 'itemBackgroundColor', currentDevice.value) || '',
}))

const numberStyles = computed(() => getTypographyStyles(props.settings, 'number_', currentDevice.value))
const labelStyles = computed(() => getTypographyStyles(props.settings, 'label_', currentDevice.value))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.id, { [key]: value })
}
</script>

<style scoped>
.countdown-block { width: 100%; }
.countdown-number { font-variant-numeric: tabular-nums; }
</style>
