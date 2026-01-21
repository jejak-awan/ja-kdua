<template>
  <div 
    class="pricing-feature-item flex items-center py-4 border-b border-gray-100 dark:border-gray-800 last:border-0 group transition-all"
    :class="{ 'opacity-40 line-through grayscale': !isIncluded }"
    :style="itemStyles"
  >
      <div 
        class="feature-icon-wrap w-6 h-6 flex items-center justify-center rounded-full mr-4 transition-transform group-hover:scale-110"
        :class="isIncluded ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
        :style="iconStyles"
      >
        <Check v-if="isIncluded" class="w-4 h-4" />
        <X v-else class="w-4 h-4" />
      </div>
      <span class="feature-text font-medium text-sm md:text-base">{{ settings.text || 'Premium Feature' }}</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Check, X } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Injected from PricingTableBlock
const pricingState = inject('pricingState', {
    parentSettings: ref({})
})
const parentSettings = computed(() => pricingState.parentSettings.value || {})

const isIncluded = computed(() => getResponsiveValue(settings.value, 'included', device.value) !== false)

const itemStyles = computed(() => {
    const textColor = getResponsiveValue(parentSettings.value, 'textColor', device.value)
    return {
        color: textColor
    }
})

const iconStyles = computed(() => ({}))
</script>

<style scoped>
.pricing-feature-item { width: 100%; }
</style>
