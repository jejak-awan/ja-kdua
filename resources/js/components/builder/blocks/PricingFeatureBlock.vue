<template>
  <div 
    class="pricing-feature-block"
    :class="{ 'pricing-feature--disabled': !isIncluded }"
    :style="itemStyles"
  >
      <Check v-if="isIncluded" class="feature-icon feature-icon--check" :style="iconStyles" />
      <X v-else class="feature-icon feature-icon--x" :style="iconStyles" />
      <span class="feature-text">{{ settings.text || 'Feature Text' }}</span>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Check, X } from 'lucide-vue-next'
import { getTypographyStyles, getResponsiveValue } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from PricingTableBlock
const pricingState = inject('pricingState', {
    parentSettings: {}
})
const parentSettings = computed(() => pricingState.parentSettings.value || {})

const isIncluded = computed(() => getResponsiveValue(settings.value, 'included', device.value) !== false)

const itemStyles = computed(() => {
    const styles = {
        color: getResponsiveValue(parentSettings.value, 'textColor', device.value) || '#666666',
        textDecoration: !isIncluded.value ? 'line-through' : 'none',
        opacity: !isIncluded.value ? 0.5 : 1
    }
    
    // Explicit padding and border are handled by CSS class for now to maintain list structure
    // but we can make them dynamic here if needed.
    
    return styles
})

const iconStyles = computed(() => ({
    width: '18px', 
    height: '18px',
    flexShrink: 0,
    color: isIncluded.value ? '#22c55e' : '#ef4444' // Could be customizable via parent
}))
</script>

<style scoped>
.pricing-feature-block { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
    padding: 12px 0; 
    border-bottom: 1px solid #f0f0f0; 
    width: 100%;
}
.pricing-feature-block:last-child { border-bottom: none; }
</style>
