<template>
  <div class="pricing-tables" :style="wrapperStyles">
    <div class="pricing-grid" :style="gridStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="pricing-card" 
        :class="{ 'pricing-card--featured': item.isFeatured }"
        :style="getCardStyles(item)"
      >
        <!-- Featured Badge -->
        <div v-if="item.isFeatured" class="pricing-badge" :style="{ backgroundColor: accentColor }">
          Featured
        </div>
        
        <!-- Header -->
        <div class="pricing-header" :style="getHeaderStyles(item)">
          <h3 class="pricing-title" :style="titleStyles">{{ item.title || 'Plan' }}</h3>
        </div>
        
        <!-- Price -->
        <div class="pricing-price" :style="priceStyles">
          <span class="price-currency">{{ item.currency || '$' }}</span>
          <span class="price-amount">{{ item.price || '0' }}</span>
          <span class="price-period">{{ item.period || '/mo' }}</span>
        </div>
        
        <!-- Features -->
        <ul class="pricing-features">
          <li v-for="(feature, fIndex) in parseFeatures(item.features)" :key="fIndex" class="pricing-feature">
            <Check :size="14" class="check-icon" :style="{ color: accentColor }" />
            <span>{{ feature }}</span>
          </li>
        </ul>
        
        <!-- Button -->
        <div class="pricing-footer">
            <a :href="item.buttonUrl || '#'" class="pricing-button" :style="getButtonStyles(item)" @click.prevent>
              {{ item.buttonText || 'Get Started' }}
            </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Check } from 'lucide-vue-next'
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
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const columns = computed(() => getResponsiveValue(settings.value, 'columns', device.value) || 3)
const gap = computed(() => getResponsiveValue(settings.value, 'gap', device.value) || 24)
const accentColor = computed(() => getResponsiveValue(settings.value, 'accentColor', device.value) || '#2059ea')

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const gridStyles = computed(() => ({
  display: 'grid',
  gridTemplateColumns: `repeat(${columns.value}, 1fr)`,
  gap: `${gap.value}px`
}))

const getCardStyles = (item) => {
  const isFeatured = item.isFeatured
  return {
    backgroundColor: isFeatured 
        ? (getResponsiveValue(settings.value, 'featuredCardBackgroundColor', device.value) || '#ffffff')
        : (getResponsiveValue(settings.value, 'cardBackgroundColor', device.value) || '#ffffff'),
    borderRadius: '16px',
    overflow: 'hidden',
    display: 'flex',
    flexDirection: 'column',
    transition: 'all 0.3s ease',
    boxShadow: isFeatured ? '0 10px 25px -5px rgba(0,0,0,0.1)' : '0 4px 6px -1px rgba(0,0,0,0.05)',
    border: isFeatured ? `2px solid ${accentColor.value}` : '1px solid #f1f5f9',
    position: 'relative',
    transform: isFeatured ? 'scale(1.05)' : 'none',
    zIndex: isFeatured ? '10' : '1'
  }
}

const getHeaderStyles = (item) => ({
  padding: '32px 24px 16px',
  textAlign: 'center'
})

const getButtonStyles = (item) => ({
  backgroundColor: item.isFeatured ? accentColor.value : '#f1f5f9',
  color: item.isFeatured ? '#ffffff' : '#1e293b',
  padding: '12px 24px',
  borderRadius: '8px',
  fontWeight: '600',
  textDecoration: 'none',
  display: 'block',
  textAlign: 'center',
  transition: 'all 0.2s ease'
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const priceStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'price_', device.value)
    return { ...styles, textAlign: 'center', padding: '0 24px 24px' }
})

const parseFeatures = (features) => {
  if (!features) return []
  return features.split('\n').filter(f => f.trim() !== '')
}
</script>

<style scoped>
.pricing-tables { width: 100%; }
.pricing-grid { width: 100%; margin: 0 auto; }
.pricing-card { background: white; }

.pricing-badge {
  position: absolute;
  top: 12px;
  right: -30px;
  color: white;
  padding: 4px 40px;
  font-size: 10px;
  font-weight: 800;
  transform: rotate(45deg);
  text-transform: uppercase;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.pricing-title { margin: 0; font-size: 1.25rem; font-weight: 700; }

.pricing-price { display: flex; align-items: baseline; justify-content: center; gap: 2px; color: #0f172a; }
.price-currency { font-size: 1.5rem; font-weight: 600; }
.price-amount { font-size: 3rem; font-weight: 800; line-height: 1; }
.price-period { font-size: 0.875rem; opacity: 0.6; }

.pricing-features { list-style: none; padding: 0 24px 24px; margin: 0; flex: 1; }
.pricing-feature { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; margin-bottom: 12px; color: #475569; }
.check-icon { flex-shrink: 0; }

.pricing-footer { padding: 24px; margin-top: auto; }
.pricing-button:hover { filter: brightness(0.95); transform: translateY(-1px); }
.pricing-button:active { transform: translateY(0); }
</style>
