<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="post-slider-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Post Slider'"
    :style="cardStyles"
  >
    <div class="slider-container w-full" :style="containerStyles">
      <h2 
        v-if="settings.title || mode === 'edit'" 
        class="slider-main-heading text-center mb-16 outline-none tracking-tighter" 
        :style="mainTitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="(e: any) => updateField('title', (e.target as HTMLElement).innerText)"
      >
        {{ settings.title || (mode === 'edit' ? 'Curated Experiences' : '') }}
      </h2>

      <div class="carousel-wrapper relative group/main">
          <Carousel 
              class="w-full" 
              :opts="carouselOptions"
              :plugins="carouselPlugins"
          >
              <CarouselContent class="-ml-0">
                  <CarouselItem 
                      v-for="(post, index) in displayPosts" 
                      :key="index" 
                      class="pl-0 basis-full"
                  >
                      <div class="relative overflow-hidden rounded-[4rem] shadow-2xl transition-all duration-1000 group/item" :style="slideStyles">
                          <!-- Image Area -->
                          <div v-if="post.image" class="absolute inset-0 z-0 transition-transform duration-[2000ms] group-hover/item:scale-110">
                               <img :src="post.image" class="w-full h-full object-cover" />
                               <div v-if="overlayEnabled" class="absolute inset-0 z-10" :style="overlayStyles" />
                          </div>
                          <div v-else class="absolute inset-0 bg-slate-900 z-0" />
                          
                          <!-- Content Area -->
                          <div class="relative z-20 h-full flex flex-col justify-center items-center text-center p-12 md:p-32 text-white">
                              <Badge 
                                  v-if="settings.showMeta !== false" 
                                  class="mb-8 bg-white/10 backdrop-blur-2xl border-white/20 text-white rounded-2xl px-8 py-2 font-black uppercase tracking-[0.3em] text-[10px] transform -translate-y-8 opacity-0 group-hover/item:translate-y-0 group-hover/item:opacity-100 transition-all duration-700"
                                  :style="metaStyles"
                              >
                                  {{ post.date }} • {{ post.readTime }}
                              </Badge>
                              
                              <h2 class="text-4xl md:text-7xl font-black mb-10 leading-[0.9] tracking-tighter max-w-5xl transition-all duration-700 delay-100 transform -translate-y-4 group-hover/item:translate-y-0" :style="itemTitleStyles">
                                  {{ post.title }}
                              </h2>
                              
                              <p v-if="settings.showExcerpt !== false" class="text-lg md:text-2xl font-medium opacity-80 mb-12 max-w-2xl leading-relaxed transition-all duration-700 delay-200 transform translate-y-4 group-hover/item:translate-y-0" :style="excerptStyles">
                                  {{ post.excerpt }}
                              </p>
                              
                              <Button 
                                  v-if="settings.showButton !== false" 
                                  as="a"
                                  :href="mode === 'view' ? post.url : '#'" 
                                  class="h-16 px-16 rounded-[2rem] font-black uppercase tracking-widest text-xs shadow-2xl hover:scale-105 active:scale-95 transition-all duration-500 delay-300 transform translate-y-8 opacity-0 group-hover/item:translate-y-0 group-hover/item:opacity-100" 
                                  :style="buttonDisplayStyles"
                                  @click="(e: any) => mode === 'edit' ? e.preventDefault() : null"
                              >
                                  {{ settings.buttonText || 'Explore Case Study' }}
                                  <LucideIcon name="ArrowRight" class="ml-3 w-5 h-5" />
                              </Button>
                          </div>
                      </div>
                  </CarouselItem>
              </CarouselContent>

              <!-- Controls -->
              <template v-if="settings.showArrows !== false">
                  <CarouselPrevious class="left-8 w-16 h-16 opacity-0 group-hover/main:opacity-100 transition-all duration-500 bg-white/5 backdrop-blur-2xl border-white/10 text-white hover:bg-white/20 hover:scale-110" />
                  <CarouselNext class="right-8 w-16 h-16 opacity-0 group-hover/main:opacity-100 transition-all duration-500 bg-white/5 backdrop-blur-2xl border-white/10 text-white hover:bg-white/20 hover:scale-110" />
              </template>
          </Carousel>
      </div>
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
import LucideIcon from '../../components/ui/LucideIcon.vue'
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

const overlayEnabled = computed(() => settings.value.overlayEnabled !== false)

// Mock dynamic data
const injectedPosts = inject<any[]>('injectedPosts', [
    { title: 'The Future of Agency Design: Dynamic Interfaces', excerpt: 'Deep dive into specialized layout controls, responsive typography, and unmatched interactive aesthetics.', date: 'JAN 25, 2026', readTime: '5 MIN READ', image: 'https://picsum.photos/1600/900?random=31', url: '#' },
    { title: 'Crafting Digital Excellence with Antigravity', excerpt: 'Explore the latest techniques in modern web design, focus on accessibility and performance.', date: 'JAN 22, 2026', readTime: '8 MIN READ', image: 'https://picsum.photos/1600/900?random=32', url: '#' },
    { title: 'Interactive Storytelling in CMS Ecosystems', excerpt: 'Learn the secrets to high-converting hero sections and seamless user journeys.', date: 'JAN 20, 2026', readTime: '12 MIN READ', image: 'https://picsum.photos/1600/900?random=33', url: '#' }
])

const displayPosts = computed(() => {
    const count = getVal(settings.value, 'totalPosts', props.device) || 5
    return injectedPosts.slice(0, count)
})

const carouselOptions = computed(() => ({
    align: 'start',
    loop: settings.value.loop !== false,
}))

const carouselPlugins = computed(() => {
    const plugins = []
    if (settings.value.autoplay !== false && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: settings.value.autoplaySpeed || 5000,
            stopOnInteraction: false
        }))
    }
    return plugins
})

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const slideStyles = computed(() => {
    const h = getVal(settings.value, 'height', props.device) || 600
    return {
        height: typeof h === 'number' ? `${h}px` : h,
        background: 'linear-gradient(135deg, #020617 0%, #0f172a 100%)'
    }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: getVal(settings.value, 'overlayColor', props.device) || 'rgba(0,0,0,0.5)',
}))

const mainTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'item_title_', props.device))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))

const buttonDisplayStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    return {
        ...styles,
        backgroundColor: getVal(settings.value, 'buttonBackgroundColor', props.device) || '#ffffff',
        color: '#0f172a',
    }
})

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.post-slider-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.post-slider-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
