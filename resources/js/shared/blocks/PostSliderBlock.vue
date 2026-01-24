<template>
  <BaseBlock :module="module" :settings="settings" class="post-slider-block">
    <h2 
      v-if="settings.title || mode === 'edit'" 
      class="slider-title-main text-3xl font-black mb-10 tracking-tighter" 
      :style="titleStyles"
      :contenteditable="mode === 'edit'"
      @blur="updateText('title', $event)"
    >
      {{ settings.title || (mode === 'edit' ? 'Featured Stories' : '') }}
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
                    <div class="relative overflow-hidden rounded-[40px] shadow-2xl" :style="containerStyles">
                        <div v-if="post.image" class="absolute inset-0 z-0 scale-105 group-hover:scale-110 transition-transform duration-1000">
                             <img :src="post.image" class="w-full h-full object-cover" />
                        </div>
                        <div v-if="overlayEnabled" class="absolute inset-0 z-10" :style="overlayStyles" />
                        
                        <div class="relative z-20 h-full flex flex-col justify-center items-center text-center p-12 md:p-24 text-white">
                            <Badge 
                                v-if="settings.showMeta !== false" 
                                class="mb-6 bg-white/20 backdrop-blur-md border-white/30 text-white rounded-full px-6 py-1.5"
                                :style="metaStyles"
                            >
                                {{ post.date }} • {{ post.readTime }}
                            </Badge>
                            
                            <h2 class="text-4xl md:text-6xl font-black mb-8 leading-none tracking-tighter max-w-4xl" :style="titleStyles">
                                {{ post.title }}
                            </h2>
                            
                            <p v-if="settings.showExcerpt !== false" class="text-lg md:text-xl font-medium opacity-90 mb-10 max-w-2xl leading-relaxed" :style="excerptStyles">
                                {{ post.excerpt }}
                            </p>
                            
                            <Button 
                                v-if="settings.showButton !== false" 
                                as="a"
                                :href="mode === 'view' ? post.url : null" 
                                class="h-14 px-12 rounded-full font-bold shadow-xl hover:scale-110 active:scale-95 transition-all text-lg" 
                                :style="buttonDisplayStyles"
                                @click="handleLinkClick"
                            >
                                {{ settings.buttonText || 'Read Story' }}
                                <ArrowRight class="ml-2 w-5 h-5" />
                            </Button>
                        </div>
                    </div>
                </CarouselItem>
            </CarouselContent>

            <!-- Controls -->
            <template v-if="settings.showArrows !== false">
                <CarouselPrevious class="left-6 opacity-0 group-hover/main:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20" />
                <CarouselNext class="right-6 opacity-0 group-hover/main:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20" />
            </template>
        </Carousel>
    </div>
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

const overlayEnabled = computed(() => settings.value.overlayEnabled !== false)

// Dynamic data injection
const injectedPosts = inject('injectedPosts', [
    { title: 'The Art of Minimalist Web Design', excerpt: 'Discover how stripping away the non-essential can create more impactful digital experiences.', date: 'Jan 10, 2026', readTime: '5 min read', image: 'https://picsum.photos/1200/600?random=1', url: '#' },
    { title: 'Future-Proofing Your Frontend Tech Stack', excerpt: 'Stay ahead of the curve with the latest trends in Vue, Radix, and Tailwind ecosystem.', date: 'Jan 12, 2026', readTime: '8 min read', image: 'https://picsum.photos/1200/600?random=2', url: '#' },
    { title: 'Building Immersive Landing Pages', excerpt: 'Learn the secrets to high-converting hero sections and interactive storytelling.', date: 'Jan 15, 2026', readTime: '12 min read', image: 'https://picsum.photos/1200/600?random=3', url: '#' }
])

const displayPosts = computed(() => {
    const count = getResponsiveValue(settings.value, 'totalPosts', device.value) || 5
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

const updateText = (key, event) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const containerStyles = computed(() => {
    const height = getResponsiveValue(settings.value, 'height', device.value) || 600
    return {
        height: typeof height === 'number' ? `${height}px` : height,
        background: 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)'
    }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.5)',
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))

const buttonDisplayStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#ffffff',
        color: settings.value.buttonTextColor || '#1e293b',
    }
})
</script>

<style scoped>
.post-slider-block { width: 100%; }
.slider-title-main { text-align: center; outline: none; }
</style>
