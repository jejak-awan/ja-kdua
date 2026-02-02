<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-portfolio-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Portfolio'"
    :style="cardStyles"
  >
    <div class="portfolio-carousel relative w-full overflow-hidden" :style="containerStyles">
      <div 
        class="carousel-track flex transition-transform duration-500 ease-in-out"
        :style="trackStyles"
      >
        <div 
          v-for="(item, i) in portfolioItems" 
          :key="i" 
          class="portfolio-slide relative group overflow-hidden aspect-[4/3]" 
          :style="{ flex: `0 0 ${100 / columns}%` }"
        >
          <div class="slide-image w-full h-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center transition-transform duration-700 group-hover:scale-110">
            <img v-if="item.image" :src="item.image" :alt="item.title" class="object-cover w-full h-full" />
            <FolderOpen v-else class="w-12 h-12 text-slate-300 dark:text-slate-600 opacity-50" />
          </div>
          
          <div class="slide-overlay absolute inset-0 z-10 flex flex-col items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-8 text-center" :style="overlayStyles">
            <h3 
              v-if="settings.showTitle !== false" 
              class="slide-title mb-2 transition-[width] duration-500 translate-y-4 group-hover:translate-y-0" 
              :style="titleStyles"
            >
{{ item.title }}
</h3>
            <span 
              v-if="settings.showMeta !== false" 
              class="slide-meta opacity-80 transition-[width] duration-500 translate-y-4 group-hover:translate-y-0 delay-100" 
              :style="metaStyles"
            >{{ item.category }}</span>
          </div>
        </div>
      </div>
      
      <!-- Navigation Arrows -->
      <button 
        v-if="settings.showArrows !== false && portfolioItems.length > columns" 
        class="nav-arrow absolute top-1/2 -translate-y-1/2 left-6 z-20 w-12 h-12 flex items-center justify-center bg-white/90 dark:bg-slate-900/90 border-0 rounded-full cursor-pointer shadow-lg hover:bg-white transition-colors duration-300 group/arrow" 
        @click="prev"
      >
        <ChevronLeft class="w-6 h-6 text-slate-900 dark:text-slate-100 group-hover/arrow:-translate-x-1 transition-transform" />
      </button>
      <button 
        v-if="settings.showArrows !== false && portfolioItems.length > columns" 
        class="nav-arrow absolute top-1/2 -translate-y-1/2 right-6 z-20 w-12 h-12 flex items-center justify-center bg-white/90 dark:bg-slate-900/90 border-0 rounded-full cursor-pointer shadow-lg hover:bg-white transition-colors duration-300 group/arrow" 
        @click="next"
      >
        <ChevronRight class="w-6 h-6 text-slate-900 dark:text-slate-100 group-hover/arrow:translate-x-1 transition-transform" />
      </button>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
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
const currentSlide = ref(0)
let autoplayInterval: any = null

const columns = computed(() => {
    const val = getResponsiveValue(settings.value, 'columns', props.device)
    return typeof val === 'number' ? val : (parseInt(val) || 4)
})

const portfolioItems = computed(() => {
    if (props.module.children && props.module.children.length > 0) {
        return props.module.children.map(child => ({
            title: (child.settings?.title as string) || 'Project Title',
            category: (child.settings?.category as string) || 'Category',
            image: (child.settings?.image as string) || null
        }))
    }
    return Array.from({ length: 8 }, (_, i) => ({ 
        title: `Project ${i + 1}`, 
        category: 'Category',
        image: null
    }))
})

const next = () => { 
    const max = Math.max(0, portfolioItems.value.length - columns.value)
    currentSlide.value = currentSlide.value >= max ? 0 : currentSlide.value + 1 
}

const prev = () => { 
    const max = Math.max(0, portfolioItems.value.length - columns.value)
    currentSlide.value = currentSlide.value <= 0 ? max : currentSlide.value - 1 
}

onMounted(() => { 
    if (settings.value.autoplay !== false && portfolioItems.value.length > columns.value) {
        autoplayInterval = setInterval(next, settings.value.autoplaySpeed || 4000) 
    }
})

onUnmounted(() => { if (autoplayInterval) clearInterval(autoplayInterval) })

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return { 
        ...getLayoutStyles(settings.value, props.device),
        width: '100%'
    }
})

const trackStyles = computed(() => ({
    transform: `translateX(-${(currentSlide.value * (100 / columns.value))}%)`,
}))

const overlayStyles = computed(() => ({ 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.6)'
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))
</script>

<style scoped>
.fullwidth-portfolio-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-portfolio-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
