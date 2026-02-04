<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="number-box-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Number Box'"
    :style="containerStyles"
  >
    <div 
        class="number-box-inner flex p-6 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 transition-colors duration-300 hover:scale-[1.02] hover:shadow-md" 
        :class="[
            layout === 'vertical' ? 'flex-col text-center' : 'flex-row',
            alignment === 'center' ? 'items-center text-center' : (alignment === 'right' ? 'items-end text-right' : 'items-start text-left')
        ]"
    >
      <div 
        class="number-box-number flex items-center justify-center font-bold relative z-10 shrink-0" 
        :style="(numberContainerStyles as any)"
      >
        <div class="number-bg absolute inset-0 opacity-10 rounded-xl" :style="{ backgroundColor: (settings.number_backgroundColor as string) || 'var(--primary)' }"></div>
        <span class="relative z-10" :style="(numberTextStyles as any)">{{ (settings.number as string) || '01' }}</span>
      </div>
      
      <div class="number-box-content flex-1" :class="layout === 'vertical' ? 'mt-6' : 'ml-6'">
        <h4 class="number-box-title text-xl font-bold mb-2" :style="(titleStyles as any)">{{ (settings.title as string) || 'Step Title' }}</h4>
        <p v-if="settings.description" class="number-box-description opacity-70 text-sm leading-relaxed" :style="(descriptionStyles as any)">{{ (settings.description as string) }}</p>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const layout = computed(() => getVal<string>(settings.value, 'layout', device.value) || 'horizontal')
const alignment = computed(() => getVal<string>(settings.value, 'alignment', device.value) || 'left')

const containerStyles = computed(() => {
  const styles = getLayoutStyles(settings.value, device.value)
  return styles
})

const numberContainerStyles = computed(() => {
  const sizeValue = settings.value.number_fontSize
  const size = sizeValue ? parseInt(sizeValue as string) : 48
  const containerSize = Math.max(size * 1.5, 64)
  const styles: Record<string, string | number> = {
    width: `${containerSize}px`,
    height: `${containerSize}px`
  }
  return styles
})

const numberTextStyles = computed(() => getTypographyStyles(settings.value, 'number_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))
</script>

<style scoped>
.number-box-block { width: 100%; }
</style>

