<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="circle-counter-wrapper"
  >
    <Card class="circle-counter-card border-none shadow-xl rounded-[40px] overflow-hidden bg-white dark:bg-slate-900 group transition-all duration-500 hover:-translate-y-2 p-10 flex flex-col items-center">
        <div class="circle-counter-container relative flex items-center justify-center" :style="containerStyles">
          <svg :width="size" :height="size" viewBox="0 0 100 100" class="circle-svg -rotate-90">
            <!-- Background Circle -->
            <circle
              class="circle-bg"
              cx="50"
              cy="50"
              :r="radius"
              fill="none"
              :stroke="trackColor"
              :stroke-width="normalizedThickness"
              opacity="0.1"
            />
            <!-- Progress Circle -->
            <circle
              class="circle-fill"
              cx="50"
              cy="50"
              :r="radius"
              fill="none"
              :stroke="fillColor"
              :stroke-width="normalizedThickness"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="dashOffset"
              stroke-linecap="round"
            />
          </svg>
          
          <div class="circle-inner-content absolute inset-0 flex flex-col items-center justify-center text-center">
            <div 
              v-if="getVal(settings, 'showValue') !== false" 
              class="circle-value font-black text-4xl lg:text-5xl tracking-tighter tabular-nums" 
              :style="valueStyles"
            >
              {{ currentValue }}%
            </div>
          </div>
        </div>

        <div 
          v-if="hasTitle" 
          class="circle-title mt-8 uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('title', $event.target.innerText)"
          v-text="getVal(settings, 'title') || 'Project Velocity'"
        ></div>
    </Card>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card } from '../ui'
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

const animatedValue = ref(0)

const size = computed(() => parseInt(getVal(props.settings, 'size', currentDevice.value)) || 180)
const thickness = computed(() => parseInt(getVal(props.settings, 'thickness', currentDevice.value)) || 10)
const targetValue = computed(() => Math.min(100, Math.max(0, parseInt(getVal(props.settings, 'value', currentDevice.value)) || 75)))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'title', currentDevice.value))

const normalizedThickness = computed(() => (thickness.value / size.value) * 100)
const radius = computed(() => 50 - normalizedThickness.value / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)

const currentValue = computed(() => props.mode === 'edit' ? targetValue.value : animatedValue.value)
const dashOffset = computed(() => circumference.value * (1 - currentValue.value / 100))

const fillColor = computed(() => getVal(props.settings, 'color', currentDevice.value) || 'currentColor')
const trackColor = computed(() => getVal(props.settings, 'trackColor', currentDevice.value) || 'currentColor')

onMounted(() => {
  if (props.mode !== 'edit' || props.isPreview) {
    let start = null
    const duration = parseInt(getVal(props.settings, 'duration', currentDevice.value)) || 2000
    const step = (timestamp) => {
      if (!start) start = timestamp
      const progress = Math.min((timestamp - start) / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3) 
      animatedValue.value = Math.floor(eased * targetValue.value)
      if (progress < 1) {
        window.requestAnimationFrame(step)
      } else {
        animatedValue.value = targetValue.value
      }
    }
    window.requestAnimationFrame(step)
  }
})

const containerStyles = computed(() => ({
  width: `${size.value}px`,
  height: `${size.value}px`,
  color: fillColor.value
}))

const valueStyles = computed(() => getTypographyStyles(props.settings, 'value_', currentDevice.value))
const titleStyles = computed(() => getTypographyStyles(props.settings, 'title_', currentDevice.value))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.id, { [key]: value })
}
</script>

<style scoped>
.circle-counter-wrapper { width: 100%; display: flex; justify-content: center; }
.circle-fill { transition: stroke-dashoffset 2s cubic-bezier(0.1, 0.5, 0.5, 1); }
</style>
