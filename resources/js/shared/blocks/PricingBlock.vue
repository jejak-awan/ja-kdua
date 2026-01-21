<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="pricing-block-wrapper"
  >
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
        <div class="pricing-header">
          <h3 
            class="pricing-title" 
            :style="titleStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index, 'title', $event.target.innerText)"
            v-text="item.title || 'Plan Name'"
          ></h3>
        </div>
        
        <!-- Price -->
        <div class="pricing-price" :style="priceStyles">
          <span class="price-currency">{{ item.currency || '$' }}</span>
          <span 
            class="price-amount"
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index, 'price', $event.target.innerText)"
            v-text="item.price || '0'"
          ></span>
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
          <a 
            :href="item.buttonUrl || '#'" 
            class="pricing-button" 
            :style="getButtonStyles(item)" 
            @click.prevent
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index, 'buttonText', $event.target.innerText)"
            v-text="item.buttonText || 'Get Started'"
          ></a>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Check } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const items = computed(() => props.settings.items || [])
const columns = computed(() => parseInt(getVal(props.settings, 'columns')) || 3)
const gap = computed(() => parseInt(getVal(props.settings, 'gap')) || 24)
const accentColor = computed(() => getVal(props.settings, 'accentColor') || '#2059ea')

const gridStyles = computed(() => ({
  display: 'grid',
  gridTemplateColumns: `repeat(${columns.value}, 1fr)`,
  gap: `${gap.value}px`,
  width: '100%'
}))

const getCardStyles = (item) => {
  const isFeatured = item.isFeatured
  return {
    backgroundColor: isFeatured 
        ? (getVal(props.settings, 'featuredCardBackgroundColor') || '#ffffff')
        : (getVal(props.settings, 'cardBackgroundColor') || '#ffffff'),
    borderRadius: '16px',
    overflow: 'hidden',
    display: 'flex',
    flexDirection: 'column',
    transition: 'all 0.3s ease',
    boxShadow: isFeatured ? '0 10px 25px -5px rgba(0,0,0,0.1)' : '0 4px 6px -1px rgba(0,0,0,0.05)',
    border: isFeatured ? `2px solid ${accentColor.value}` : '1px solid #f1f5f9',
    position: 'relative',
    transform: (isFeatured && props.isPreview) || (isFeatured && props.mode === 'view') ? 'scale(1.05)' : 'none',
    zIndex: isFeatured ? '10' : '1'
  }
}

const getButtonStyles = (item) => ({
  backgroundColor: item.isFeatured ? accentColor.value : '#f1f5f9',
  color: item.isFeatured ? '#ffffff' : '#1e293b',
  padding: '12px 24px',
  borderRadius: '8px',
  fontWeight: '600',
  textDecoration: 'none',
  display: 'block',
  textAlign: 'center',
  transition: 'all 0.2s ease',
  outline: 'none'
})

const titleStyles = computed(() => getTypographyStyles(props.settings, 'title_'))
const priceStyles = computed(() => {
    const styles = getTypographyStyles(props.settings, 'price_')
    return { ...styles, textAlign: 'center', padding: '0 24px 16px' }
})

const parseFeatures = (features) => {
  if (!features) return []
  if (Array.isArray(features)) return features
  return features.split('\n').filter(f => f.trim() !== '')
}

const updateItemField = (index, key, value) => {
  if (props.mode !== 'edit' || !builder) return
  const newItems = [...items.value]
  newItems[index] = { ...newItems[index], [key]: value }
  builder.updateModuleSettings(props.id, { items: newItems })
}
</script>

<style scoped>
.pricing-grid {
  width: 100%;
}

.pricing-card {
  background: white;
  min-height: 400px;
}

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
  pointer-events: none;
}

.pricing-header {
  padding: 32px 24px 16px;
  text-align: center;
}

.pricing-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  outline: none;
}

.pricing-price {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 2px;
  color: #0f172a;
}

.price-currency {
  font-size: 1.5rem;
  font-weight: 600;
}

.price-amount {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1;
  outline: none;
}

.price-period {
  font-size: 0.875rem;
  opacity: 0.6;
}

.pricing-features {
  list-style: none;
  padding: 0 24px 24px;
  margin: 0;
  flex: 1;
}

.pricing-feature {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  margin-bottom: 12px;
  color: #475569;
}

.check-icon {
  flex-shrink: 0;
}

.pricing-footer {
  padding: 24px;
  margin-top: auto;
}

.pricing-button:hover {
  filter: brightness(0.95);
  transform: translateY(-1px);
}

.pricing-button:active {
  transform: translateY(0);
}

@media (max-width: 768px) {
  .pricing-grid {
    grid-template-columns: 1fr !important;
  }
  .pricing-card--featured {
    transform: none !important;
  }
}
</style>
