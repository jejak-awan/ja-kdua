<template>
  <div class="pricing-block" :style="wrapperStyles">
    <div class="pricing-card" :style="cardStyles" :class="{ 'pricing-card--featured': isFeatured }">
      <!-- Featured Badge -->
      <div v-if="isFeatured" class="pricing-badge">
        {{ featuredLabelValue }}
      </div>
      
      <!-- Header -->
      <div class="pricing-header" :style="headerStyles">
        <h3 class="pricing-title">{{ titleValue }}</h3>
        <p v-if="subtitleValue" class="pricing-subtitle" :style="subtitleStyles">{{ subtitleValue }}</p>
      </div>
      
      <!-- Price -->
      <div class="pricing-price" :style="priceStyles">
        <span class="price-currency">{{ currencyValue }}</span>
        <span class="price-amount">{{ priceValue }}</span>
        <span class="price-period">{{ periodValue }}</span>
      </div>
      
      <!-- Features -->
      <draggable
          tag="ul"
          v-model="module.children"
          item-key="id"
          group="pricing_feature"
          class="pricing-features"
          ghost-class="ja-builder-ghost"
      >
          <template #item="{ element: child, index }">
              <ModuleWrapper
                  :module="child"
                  :index="index"
                  tag="li"   
                  class="pricing-feature-wrapper"
              />
          </template>
      </draggable>
      
      <!-- Button -->
      <a :href="buttonUrlValue" class="pricing-button" :style="buttonStyles" @click.prevent>
        {{ buttonTextValue }}
      </a>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
import { Check, X } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: {
    type: Object,
    required: true
  }
})

const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

// Provide state to PricingFeatureBlock
provide('pricingState', {
    parentSettings: settings
})

const isFeatured = computed(() => getResponsiveValue(settings.value, 'featured', device.value))
const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value) || 'Plan')
const subtitleValue = computed(() => getResponsiveValue(settings.value, 'subtitle', device.value))
const currencyValue = computed(() => getResponsiveValue(settings.value, 'currency', device.value) || '$')
const priceValue = computed(() => getResponsiveValue(settings.value, 'price', device.value) || '0')
const periodValue = computed(() => getResponsiveValue(settings.value, 'period', device.value) || '/month')
const buttonTextValue = computed(() => getResponsiveValue(settings.value, 'buttonText', device.value) || 'Get Started')
const buttonUrlValue = computed(() => getResponsiveValue(settings.value, 'buttonUrl', device.value) || '#')
const featuredLabelValue = computed(() => getResponsiveValue(settings.value, 'featuredLabel', device.value) || 'Best Value')

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const cardStyles = computed(() => {
  const styles = {
    position: 'relative',
    overflow: 'hidden',
    transform: isFeatured.value ? 'scale(1.05)' : 'none',
    zIndex: isFeatured.value ? '1' : '0',
    transition: 'transform 0.3s ease'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const headerStyles = computed(() => {
  const styles = {
    backgroundColor: getResponsiveValue(settings.value, 'headerBackgroundColor', device.value) || '#2059ea',
    color: getResponsiveValue(settings.value, 'headerTextColor', device.value) || '#ffffff',
    padding: '24px',
    textAlign: 'center'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'title_', device.value))
  return styles
})

const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))

const priceStyles = computed(() => {
  const styles = {
    padding: '24px',
    textAlign: 'center',
    display: 'flex',
    alignItems: 'baseline',
    justifyContent: 'center',
    gap: '4px',
    color: getResponsiveValue(settings.value, 'priceColor', device.value) || '#2059ea'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'price_', device.value))
  return styles
})

const featureStyles = computed(() => ({
  color: getResponsiveValue(settings.value, 'textColor', device.value) || '#666666'
}))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || '#2059ea'
  
  return {
    ...styles,
    display: 'block',
    margin: '24px',
    padding: '14px 24px',
    backgroundColor: bgColor,
    textAlign: 'center',
    textDecoration: 'none',
    borderRadius: '6px',
    transition: 'transform 0.2s ease, opacity 0.2s ease'
  }
})
</script>

<style scoped>
.pricing-block { width: 100%; }
.pricing-card { position: relative; }
.pricing-badge { position: absolute; top: 12px; right: -30px; background: #ff6b6b; color: white; padding: 4px 40px; font-size: 12px; font-weight: 600; transform: rotate(45deg); z-index: 10; }
.pricing-title { margin: 0; }
.pricing-subtitle { margin: 8px 0 0; opacity: 0.8; }
.price-amount { font-size: 56px; font-weight: 700; line-height: 1; }
.price-currency { font-size: 24px; font-weight: 500; }
.price-period { font-size: 16px; opacity: 0.7; }
.pricing-features { list-style: none; padding: 0 24px; margin: 0; }
.pricing-feature { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.pricing-feature:last-child { border-bottom: none; }
.pricing-feature--disabled { opacity: 0.5; text-decoration: line-through; }
.feature-icon { width: 18px; height: 18px; flex-shrink: 0; }
.feature-icon--check { color: #22c55e; }
.feature-icon--x { color: #ef4444; }
.pricing-button:hover { transform: translateY(-2px); opacity: 0.9; }
</style>
