<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-portfolio-block">
    <div class="portfolio-carousel" :style="carouselStyles">
      <div 
        v-for="(item, i) in portfolioItems" 
        :key="i" 
        class="portfolio-slide" 
        :class="{ 'portfolio-slide--active': currentSlide === i }"
        :style="{ flex: `0 0 ${100 / columns}%` }"
      >
        <div class="slide-image">
          <img v-if="item.image" :src="item.image" :alt="item.title" class="object-cover w-full h-full" />
          <FolderOpen v-else class="placeholder-icon" />
        </div>
        <div class="slide-overlay" :style="overlayStyles">
          <h3 
            v-if="settings.showTitle !== false" 
            class="slide-title" 
            :style="titleStyles"
          >{{ item.title }}</h3>
          <span 
            v-if="settings.showMeta !== false" 
            class="slide-meta" 
            :style="metaStyles"
          >{{ item.category }}</span>
        </div>
      </div>
    </div>
    
    <button v-if="settings.showArrows !== false && portfolioItems.length > columns" class="nav-arrow nav-arrow--prev" @click="prev">
      <ChevronLeft />
    </button>
    <button v-if="settings.showArrows !== false && portfolioItems.length > columns" class="nav-arrow nav-arrow--next" @click="next">
      <ChevronRight />
    </button>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { FolderOpen, ChevronLeft, ChevronRight } from 'lucide-vue-next'
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

const currentSlide = ref(0)
let interval = null

const columns = computed(() => parseInt(getResponsiveValue(settings.value, 'columns', device.value)) || 4)

// Mock items or dynamic items if available
const portfolioItems = computed(() => {
    if (props.module.children?.length > 0) {
        return props.module.children.map(child => ({
            title: child.settings?.title || 'Project Title',
            category: child.settings?.category || 'Category',
            image: child.settings?.image
        }))
    }
    return Array.from({ length: 8 }, (_, i) => ({ 
        title: `Project ${i + 1}`, 
        category: 'Category',
        image: null
    }))
})

const next = () => { currentSlide.value = (currentSlide.value + 1) % portfolioItems.value.length }
const prev = () => { currentSlide.value = currentSlide.value === 0 ? portfolioItems.value.length - 1 : currentSlide.value - 1 }

onMounted(() => { 
    if (settings.value.autoplay !== false && portfolioItems.value.length > columns.value) {
        interval = setInterval(next, settings.value.autoplaySpeed || 4000) 
    }
})
onUnmounted(() => { if (interval) clearInterval(interval) })

const carouselStyles = computed(() => {
  return { 
    display: 'flex', 
    minHeight: '400px', 
    overflow: 'hidden', 
    position: 'relative',
    transform: `translateX(-${(currentSlide.value * (100 / columns.value)) % 100}%)`,
    transition: 'transform 0.5s ease-in-out'
  }
})

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.6)',
  position: 'absolute',
  inset: 0,
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  opacity: 0,
  transition: 'opacity 0.3s',
  zIndex: 10
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
</script>

<style scoped>
.fullwidth-portfolio-block { width: 100%; position: relative; overflow: hidden; }
.portfolio-slide { position: relative; overflow: hidden; aspect-ratio: 4/3; }
.slide-image { width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; }
.placeholder-icon { width: 48px; height: 48px; color: #ccc; }
.portfolio-slide:hover .slide-overlay { opacity: 1; }
.slide-title { margin: 0 0 4px; }
.slide-meta { opacity: 0.8; }
.nav-arrow { 
  position: absolute; 
  top: 50%; 
  transform: translateY(-50%); 
  z-index: 20; 
  width: 48px; 
  height: 48px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  background: rgba(255,255,255,0.9); 
  border: none; 
  border-radius: 50%; 
  cursor: pointer; 
  transition: background 0.2s; 
}
.nav-arrow:hover { background: white; }
.nav-arrow--prev { left: 20px; }
.nav-arrow--next { right: 20px; }
</style>
