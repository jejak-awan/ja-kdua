<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="portfolio-block" :style="portfolioBlockStyles">
        <h2 
          v-if="settings.title || mode === 'edit'" 
          class="portfolio-title" 
          :style="titleStyles"
          :contenteditable="mode === 'edit'"
          @blur="e => updateResponsiveField('title', e.target.innerText)"
        >
          {{ settings.title }}
        </h2>
        <!-- Filter -->
        <div v-if="settings.showFilter !== false" class="portfolio-filter">
          <button v-for="cat in categories" :key="cat" class="filter-btn" :class="{ 'filter-btn--active': activeFilter === cat }" :style="filterStyles" @click="activeFilter = cat">{{ cat }}</button>
        </div>
        
        <!-- Grid -->
        <div class="portfolio-grid" :style="gridStyles">
          <div v-for="item in mockItems" :key="item.id" class="portfolio-item" :class="`portfolio-item--${hoverEffect}`" :style="itemStyles">
            <div class="item-image" :style="imageStyles">
              <LucideIcon name="Layers" class="placeholder-icon" />
            </div>
            <div class="item-overlay" :style="overlayStyles">
              <span v-if="settings.showCategory !== false" class="item-category" :style="categoryStyles">{{ item.category }}</span>
              <h4 v-if="settings.showTitle !== false" class="item-title" :style="titleStyles">{{ item.title }}</h4>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

const categories = ['All', 'Web', 'Mobile', 'Branding']
const activeFilter = ref('All')
const hoverEffect = computed(() => settings.value.hoverEffect || 'overlay')

const mockItems = computed(() => {
    const count = settings.value.itemsPerPage || 9
    return Array.from({ length: count }, (_, i) => ({
        id: i + 1, title: `Project ${i + 1}`, category: categories[1 + (i % 3)]
    }))
})

const portfolioBlockStyles = computed(() => ({ width: '100%' }))

const gridStyles = computed(() => {
    const cols = getVal(settings.value, 'columns', props.device) || 3
    const gap = getVal(settings.value, 'gap', props.device) || 20
    return { 
        display: 'grid', 
        gridTemplateColumns: `repeat(${cols}, 1fr)`, 
        gap: `${gap}px`,
        width: '100%'
    }
})

const itemStyles = computed(() => ({ position: 'relative', overflow: 'hidden', cursor: 'pointer' }))

const imageStyles = computed(() => {
  const ratioMap = { '1:1': '100%', '4:3': '75%', '16:9': '56.25%' }
  const ratio = ratioMap[settings.value.imageAspectRatio] || '100%'
  return { paddingTop: ratio, backgroundColor: '#f0f0f0', position: 'relative' }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || 'rgba(32, 89, 234, 0.9)', 
    color: settings.value.textColor || '#ffffff' 
}))

const filterStyles = computed(() => getTypographyStyles(settings.value, 'filter_', props.device))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const categoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.portfolio-block { width: 100%; }
.portfolio-title { margin-bottom: 24px; text-align: center; width: 100%; }
.portfolio-filter { display: flex; gap: 8px; justify-content: center; margin-bottom: 24px; flex-wrap: wrap; }
.filter-btn { padding: 8px 20px; border: 1px solid #e0e0e0; background: #fff; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.filter-btn--active, .filter-btn:hover { background: var(--theme-primary-color, #2059ea); color: #fff; border-color: var(--theme-primary-color, #2059ea); }
.portfolio-item { position: relative; }
.placeholder-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; color: #ccc; }
.item-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.portfolio-item:hover .item-overlay { opacity: 1; }
.item-category { text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
.item-title { margin: 8px 0 0; }
.portfolio-item--zoom:hover .item-image { transform: scale(1.1); transition: transform 0.3s; }
.portfolio-item--grayscale .item-image { filter: grayscale(100%); }
.portfolio-item--grayscale:hover .item-image { filter: grayscale(0); }
</style>
