<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="circle-counter-wrapper"
  >
    <div class="circle-counter-container" :style="containerStyles">
      <svg :width="size" :height="size" viewBox="0 0 100 100" class="circle-svg">
        <!-- Background Circle -->
        <circle
          class="circle-bg"
          cx="50"
          cy="50"
          :r="radius"
          fill="none"
          :stroke="trackColor"
          :stroke-width="normalizedThickness"
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
      
      <div class="circle-inner-content">
        <div 
          v-if="getVal(settings, 'showValue') !== false" 
          class="circle-value" 
          :style="valueStyles"
        >
          {{ currentValue }}%
        </div>
        <div 
          v-if="hasTitle" 
          class="circle-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="updateField('title', $event.target.innerText)"
          v-text="getVal(settings, 'title') || 'Label'"
        ></div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)
const animatedValue = ref(0)

const size = computed(() => parseInt(getVal(props.settings, 'size')) || 160)
const thickness = computed(() => parseInt(getVal(props.settings, 'thickness')) || 8)
const targetValue = computed(() => Math.min(100, Math.max(0, parseInt(getVal(props.settings, 'value')) || 75)))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'title'))

const normalizedThickness = computed(() => (thickness.value / size.value) * 100)
const radius = computed(() => 50 - normalizedThickness.value / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)

const currentValue = computed(() => props.mode === 'edit' ? targetValue.value : animatedValue.value)
const dashOffset = computed(() => circumference.value * (1 - currentValue.value / 100))

const fillColor = computed(() => getVal(props.settings, 'color') || 'var(--theme-primary-color, #2059ea)')
const trackColor = computed(() => getVal(props.settings, 'trackColor') || '#f1f5f9')

onMounted(() => {
  if (props.mode !== 'edit' || props.isPreview) {
    // Basic animation
    let start = null
    const duration = 1500
    const step = (timestamp) => {
      if (!start) start = timestamp
      const progress = Math.min((timestamp - start) / duration, 1)
      animatedValue.value = Math.floor(progress * targetValue.value)
      if (progress < 1) {
        window.requestAnimationFrame(step)
      }
    }
    window.requestAnimationFrame(step)
  }
})

const containerStyles = computed(() => ({
  position: 'relative',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: `${size.value}px`,
  height: `${size.value}px`
}))

const valueStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'value_'),
  fontWeight: 800,
  lineHeight: 1
}))

const titleStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'title_'),
  marginTop: '4px',
  fontSize: '0.875rem',
  opacity: 0.8,
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
.circle-counter-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
}

.circle-svg {
  transform: rotate(-90deg);
}

.circle-fill {
  transition: stroke-dashoffset 0.8s cubic-bezier(0.1, 0.5, 0.5, 1);
}

.circle-inner-content {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.circle-value {
  font-size: 2rem;
}
</style>
