<template>
  <BaseBlock :module="module" :settings="settings" class="filterable-portfolio-block">
    <div class="container mx-auto">
      <!-- Filter Tabs -->
      <div v-if="settings.showFilter !== false" class="portfolio-filter mb-12 flex flex-wrap gap-2" :style="filterStyles">
        <button 
          v-for="cat in categories" 
          :key="cat" 
          class="filter-btn px-6 py-2 rounded-full text-sm font-medium transition-all" 
          :class="activeFilter === cat ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700'" 
          :style="activeFilter === cat ? {} : filterBtnStyles"
          @click="activeFilter = cat"
        >
          {{ cat }}
        </button>
      </div>
      
      <!-- Portfolio Grid -->
      <div class="portfolio-grid grid gap-6" :style="gridStyles">
        <article 
            v-for="(item, i) in filteredItems" 
            :key="i" 
            class="portfolio-item relative aspect-square rounded-xl overflow-hidden group cursor-pointer"
            @click="handleItemClick(item)"
        >
          <div class="item-image w-full h-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center transition-transform duration-500 group-hover:scale-110">
            <img v-if="item.image" :src="item.image" :alt="item.title" class="w-full h-full object-cover" />
            <FolderOpen v-else class="w-10 h-10 text-gray-300" />
          </div>
          <div class="item-overlay absolute inset-0 flex flex-col items-center justify-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity duration-300" :style="overlayStyles">
            <h4 v-if="settings.showTitle !== false" class="item-title mb-1 font-bold text-white" :style="itemTitleStyles">{{ item.title }}</h4>
            <span v-if="settings.showCategories !== false" class="item-category text-sm text-white/80" :style="itemCategoryStyles">{{ item.category }}</span>
          </div>
        </article>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { FolderOpen } from 'lucide-vue-next'
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

const activeFilter = ref('All')

// Dynamic data injection
const injectedPortfolioItems = inject('portfolioItems', [
  { title: 'Modern Architecture', category: 'Design', image: 'https://picsum.photos/600/600?random=11' },
  { title: 'Mobile Banking App', category: 'Development', image: 'https://picsum.photos/600/600?random=12' },
  { title: 'Brand Identity', category: 'Branding', image: 'https://picsum.photos/600/600?random=13' },
  { title: 'Social Campaign', category: 'Marketing', image: 'https://picsum.photos/600/600?random=14' },
  { title: 'Web Dashboard', category: 'Design', image: 'https://picsum.photos/600/600?random=15' },
  { title: 'Ecommerce Platform', category: 'Development', image: 'https://picsum.photos/600/600?random=16' },
  { title: 'Visual Arts', category: 'Design', image: 'https://picsum.photos/600/600?random=17' },
  { title: 'Startup Launch', category: 'Branding', image: 'https://picsum.photos/600/600?random=18' }
])

const allLabel = computed(() => getResponsiveValue(settings.value, 'allLabel', device.value) || 'All')

const categories = computed(() => {
    const cats = new Set([allLabel.value])
    injectedPortfolioItems.forEach(item => cats.add(item.category))
    return Array.from(cats)
})

const filteredItems = computed(() => {
  const count = getResponsiveValue(settings.value, 'postsCount', device.value) || 12
  let items = injectedPortfolioItems
  if (activeFilter.value !== allLabel.value) {
      items = items.filter(i => i.category === activeFilter.value)
  }
  return items.slice(0, count)
})

const handleItemClick = (item) => {
    if (props.mode === 'edit') return
    if (item.url) window.location.href = item.url
}

const filterStyles = computed(() => {
    const alignment = getResponsiveValue(settings.value, 'filterAlignment', device.value) || 'center'
    return { 
        justifyContent: alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start')
    }
})

const filterBtnStyles = computed(() => getTypographyStyles(settings.value, 'filter_', device.value))

const gridStyles = computed(() => ({ 
  gridTemplateColumns: `repeat(${getResponsiveValue(settings.value, 'columns', device.value) || 4}, 1fr)`
}))

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'rgba(59, 130, 246, 0.9)' 
}))

const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const itemCategoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', device.value))
</script>

<style scoped>
.filterable-portfolio-block { width: 100%; }
.portfolio-filter { width: 100%; }
</style>
