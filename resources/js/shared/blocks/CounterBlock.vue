<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="counter-block" :style="counterBlockStyles">
        <div 
          class="counter-number" 
          :style="numberStyles"
          :contenteditable="mode === 'edit'"
          @blur="onNumberBlur"
          @input="onNumberInput"
        >
          {{ prefixValue }}{{ isEditing ? targetNumber : displayNumber }}{{ suffixValue }}
        </div>
        <div 
          v-if="titleValue || mode === 'edit'" 
          class="counter-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="e => updateResponsiveField('title', e.target.innerText)"
        >
          {{ titleValue }}
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

const targetNumber = computed(() => parseInt(getVal(settings.value, 'number', props.device)) || 0)
const prefixValue = computed(() => getVal(settings.value, 'prefix', props.device) || '')
const suffixValue = computed(() => getVal(settings.value, 'suffix', props.device) || '')
const titleValue = computed(() => getVal(settings.value, 'title', props.device))

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

const updateResponsiveField = (fieldName, value) => {
    if (props.mode !== 'edit') return
    const current = settings.value[fieldName]
    let newValue
    if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
        newValue = { ...current, [props.device]: value }
    } else {
        newValue = { [props.device]: value }
    }
    builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}

onMounted(() => {
  if (getVal(settings.value, 'animateOnView', props.device) !== false) {
    animateNumber()
  } else {
    displayNumber.value = targetNumber.value
  }
})

const animateNumber = () => {
  const duration = getVal(settings.value, 'duration', props.device) || 2000
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

const counterBlockStyles = computed(() => {
  return { 
    textAlign: getVal(settings.value, 'alignment', props.device) || 'center',
    width: '100%'
  }
})

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', props.device))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
</script>

<style scoped>
.counter-block { width: 100%; }
.counter-number { font-variant-numeric: tabular-nums; }
.counter-title { text-transform: uppercase; letter-spacing: 0.5px; }
</style>
