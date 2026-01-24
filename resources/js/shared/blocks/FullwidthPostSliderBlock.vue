<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-post-slider-block" :style="containerStyles">
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
                    <div class="absolute inset-0 z-0 bg-slate-900 transition-transform duration-[2000ms] group-hover:scale-110">
                        <img v-if="post.image" :src="post.image" class="w-full h-full object-cover" />
                    </div>
                    <div class="absolute inset-0 z-10" :style="overlayStyles" />
                    
                    <div class="relative z-20 h-full w-full flex flex-col items-center justify-center text-center px-8 md:px-24 text-white">
                        <div v-if="settings.showMeta !== false" class="post-meta mb-8 flex flex-wrap justify-center gap-4" :style="metaStyles">
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

                        <h2 class="post-title text-5xl md:text-7xl font-black mb-8 leading-none tracking-tighter max-w-5xl" :style="titleStyles">
                            {{ post.title }}
                        </h2>

                        <p v-if="settings.showExcerpt !== false" class="post-excerpt text-lg md:text-2xl font-medium opacity-90 mb-12 max-w-3xl leading-relaxed line-clamp-3" :style="excerptStyles">
                            {{ post.excerpt }}
                        </p>

                        <Button 
                            v-if="settings.showReadMore !== false" 
                            as="a"
                            :href="mode === 'view' ? post.url : null" 
                            class="h-14 px-12 rounded-full font-bold shadow-2xl hover:scale-110 active:scale-95 transition-all text-lg"
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
            <CarouselPrevious class="left-10 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-14 w-14" />
            <CarouselNext class="right-10 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-14 w-14" />
        </template>
    </Carousel>
  </BaseBlock>
</template>

<script setup>
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
    const plugins = []
    if (settings.value.autoplay !== false && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: settings.value.autoplaySpeed || 5000,
            stopOnInteraction: false
        }))
    }
    return plugins
})

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
    // Logic to navigate if mode === view handles by 'as="a"'
}

const containerStyles = computed(() => {
  return { 
    position: 'relative', 
    overflow: 'hidden', 
    width: '100%',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 700}px`
  }
})

const overlayStyles = computed(() => ({ 
  background: settings.value.overlayGradient !== false 
    ? `linear-gradient(to top, ${settings.value.overlayColor || 'rgba(0,0,0,0.8)'} 0%, rgba(0,0,0,0.2) 100%)` 
    : settings.value.overlayColor || 'rgba(0,0,0,0.5)',
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))

const buttonDisplayStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#ffffff',
        color: settings.value.buttonTextColor || '#0f172a',
    }
})
</script>

<style scoped>
.fullwidth-post-slider-block { width: 100%; position: relative; }
</style>
