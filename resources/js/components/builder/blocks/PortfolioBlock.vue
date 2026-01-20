<template>
  <div class="portfolio-block" :style="wrapperStyles">
    <h2 
      v-if="settings.title || builder?.isEditing" 
      class="portfolio-title" 
      :style="titleStyles"
      contenteditable="true"
      @blur="e => builder.updateModuleSetting(module.id, 'title', e.target.innerText)"
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
          <Layers class="placeholder-icon" />
        </div>
        <div class="item-overlay" :style="overlayStyles">
          <span v-if="settings.showCategory !== false" class="item-category" :style="categoryStyles">{{ item.category }}</span>
          <h4 v-if="settings.showTitle !== false" class="item-title" :style="titleStyles">{{ item.title }}</h4>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Layers } from 'lucide-vue-next'
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

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const categories = ['All', 'Web', 'Mobile', 'Branding']
const activeFilter = ref('All')
const hoverEffect = computed(() => settings.value.hoverEffect || 'overlay')

const mockItems = computed(() => Array.from({ length: settings.value.itemsPerPage || 9 }, (_, i) => ({
  id: i + 1, title: `Project ${i + 1}`, category: categories[1 + (i % 3)]
})))

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

const gridStyles = computed(() => {
    const cols = getResponsiveValue(settings.value, 'columns', device.value) || 3
    const gap = getResponsiveValue(settings.value, 'gap', device.value) || 20
    return { 
        display: 'grid', 
        gridTemplateColumns: `repeat(${cols}, 1fr)`, 
        gap: `${gap}px` 
    }
})

const itemStyles = computed(() => {
  const styles = { position: 'relative', overflow: 'hidden', cursor: 'pointer' }
  // Item overrides could be added here if needed, or if border applies to block vs items. 
  // Assuming border mostly applies to block in this case, but let's see. 
  // Based on Portfolio.js, border is in the main design tab, so it likely applies to the wrapper.
  return styles
})

const imageStyles = computed(() => {
  const ratio = { '1:1': '100%', '4:3': '75%', '16:9': '56.25%' }[settings.value.imageAspectRatio] || '100%'
  return { paddingTop: ratio, backgroundColor: '#f0f0f0', position: 'relative' }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || 'rgba(32, 89, 234, 0.9)', 
    color: settings.value.textColor || '#ffffff' 
}))

const filterStyles = computed(() => getTypographyStyles(settings.value, 'filter_', device.value))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const categoryStyles = computed(() => getTypographyStyles(settings.value, 'category_', device.value))

</script>

<style scoped>
.portfolio-block { width: 100%; }
.portfolio-title {
    margin-bottom: 24px;
    text-align: center;
    width: 100%;
}
.portfolio-filter { display: flex; gap: 8px; justify-content: center; margin-bottom: 24px; flex-wrap: wrap; }
.filter-btn { padding: 8px 20px; border: 1px solid #e0e0e0; background: #fff; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.filter-btn--active, .filter-btn:hover { background: #2059ea; color: #fff; border-color: #2059ea; }
.portfolio-item { position: relative; }
.item-image { display: flex; align-items: center; justify-content: center; }
.placeholder-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; color: #ccc; }
.item-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.portfolio-item:hover .item-overlay { opacity: 1; }
.item-category { text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
.item-title { margin: 8px 0 0; }
.portfolio-item--zoom:hover .item-image { transform: scale(1.1); transition: transform 0.3s; }
.portfolio-item--grayscale .item-image { filter: grayscale(100%); }
.portfolio-item--grayscale:hover .item-image { filter: grayscale(0); }
</style>
