<template>
  <div class="fullwidth-portfolio-block">
    <div class="portfolio-carousel" :style="carouselStyles">
      <div v-for="(item, i) in mockItems" :key="i" class="portfolio-slide" :class="{ 'portfolio-slide--active': currentSlide === i }">
        <div class="slide-image"><FolderOpen class="placeholder-icon" /></div>
        <div class="slide-overlay" :style="overlayStyles">
          <h3 v-if="settings.showTitle !== false" class="slide-title" :style="titleStyles">{{ item.title }}</h3>
          <span v-if="settings.showMeta !== false" class="slide-meta" :style="metaStyles">{{ item.category }}</span>
        </div>
      </div>
    </div>
    
    <button v-if="settings.showArrows !== false" class="nav-arrow nav-arrow--prev" @click="prev"><ChevronLeft /></button>
    <button v-if="settings.showArrows !== false" class="nav-arrow nav-arrow--next" @click="next"><ChevronRight /></button>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import { FolderOpen, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const currentSlide = ref(0)
let interval = null

const mockItems = Array.from({ length: 8 }, (_, i) => ({ title: `Project ${i + 1}`, category: 'Category' }))

const next = () => { currentSlide.value = (currentSlide.value + 1) % mockItems.length }
const prev = () => { currentSlide.value = currentSlide.value === 0 ? mockItems.length - 1 : currentSlide.value - 1 }

onMounted(() => { if (settings.value.autoplay !== false) interval = setInterval(next, settings.value.autoplaySpeed || 4000) })
onUnmounted(() => { if (interval) clearInterval(interval) })

const carouselStyles = computed(() => {
  const styles = { display: 'flex', minHeight: '400px', overflow: 'hidden', position: 'relative' }
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

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.6)',
  position: 'absolute',
  inset: 0,
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  opacity: 0,
  transition: 'opacity 0.3s'
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
</script>

<style scoped>
.fullwidth-portfolio-block { width: 100%; position: relative; }
.portfolio-slide { flex: 0 0 25%; position: relative; overflow: hidden; }
.slide-image { width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; }
.placeholder-icon { width: 48px; height: 48px; color: #ccc; }
.portfolio-slide:hover .slide-overlay { opacity: 1; }
.slide-title { margin: 0 0 4px; }
.slide-meta { opacity: 0.8; }
.nav-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; cursor: pointer; transition: background 0.2s; }
.nav-arrow:hover { background: white; }
.nav-arrow--prev { left: 20px; }
.nav-arrow--next { right: 20px; }
</style>
