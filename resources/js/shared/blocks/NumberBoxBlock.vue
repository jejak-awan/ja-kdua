<template>
  <BaseBlock :module="module" :settings="settings" class="number-box-block">
    <div 
        class="number-box-inner flex items-center p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 transition-transform hover:scale-[1.02]" 
        :class="layoutClass === 'vertical' ? 'flex-col text-center gap-6' : 'flex-row gap-6'"
        :style="innerStyles"
    >
      <div 
        class="number-box-number flex items-center justify-center font-bold relative z-10" 
        :style="numberStyles"
      >
        <div class="number-bg absolute inset-0 opacity-10 rounded-xl" :style="{ backgroundColor: settings.number_backgroundColor || '#3b82f6' }"></div>
        <span class="relative z-10">{{ settings.number || '01' }}</span>
      </div>
      
      <div class="number-box-content flex-1">
        <h4 class="number-box-title text-xl font-bold mb-2" :style="titleStyles">{{ settings.title || 'Step Title' }}</h4>
        <p v-if="settings.description" class="number-box-description opacity-70 text-sm leading-relaxed" :style="descriptionStyles">{{ settings.description }}</p>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const layoutClass = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'horizontal')

const innerStyles = computed(() => ({}))

const numberStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'number_', device.value)
  const size = settings.value.number_fontSize ? parseInt(settings.value.number_fontSize) : 48
  const containerSize = Math.max(size * 1.5, 64)
  return {
    ...styles,
    width: `${containerSize}px`,
    height: `${containerSize}px`,
    flexShrink: 0
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-box-block { width: 100%; }
</style>
