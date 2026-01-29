<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="video-slider-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Premium Video Slider'"
    :style="cardStyles"
  >
    <div class="carousel-container relative w-full" :style="containerStyles">
        <Carousel 
            class="w-full" 
            :opts="carouselOptions"
            :plugins="carouselPlugins"
        >
            <CarouselContent class="-ml-6">
                <CarouselItem 
                    v-for="(video, index) in items" 
                    :key="index"
                    class="pl-6 pb-6"
                    :class="slideBasisClass"
                >
                    <Card 
                        class="video-card group bg-white dark:bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl border border-slate-50 dark:border-slate-800 transition-colors duration-700 hover:-translate-y-3 hover:shadow-primary/20 cursor-pointer h-full flex flex-col"
                        @click="handlePlay(video)"
                    >
                        <div class="video-thumbnail relative overflow-hidden aspect-video bg-slate-900">
                             <img v-if="getThumb(video)" :src="getThumb(video)" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                             <div v-else class="thumbnail-placeholder absolute inset-0 flex items-center justify-center text-slate-700 bg-slate-950">
                                <Film class="w-16 h-16 opacity-20 animate-pulse" />
                             </div>
                             
                             <!-- Play Icon Overlay -->
                             <div class="absolute inset-0 z-10 flex items-center justify-center bg-primary/20 opacity-0 group-hover:opacity-100 transition-colors duration-700 backdrop-blur-sm">
                                <div class="w-24 h-24 rounded-full bg-white/10 backdrop-blur-xl border border-white/20 text-white flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform duration-700 shadow-2xl group/btn">
                                    <Play class="w-10 h-10 fill-current translate-x-1 transition-transform duration-500 group-hover/btn:scale-110" />
                                </div>
                             </div>
                             
                             <div v-if="video.type" class="absolute top-6 left-6 z-20">
                                <Badge class="rounded-full bg-black/40 backdrop-blur-md border border-white/10 text-white font-black uppercase tracking-widest text-[10px] px-5 py-2">
                                    {{ video.type }}
                                </Badge>
                             </div>
                        </div>
                        
                        <CardContent class="p-10 flex flex-col flex-grow bg-gradient-to-b from-transparent to-slate-50/30 dark:to-slate-950/30">
                             <CardTitle class="text-2xl font-black mb-4 line-clamp-2 leading-tight tracking-tighter group-hover:text-primary transition-colors border-none" :style="titleStyles">
                                {{ video.title }}
                             </CardTitle>
                             <CardDescription v-if="video.description" class="text-sm font-medium text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                {{ video.description }}
                             </CardDescription>
                             
                             <div class="mt-auto pt-8 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-[width] duration-500 translate-y-4 group-hover:translate-y-0">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Watch Journey</span>
                                <div class="h-px flex-1 bg-primary/20"></div>
                             </div>
                        </CardContent>
                    </Card>
                </CarouselItem>
            </CarouselContent>

            <!-- Navigation -->
            <template v-if="items.length > slidesPerView">
                <div class="carousel-nav-wrapper absolute -inset-x-12 top-1/2 -translate-y-1/2 flex justify-between pointer-events-none z-30">
                    <CarouselPrevious v-if="showArrows" class="pointer-events-auto w-14 h-14 rounded-full bg-white dark:bg-slate-900 shadow-2xl border-slate-100 dark:border-slate-800 transition-[width] duration-500 opacity-0 group-hover:opacity-100 -translate-x-8 group-hover:translate-x-0 hover:bg-primary hover:text-white" />
                    <CarouselNext v-if="showArrows" class="pointer-events-auto w-14 h-14 rounded-full bg-white dark:bg-slate-900 shadow-2xl border-slate-100 dark:border-slate-800 transition-[width] duration-500 opacity-0 group-hover:opacity-100 translate-x-8 group-hover:translate-x-0 hover:bg-primary hover:text-white" />
                </div>
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
import { Card, CardContent, CardTitle, CardDescription, Badge } from '../ui'
import Autoplay from 'embla-carousel-autoplay'
import Film from 'lucide-vue-next/dist/esm/icons/film.js';
import Play from 'lucide-vue-next/dist/esm/icons/play.js';import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance, BlockProps } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<any>('builder', null)
const settings = computed(() => (props.settings || props.module.settings || {}) as Record<string, any>)

const items = computed(() => settings.value.items || [
    { title: 'Designing the Future of CMS', description: 'Take a deep dive into modern component architecture and user experience design.', type: 'YouTube', videoId: 'aqz-KE-bpKQ' },
    { title: 'Unmatched Performance with Vue 3', description: 'Exploring how script setup and reactivity transform web development workflows.', type: 'Vimeo', videoId: '446698547' },
    { title: 'Mastering Tailwind Layouts', description: 'Learn the secrets to building highly responsive, premium interfaces in record time.', type: 'YouTube', videoId: 'aqz-KE-bpKQ' }
])

const slidesPerView = computed(() => parseInt(getResponsiveValue(settings.value, 'slidesPerView', props.device)) || 1)
const showArrows = computed(() => getResponsiveValue(settings.value, 'showArrows', props.device) !== false)

const slideBasisClass = computed(() => {
    const spv = slidesPerView.value
    if (spv === 1) return 'basis-full'
    if (spv === 2) return 'basis-1/2'
    if (spv === 3) return 'basis-1/3'
    if (spv === 4) return 'basis-1/4'
    return 'basis-full'
})

const carouselOptions = computed(() => ({
    align: 'start' as const,
    loop: settings.value.loop !== false,
    slidesToScroll: slidesPerView.value
}))

const carouselPlugins = computed(() => {
    const plugins = []
    if (settings.value.autoplay && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: parseInt(settings.value.autoplaySpeed) || 5000,
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

const handlePlay = (video: any) => {
    if (props.mode === 'edit') return
    console.log('Playing video:', video)
}

const getThumb = (video: any) => {
    if (video.thumbnail) return video.thumbnail
    if (video.videoId && video.type === 'youtube') {
        return `https://img.youtube.com/vi/${video.videoId}/hqdefault.jpg`
    }
    return null
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
</script>

<style scoped>
.video-slider-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.video-slider-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
