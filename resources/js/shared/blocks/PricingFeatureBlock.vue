<template>
  <div 
    class="pricing-feature-item flex items-center py-5 border-b border-slate-50 dark:border-slate-800 last:border-0 group transition-all duration-300"
    :class="[
        !isIncluded ? 'opacity-30 line-through grayscale' : '',
        settings.strikeThrough ? 'line-through opacity-40' : ''
    ]"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Pricing Feature'"
    :style="itemStyles"
  >
      <div 
        class="feature-icon-wrap w-7 h-7 flex items-center justify-center rounded-xl mr-5 transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-sm border border-slate-100 dark:border-slate-800"
        :class="isIncluded ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
        :style="iconStyles"
      >
        <Check v-if="isIncluded" class="w-4 h-4" stroke-width="3" />
        <X v-else class="w-4 h-4" stroke-width="3" />
      </div>
      <span class="feature-text font-bold text-sm md:text-base tracking-tight text-slate-700 dark:text-slate-300 transition-colors group-hover:text-primary" :style="textStyles">
        {{ settings.text || 'Premium Feature' }}
      </span>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Check, X } from 'lucide-vue-next'
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

// Injected from PricingTableBlock (if applicable)
const pricingState = inject<any>('pricingState', null)
const parentSettings = computed(() => pricingState?.parentSettings?.value || {})

const isIncluded = computed(() => getVal(settings.value, 'included', device.value) !== false)

const itemStyles = computed(() => {
    const layout = getLayoutStyles(settings.value, device.value)
    return {
        ...layout
    }
})

const textStyles = computed(() => {
    return getTypographyStyles(settings.value, 'text_', device.value)
})

const iconStyles = computed(() => ({}))
</script>

<style scoped>
.pricing-feature-item { width: 100%; }
</style>

