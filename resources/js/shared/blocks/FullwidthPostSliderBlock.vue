<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-post-slider-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Post Slider'"
    :style="cardStyles"
  >
    <div class="slider-container relative w-full overflow-hidden" :style="{ height: containerHeight }">
      <Carousel 
          class="w-full h-full" 
          :opts="carouselOptions"
          :plugins="carouselPlugins"
      >
          <CarouselContent class="-ml-0 h-full">
              <CarouselItem 
                  v-for="(post, index) in displayPosts" 
                  :key="index"
                  class="pl-0 basis-full h-full"
              >
                  <div class="relative w-full h-full group overflow-hidden">
                      <!-- Background Image with Zoom Effect -->
                      <div class="absolute inset-0 z-0 bg-slate-900 transition-transform duration-[2000ms] group-hover:scale-110">
                          <img v-if="post.image" :src="post.image" class="w-full h-full object-cover" :alt="post.title" />
                      </div>
                      
                      <!-- Dynamic Overlay -->
                      <div class="absolute inset-0 z-10 transition-opacity duration-700" :style="overlayStyles" />
                      
                      <!-- Content -->
                      <div 
                        class="relative z-20 h-full w-full flex flex-col items-center justify-center text-center px-8 md:px-24 text-white transition-all duration-700"
                        :class="[
                          settings.contentPosition === 'top' ? 'justify-start pt-24' : 
                          settings.contentPosition === 'bottom' ? 'justify-end pb-24' : 'justify-center'
                        ]"
                      >
                          <!-- Meta Info -->
                          <div v-if="settings.showMeta !== false" class="post-meta mb-8 flex flex-wrap justify-center gap-4 animate-in fade-in slide-in-from-bottom-4 duration-700" :style="metaStyles">
                               <Badge v-if="settings.showCategories !== false" class="bg-primary hover:bg-primary border-none text-white font-bold py-1 px-4 rounded-full uppercase tracking-widest text-[10px]">
                                  {{ post.category }}
                               </Badge>
                               <div class="flex items-center gap-4 text-xs font-bold uppercase tracking-widest opacity-80">
                                  <span v-if="settings.showDate !== false">{{ post.date }}</span>
                                  <span v-if="settings.showAuthor !== false" class="flex items-center gap-2">
                                      <span class="w-1 h-1 rounded-full bg-white/40"></span>
                                      BY {{ post.author }}
                                  </span>
                               </div>
                          </div>
  
                          <!-- Title -->
                          <h2 
                            class="post-title text-5xl md:text-7xl font-black mb-8 leading-tight tracking-tighter max-w-5xl animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100" 
                            :style="titleStyles"
                          >
                              {{ post.title }}
                          </h2>
  
                          <!-- Excerpt -->
                          <p 
                            v-if="settings.showExcerpt !== false" 
                            class="post-excerpt text-lg md:text-2xl font-medium opacity-90 mb-12 max-w-3xl leading-relaxed line-clamp-3 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200" 
                            :style="excerptStyles"
                          >
                              {{ post.excerpt }}
                          </p>
  
                          <!-- Read More Button -->
                          <Button 
                              v-if="settings.showReadMore !== false" 
                              as="a"
                              :href="mode === 'view' ? post.url : undefined" 
                              class="h-14 px-12 rounded-full font-bold shadow-2xl hover:scale-110 active:scale-95 transition-all text-lg animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300"
                              :style="buttonDisplayStyles"
                              @click="handleLinkClick"
                          >
                              {{ settings.readMoreText || 'Read Story' }}
                              <ArrowRight class="ml-2 w-5 h-5" />
                          </Button>
                      </div>
                  </div>
              </CarouselItem>
          </CarouselContent>
  
          <!-- Controls -->
          <template v-if="settings.showArrows !== false">
              <CarouselPrevious class="left-10 opacity-0 group-hover/main:opacity-100 transition-all duration-300 bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 hover:scale-110 h-14 w-14 z-30" />
              <CarouselNext class="right-10 opacity-0 group-hover/main:opacity-100 transition-all duration-300 bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 hover:scale-110 h-14 w-14 z-30" />
          </template>
      </Carousel>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import { Badge, Button } from '../ui'
import Autoplay from 'embla-carousel-autoplay'
import { ArrowRight } from 'lucide-vue-next'
import { 
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

const mockPosts = [
  { title: 'The Future of Immersive Digital Experiences', category: 'Technology', excerpt: 'How spatial computing and modern web technologies are redefining the way we interact with content online.', date: 'JAN 24, 2026', author: 'ANTIGRAVITY', image: 'https://picsum.photos/1920/1080?random=1', url: '#' },
  { title: 'Mastering Component Architecture in Vue 3', category: 'Design', excerpt: 'An in-depth guide to building scalable, high-performance design systems using Composition API and Radix.', date: 'JAN 22, 2026', author: 'DESIGN TEAM', image: 'https://picsum.photos/1920/1080?random=2', url: '#' },
  { title: 'Designing for Unmatched Speed and Performance', category: 'Marketing', excerpt: 'Why millisecond matters and how to optimize your global assets for instantaneous page loads.', date: 'JAN 20, 2026', author: 'ENGINEERING', image: 'https://picsum.photos/1920/1080?random=3', url: '#' }
]

const displayPosts = computed(() => mockPosts)

const carouselOptions = computed(() => ({
    align: 'start',
    loop: settings.value.loop !== false,
}))

const carouselPlugins = computed(() => {
    const plugins: any[] = []
    if (settings.value.autoplay !== false && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: settings.value.autoplaySpeed || 5000,
            stopOnInteraction: false
        }))
    }
    return plugins
})

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerHeight = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', props.device) || 700
  return `${height}px`
})

const overlayStyles = computed(() => {
    const color = settings.value.overlayColor || 'rgba(0,0,0,0.5)'
    return { 
        background: settings.value.overlayGradient !== false 
            ? `linear-gradient(to top, ${color} 0%, rgba(0,0,0,0.2) 100%)` 
            : color
    }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))

const buttonDisplayStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#ffffff',
        color: settings.value.buttonTextColor || '#0f172a',
    }
})
</script>

<style scoped>
.fullwidth-post-slider-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-post-slider-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.post-title {
    text-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
</style>
