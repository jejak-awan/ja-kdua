<template>
  <div class="filterable-portfolio-block" :style="wrapperStyles">
    <!-- Filter Tabs -->
    <div v-if="settings.showFilter !== false" class="portfolio-filter" :style="filterStyles">
      <button 
        v-for="cat in categories" 
        :key="cat" 
        class="filter-btn" 
        :class="{ 'filter-btn--active': activeFilter === cat }" 
        :style="filterBtnStyles"
        @click="activeFilter = cat"
      >
        {{ cat }}
      </button>
    </div>
    
    <!-- Portfolio Grid -->
    <div class="portfolio-grid" :style="gridStyles">
      <article v-for="(item, i) in filteredItems" :key="i" class="portfolio-item" :class="`portfolio-item--${settings.hoverEffect || 'zoom'}`">
        <div class="item-image">
          <FolderOpen class="placeholder-icon" />
        </div>
        <div class="item-overlay" :style="overlayStyles">
          <h4 v-if="settings.showTitle !== false" class="item-title" :style="itemTitleStyles">{{ item.title }}</h4>
          <span v-if="settings.showCategories !== false" class="item-category" :style="itemCategoryStyles">{{ item.category }}</span>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { FolderOpen } from 'lucide-vue-next'
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

const props = defineProps({ module: { type: Object, required: true } })
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')

const activeFilter = ref('All')

const categories = computed(() => [
  getResponsiveValue(settings.value, 'allLabel', device.value) || 'All', 
  'Design', 
  'Development', 
  'Branding', 
  'Marketing'
])

const mockItems = [
  { title: 'Project Alpha', category: 'Design' },
  { title: 'Project Beta', category: 'Development' },
  { title: 'Project Gamma', category: 'Branding' },
  { title: 'Project Delta', category: 'Marketing' },
  { title: 'Project Epsilon', category: 'Design' },
  { title: 'Project Zeta', category: 'Development' },
  { title: 'Project Eta', category: 'Design' },
  { title: 'Project Theta', category: 'Branding' }
]

const filteredItems = computed(() => {
  const allLabel = getResponsiveValue(settings.value, 'allLabel', device.value) || 'All'
  const count = getResponsiveValue(settings.value, 'postsCount', device.value) || 12
  if (activeFilter.value === allLabel) return mockItems.slice(0, count)
  return mockItems.filter(i => i.category === activeFilter.value).slice(0, count)
})

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

const filterStyles = computed(() => ({ 
  display: 'flex', 
  gap: '8px', 
  marginBottom: '24px', 
  justifyContent: (getResponsiveValue(settings.value, 'filterAlignment', device.value) || 'center') === 'center' ? 'center' : 
                  (getResponsiveValue(settings.value, 'filterAlignment', device.value) || 'center') === 'right' ? 'flex-end' : 'flex-start', 
  flexWrap: 'wrap' 
}))

const filterBtnStyles = computed(() => getTypographyStyles(settings.value, 'filter_', device.value))

const gridStyles = computed(() => ({ 
  display: 'grid', 
  gridTemplateColumns: `repeat(${getResponsiveValue(settings.value, 'columns', device.value) || 4}, 1fr)`, 
  gap: '16px' 
}))

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'rgba(32, 89, 234, 0.8)' 
}))

const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const itemCategoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', device.value))
</script>

<style scoped>
.filterable-portfolio-block { width: 100%; }
.filter-btn { padding: 8px 20px; background: #f0f0f0; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.filter-btn:hover, .filter-btn--active { background: #2059ea; color: white !important; }
.portfolio-item { position: relative; aspect-ratio: 1; overflow: hidden; border-radius: 8px; cursor: pointer; }
.item-image { width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; }
.placeholder-icon { width: 32px; height: 32px; color: #ccc; }
.item-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.portfolio-item:hover .item-overlay { opacity: 1; }
.portfolio-item--zoom:hover .item-image { transform: scale(1.1); }
</style>
