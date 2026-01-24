<template>
  <BaseBlock :module="module" :settings="settings" class="filterable-portfolio-block">
    <div class="container mx-auto">
      <!-- Filter Tabs -->
      <div v-if="settings.showFilter !== false" class="portfolio-filter mb-12 flex flex-wrap gap-3" :style="filterStyles">
        <Button 
          v-for="cat in categories" 
          :key="cat" 
          :variant="activeFilter === cat ? 'default' : 'outline'"
          class="rounded-full px-6 transition-all duration-300" 
          :style="activeFilter === cat ? {} : filterBtnStyles"
          @click="activeFilter = cat"
        >
          {{ cat }}
        </Button>
      </div>
      
      <!-- Portfolio Grid -->
      <div class="portfolio-grid grid gap-8" :style="gridStyles">
        <Card 
            v-for="(item, i) in filteredItems" 
            :key="i" 
            class="portfolio-item relative aspect-square rounded-[30px] overflow-hidden group cursor-pointer border-none shadow-lg hover:shadow-2xl transition-all duration-500"
            @click="handleItemClick(item)"
        >
          <div class="item-image w-full h-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center transition-transform duration-700 group-hover:scale-110">
            <img v-if="item.image" :src="item.image" :alt="item.title" class="w-full h-full object-cover" />
            <FolderOpen v-else class="w-16 h-16 text-slate-300 opacity-50" />
          </div>
          
          <div 
            class="item-overlay absolute inset-0 z-10 flex flex-col items-center justify-center p-8 text-center opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-md bg-primary/80" 
            :style="overlayStyles"
          >
            <Badge 
                v-if="settings.showCategories !== false" 
                variant="secondary"
                class="mb-3 rounded-full uppercase tracking-widest text-[10px] font-bold py-1 px-4 bg-white/20 text-white border-white/30 backdrop-blur-md"
                :style="itemCategoryStyles"
            >
                {{ item.category }}
            </Badge>
            <h4 
                v-if="settings.showTitle !== false" 
                class="item-title text-white text-2xl font-black tracking-tighter" 
                :style="itemTitleStyles"
            >
                {{ item.title }}
            </h4>
            
            <div class="mt-6 w-12 h-12 rounded-full bg-white text-primary flex items-center justify-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100">
                 <LucideIcon name="ArrowUpRight" :size="20" />
            </div>
          </div>
        </Card>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button, Badge, Card } from '../ui'
import { FolderOpen, ArrowUpRight } from 'lucide-vue-next'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

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
  backgroundColor: settings.value.overlayColor || '' 
}))

const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const itemCategoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', device.value))
</script>

<style scoped>
.filterable-portfolio-block { width: 100%; }
.portfolio-filter { width: 100%; }
</style>
