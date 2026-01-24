<template>
  <BaseBlock :module="module" :settings="settings" class="video-slider-block">
    <div class="carousel-container relative">
        <Carousel 
            class="w-full" 
            :opts="carouselOptions"
            :plugins="carouselPlugins"
        >
            <CarouselContent class="-ml-4">
                <CarouselItem 
                    v-for="(video, index) in items" 
                    :key="index"
                    class="pl-4 pb-4"
                    :class="slideBasisClass"
                >
                    <Card 
                        class="video-card group bg-white dark:bg-slate-900 rounded-[32px] overflow-hidden shadow-lg border-none hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 cursor-pointer h-full flex flex-col"
                        @click="handlePlay(video)"
                    >
                        <div class="video-thumbnail relative overflow-hidden aspect-video bg-slate-900">
                             <img v-if="getThumb(video)" :src="getThumb(video)" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                             <div v-else class="thumbnail-placeholder absolute inset-0 flex items-center justify-center text-slate-700">
                                <Film class="w-12 h-12 opacity-30" />
                             </div>
                             
                             <!-- Play Icon Overlay -->
                             <div class="absolute inset-0 z-10 flex items-center justify-center bg-primary/20 opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-sm">
                                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-500">
                                    <Play class="w-10 h-10 fill-current translate-x-0.5" />
                                </div>
                             </div>
                             
                             <Badge v-if="video.type" class="absolute top-4 left-4 z-20 rounded-full bg-black/50 backdrop-blur-md border-white/20 text-white font-bold px-4 py-1">
                                {{ video.type }}
                             </Badge>
                        </div>
                        
                        <CardContent class="p-8 flex flex-col flex-grow">
                             <CardTitle class="text-xl font-black mb-3 line-clamp-2 leading-tight group-hover:text-primary transition-colors border-none" :style="titleStyles">
                                {{ video.title }}
                             </CardTitle>
                             <CardDescription v-if="video.description" class="text-sm font-medium text-slate-500 line-clamp-2 leading-relaxed">
                                {{ video.description }}
                             </CardDescription>
                        </CardContent>
                    </Card>
                </CarouselItem>
            </CarouselContent>

            <!-- Navigation -->
            <template v-if="items.length > slidesPerView">
                <CarouselPrevious v-if="showArrows" class="left-0 -translate-x-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                <CarouselNext v-if="showArrows" class="right-0 translate-x-4 opacity-0 group-hover:opacity-100 transition-opacity" />
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
import { Card, CardContent, CardTitle, CardDescription, Badge } from '../ui'
import Autoplay from 'embla-carousel-autoplay'
import { Film, Play } from 'lucide-vue-next'
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

const items = computed(() => settings.value.items || [
    { title: 'Designing the Future of CMS', description: 'Take a deep dive into modern component architecture and user experience design.', type: 'YouTube', videoId: 'dQw4w9WgXcQ' },
    { title: 'Unmatched Performance with Vue 3', description: 'Exploring how script setup and reactivity transform web development workflows.', type: 'Vimeo', videoId: 'dQw4w9WgXcQ' },
    { title: 'Mastering Tailwind Layouts', description: 'Learn the secrets to building highly responsive, premium interfaces in record time.', type: 'YouTube', videoId: 'dQw4w9WgXcQ' }
])

const slidesPerView = computed(() => parseInt(getResponsiveValue(settings.value, 'slidesPerView', device.value)) || 1)
const showArrows = computed(() => getResponsiveValue(settings.value, 'showArrows', device.value) !== false)

const slideBasisClass = computed(() => {
    const spv = slidesPerView.value
    if (spv === 1) return 'basis-full'
    if (spv === 2) return 'basis-1/2'
    if (spv === 3) return 'basis-1/3'
    return 'basis-full'
})

const carouselOptions = computed(() => ({
    align: 'start',
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

const handlePlay = (video) => {
    if (props.mode === 'edit') return
    console.log('Playing video:', video)
}

const getThumb = (video) => {
    if (video.thumbnail) return video.thumbnail
    if (video.videoId) {
        return `https://img.youtube.com/vi/${video.videoId}/hqdefault.jpg`
    }
    return null
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.video-slider-block { width: 100%; position: relative; }
</style>
