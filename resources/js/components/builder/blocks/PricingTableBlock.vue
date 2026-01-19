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
    overflow: 'visible',
    transform: isFeatured.value ? 'scale(1.05)' : 'none',
    zIndex: isFeatured.value ? '10' : '1',
    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
    borderRadius: '16px'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  
  if (!settings.value.boxShadow) {
    styles.boxShadow = isFeatured.value ? 'var(--shadow-premium)' : 'var(--shadow-lg)'
  } else {
    Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  }
  
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
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    margin: '32px 24px 24px',
    padding: '16px 32px',
    backgroundColor: bgColor,
    textAlign: 'center',
    textDecoration: 'none',
    borderRadius: '12px',
    fontWeight: '700',
    letterSpacing: '0.01em',
    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
  }
})
</script>

<style scoped>
.pricing-block { width: 100%; padding: 40px 20px; }
.pricing-card { position: relative; background: #ffffff; }
.pricing-card:hover { transform: translateY(-8px) scale(1.02); }
.pricing-card--featured:hover { transform: translateY(-8px) scale(1.07); }

.pricing-badge { 
  position: absolute; 
  top: 20px; 
  right: 20px; 
  background: linear-gradient(135deg, #ff6b6b, #ee5253); 
  color: white; 
  padding: 6px 16px; 
  font-size: 11px; 
  font-weight: 800; 
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-radius: 20px;
  z-index: 10; 
  box-shadow: 0 4px 12px rgba(238, 82, 83, 0.3);
}

.pricing-header { border-radius: 16px 16px 0 0; }
.pricing-title { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9; }
.pricing-subtitle { margin: 8px 0 0; font-size: 13px; opacity: 0.7; }

.pricing-price { padding: 40px 24px; color: #0f172a !important; }
.price-amount { font-size: 64px; font-weight: 800; line-height: 1; letter-spacing: -0.02em; }
.price-currency { font-size: 24px; font-weight: 600; vertical-align: super; margin-right: 2px; }
.price-period { font-size: 14px; font-weight: 500; opacity: 0.5; margin-left: 4px; }

.pricing-features { list-style: none; padding: 0 32px; margin: 0; }
.pricing-feature-wrapper { 
  padding: 16px 0; 
  border-bottom: 1px solid rgba(0,0,0,0.04); 
  transition: all 0.2s ease;
}
.pricing-feature-wrapper:last-child { border-bottom: none; }
.pricing-feature-wrapper:hover { background: rgba(0,0,0,0.01); padding-left: 4px; }

.pricing-button:hover { 
  transform: translateY(-2px); 
  box-shadow: 0 10px 20px -10px rgba(var(--builder-accent-rgb), 0.5);
  filter: brightness(1.1);
}
.pricing-button:active { transform: translateY(0); }
</style>
