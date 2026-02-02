<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="filterable-portfolio-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Filterable Portfolio'"
    :style="cardStyles"
  >
    <div class="portfolio-container w-full" :style="containerStyles">
      <!-- Filter Tabs -->
      <div v-if="settings.showFilter !== false" class="portfolio-filter mb-16 flex flex-wrap gap-4" :style="filterStyles">
        <Button 
          v-for="cat in categories" 
          :key="cat" 
          :variant="activeFilter === cat ? 'default' : 'outline'"
          class="rounded-full px-8 py-6 font-black uppercase tracking-widest text-[10px] transition-[width] duration-500 shadow-xl hover:shadow-2xl" 
          :style="activeFilter === cat ? {} : filterBtnStyles"
          @click="activeFilter = cat"
        >
          {{ cat }}
        </Button>
      </div>
      
      <!-- Portfolio Grid -->
      <div class="portfolio-grid grid transition-[width] duration-500" :style="gridStyles">
        <Card 
            v-for="(item, i) in filteredItems" 
            :key="i" 
            class="portfolio-item relative aspect-square rounded-[3.5rem] overflow-hidden group cursor-pointer border-none shadow-2xl transition-colors duration-700 hover:-translate-y-4"
            @click="handleItemClick(item)"
        >
          <div class="item-background absolute inset-0 z-0 bg-slate-50 dark:bg-slate-950 flex items-center justify-center transition-transform duration-1000 group-hover:scale-110">
            <img v-if="item.image" :src="item.image" :alt="item.title" class="w-full h-full object-cover transition-opacity duration-700 group-hover:opacity-40" />
            <FolderOpen v-else class="w-24 h-24 text-slate-100 dark:text-slate-800 opacity-30" />
          </div>
          
          <div 
            class="item-overlay absolute inset-0 z-20 flex flex-col items-center justify-center p-12 text-center opacity-0 group-hover:opacity-100 transition-colors duration-700 backdrop-blur-xl bg-primary/80" 
            :style="overlayStyles"
          >
            <Badge 
                v-if="settings.showCategories !== false" 
                variant="secondary"
                class="mb-6 rounded-2xl font-black px-6 py-2 text-[10px] uppercase tracking-[0.3em] bg-white/10 text-white border-white/20 backdrop-blur-md shadow-2xl transform -translate-y-4 group-hover:translate-y-0 transition-transform duration-500"
                :style="itemCategoryStyles"
            >
                {{ item.category }}
            </Badge>
            <h4 
                v-if="settings.showTitle !== false" 
                class="item-title text-white text-3xl font-black tracking-tighter leading-none max-w-xs transform -translate-y-2 group-hover:translate-y-0 transition-transform duration-500 delay-75" 
                :style="itemTitleStyles"
            >
                {{ item.title }}
            </h4>
            
            <div class="mt-10 w-16 h-16 rounded-3xl bg-white text-primary flex items-center justify-center transform translate-y-8 group-hover:translate-y-0 transition-colors duration-700 delay-150 shadow-2xl hover:scale-110">
                 <LucideIcon name="ArrowUpRight" class="w-7 h-7" />
            </div>
          </div>
        </Card>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button, Badge, Card } from '../ui'
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';import { LucideIcon } from '@/components/ui';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const activeFilter = ref('All')

// Mock dynamic data
const injectedPortfolioItems = inject<any[]>('portfolioItems', [
  { title: 'Luminous Branding', category: 'Branding', image: 'https://picsum.photos/800/800?random=21' },
  { title: 'Nexus UI Design', category: 'Design', image: 'https://picsum.photos/800/800?random=22' },
  { title: 'Aura App Dev', category: 'Development', image: 'https://picsum.photos/800/800?random=23' },
  { title: 'Cyber Pulse Dev', category: 'Development', image: 'https://picsum.photos/800/800?random=24' },
  { title: 'Flux Visual Arts', category: 'Design', image: 'https://picsum.photos/800/800?random=25' },
  { title: 'Zenith Identity', category: 'Branding', image: 'https://picsum.photos/800/800?random=26' },
  { title: 'Infinity Web', category: 'Design', image: 'https://picsum.photos/800/800?random=27' },
  { title: 'Nova Marketing', category: 'Strategy', image: 'https://picsum.photos/800/800?random=28' }
])

const allLabel = computed(() => getVal(settings.value, 'allLabel', props.device) || 'All')

const categories = computed(() => {
    const cats = new Set([allLabel.value])
    injectedPortfolioItems.forEach(item => cats.add(item.category))
    return Array.from(cats)
})

const filteredItems = computed(() => {
  const count = getVal(settings.value, 'postsCount', props.device) || 12
  let items = injectedPortfolioItems
  if (activeFilter.value !== allLabel.value) {
      items = items.filter(i => i.category === activeFilter.value)
  }
  return items.slice(0, count)
})

const handleItemClick = (item: any) => {
    if (props.mode === 'edit') return
    if (item.url) window.location.href = item.url
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const filterStyles = computed(() => {
    const alignment = getVal(settings.value, 'filterAlignment', props.device) || 'center'
    return { 
        justifyContent: alignment === 'center' ? 'center' : (alignment === 'right' ? 'flex-end' : 'flex-start')
    }
})

const filterBtnStyles = computed(() => getTypographyStyles(settings.value, 'filter_', props.device))

const gridStyles = computed(() => {
    const cols = getVal(settings.value, 'columns', props.device) || 4
    return { 
        display: 'grid',
        gridTemplateColumns: props.device === 'mobile' ? '1fr' : (props.device === 'tablet' ? 'repeat(2, 1fr)' : `repeat(${cols}, 1fr)`),
        gap: '2rem'
    }
})

const overlayStyles = computed(() => ({ 
  backgroundColor: getVal(settings.value, 'overlayColor', props.device) || '' 
}))

const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemCategoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', props.device))
</script>

<style scoped>
.filterable-portfolio-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.filterable-portfolio-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.portfolio-item:hover { z-index: 30; }
</style>
