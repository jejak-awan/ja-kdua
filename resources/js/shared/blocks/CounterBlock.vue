<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <Card class="counter-block border-none shadow-xl rounded-[32px] overflow-hidden bg-white dark:bg-slate-900 group transition-all duration-500 hover:-translate-y-2" :style="counterBlockStyles">
        <CardContent class="p-8 flex flex-col items-center">
            <div 
              class="counter-number font-black text-5xl lg:text-6xl tracking-tighter text-primary bg-primary/5 w-full py-6 rounded-[24px] mb-6 text-center tabular-nums transition-all duration-500 group-hover:bg-primary/10 group-hover:scale-105" 
              :style="numberStyles"
              :contenteditable="mode === 'edit'"
              @blur="onNumberBlur"
              @input="onNumberInput"
            >
              {{ prefixValue }}{{ isEditing ? targetNumber : formattedDisplayNumber }}{{ suffixValue }}
            </div>
            <div 
              v-if="titleValue || mode === 'edit'" 
              class="counter-title uppercase text-[10px] font-black tracking-[0.2em] text-slate-400 group-hover:text-primary transition-colors duration-300" 
              :style="titleStyles"
              :contenteditable="mode === 'edit'"
              @blur="e => updateResponsiveField('title', e.target.innerText)"
            >
              {{ titleValue || 'Metric Label' }}
            </div>
        </CardContent>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module?.settings || {})

const targetNumber = computed(() => parseFloat(getVal(settings.value, 'number', currentDevice.value)) || 0)
const prefixValue = computed(() => getVal(settings.value, 'prefix', currentDevice.value) || '')
const suffixValue = computed(() => getVal(settings.value, 'suffix', currentDevice.value) || '')
const titleValue = computed(() => getVal(settings.value, 'title', currentDevice.value))

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
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [fieldName]: value })
}

const decimals = computed(() => parseInt(getVal(settings.value, 'decimals', currentDevice.value)) || 0)
const useSeparator = computed(() => getVal(settings.value, 'separator', currentDevice.value) !== false)

const formattedDisplayNumber = computed(() => {
    let num = displayNumber.value.toFixed(decimals.value)
    if (useSeparator.value) {
        const parts = num.split('.')
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        num = parts.join('.')
    }
    return num
})

onMounted(() => {
    animateNumber()
})

const animateNumber = () => {
    const duration = parseInt(getVal(settings.value, 'duration', currentDevice.value)) || 2000
    let start = null
    
    const step = (timestamp) => {
        if (!start) start = timestamp
        const progress = Math.min((timestamp - start) / duration, 1)
        
        // Quad ease out
        const eased = 1 - Math.pow(1 - progress, 2)
        
        displayNumber.value = eased * targetNumber.value
        
        if (progress < 1) {
            window.requestAnimationFrame(step)
        } else {
            displayNumber.value = targetNumber.value
        }
    }
    
    window.requestAnimationFrame(step)
}

const counterBlockStyles = computed(() => ({
    backgroundColor: settings.value.cardBackgroundColor || ''
}))

const numberStyles = computed(() => getTypographyStyles(settings.value, 'number_', currentDevice.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', currentDevice.value))
</script>

<style scoped>
.counter-block { width: 100%; }
</style>
