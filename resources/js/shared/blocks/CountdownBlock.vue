<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="countdown-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Countdown Timer'"
    :style="cardStyles"
  >
    <div class="countdown-items flex flex-wrap" :style="containerStyles">
      <Card 
        v-if="getVal(settings, 'showDays', device) !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-all"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.days }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              v-text="getVal(settings, 'daysLabel', device) || 'Days'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showHours', device) !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-all"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.hours }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              v-text="getVal(settings, 'hoursLabel', device) || 'Hours'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showMinutes', device) !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-all"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center" :style="numberStyles">{{ timeLeft.minutes }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              v-text="getVal(settings, 'minutesLabel', device) || 'Minutes'"
            ></div>
        </CardContent>
      </Card>
      
      <Card 
        v-if="getVal(settings, 'showSeconds', device) !== false" 
        class="countdown-unit group flex-1 min-w-[120px] bg-white dark:bg-slate-900 border-none shadow-xl rounded-[32px] overflow-hidden p-6 transition-all"
        :style="unitBaseStyles"
      >
        <CardContent class="p-0 flex flex-col items-center">
            <div class="countdown-number font-black text-4xl lg:text-5xl tracking-tighter text-primary bg-primary/5 w-full py-4 rounded-2xl mb-4 text-center animate-pulse" :style="numberStyles">{{ timeLeft.seconds }}</div>
            <div 
              class="countdown-label uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="labelStyles"
              v-text="getVal(settings, 'secondsLabel', device) || 'Seconds'"
            ></div>
        </CardContent>
      </Card>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent } from '../ui'
import { 
  getVal, 
  getLayoutStyles,
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const timeLeft = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
let interval: any = null

const calculateTimeLeft = () => {
  const endDate = getVal(settings.value, 'endDate', device.value) || new Date(Date.now() + 864000000).toISOString().split('T')[0]
  const endTime = getVal(settings.value, 'endTime', device.value) || '00:00'
  const target = new Date(`${endDate}T${endTime}:00`)
  const now = new Date()
  const diff = target.getTime() - now.getTime()

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

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  const align = getVal(settings.value, 'alignment', device.value) || 'center'
  const gap = parseInt(getVal(settings.value, 'gap', device.value)) || 24
  
  return {
    ...layout,
    gap: `${gap}px`,
    justifyContent: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start'),
  }
})

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const unitBaseStyles = computed(() => ({
    backgroundColor: getVal(settings.value, 'itemBackgroundColor', device.value) || '',
    borderRadius: `${parseInt(getVal(settings.value, 'itemBorderRadius', device.value)) || 32}px`,
    padding: `${parseInt(getVal(settings.value, 'itemPadding', device.value)) || 24}px`
}))

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))

</script>

<style scoped>
.countdown-block { width: 100%; }
.countdown-number { font-variant-numeric: tabular-nums; }
.countdown-unit {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.countdown-unit:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>

