<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-post-slider-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Post Slider'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="slider-container relative w-full overflow-hidden" 
        :style="containerStyles"
      >
        <Carousel 
            class="w-full h-full" 
            :opts="carouselOptions"
            :plugins="carouselPlugins"
        >
            <CarouselContent class="-ml-0 h-full">
                <CarouselItem 
                    v-for="(post, index) in injectedRelatedPosts" 
                    :key="index"
                    class="pl-0 basis-full h-full"
                >
                    <div class="relative w-full h-full flex flex-col justify-center overflow-hidden">
                        <!-- Post Background -->
                        <div 
                            v-if="post.image"
                            class="absolute inset-0 z-0 transition-transform duration-[10000ms] ease-linear group-hover:scale-110"
                            :style="{ backgroundImage: `url(${post.image})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
                        ></div>

                        <!-- Overlay -->
                        <div class="absolute inset-0 z-10 bg-black/40 pointer-events-none"></div>

                        <!-- Content -->
                        <div class="relative z-20 container mx-auto px-6 max-w-7xl">
                            <div class="max-w-3xl">
                                <Badge v-if="post.category" variant="secondary" class="mb-6 bg-white/20 backdrop-blur-md text-white border-white/30 uppercase tracking-widest px-4 py-1.5 font-bold">
                                    {{ post.category }}
                                </Badge>
                                
                                <h2 class="text-4xl md:text-6xl font-black text-white mb-6 leading-[1.1] tracking-tighter" :style="titleStyles">
                                    {{ post.title }}
                                </h2>

                                <p class="text-xl text-white/80 mb-10 line-clamp-2 max-w-2xl leading-relaxed" :style="contentStyles">
                                    {{ post.excerpt || post.content }}
                                </p>

                                <a 
                                    :href="post.url || '#'" 
                                    class="inline-flex items-center gap-2 bg-white text-black px-8 py-4 rounded-full font-black hover:bg-primary hover:text-white transition-all transform hover:-translate-y-1 active:translate-y-0 shadow-2xl"
                                    @click="mode === 'edit' ? $event.preventDefault() : null"
                                >
                                    Read Story
                                    <ArrowRight class="w-5 h-5" />
                                </a>
                            </div>
                        </div>
                    </div>
                </CarouselItem>
            </CarouselContent>

            <!-- Navigation Controls -->
            <template v-if="blockSettings.showArrows !== false">
                 <CarouselPrevious class="left-8 opacity-0 group-hover:opacity-100 transition-all bg-white/10 backdrop-blur-xl border-white/20 text-white hover:bg-white hover:text-black h-14 w-14" />
                <CarouselNext class="right-8 opacity-0 group-hover:opacity-100 transition-all bg-white/10 backdrop-blur-xl border-white/20 text-white hover:bg-white hover:text-black h-14 w-14" />
            </template>
        </Carousel>

        <!-- Progress Indicator -->
        <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-30 flex gap-3">
            <button 
                v-for="(_, i) in injectedRelatedPosts" 
                :key="i"
                class="h-1.5 rounded-full transition-all duration-500"
                :class="i === currentSlide ? 'w-12 bg-white' : 'w-4 bg-white/30 hover:bg-white/50'"
            />
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import { Badge } from '../ui'
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import Autoplay from 'embla-carousel-autoplay'
import type { EmblaPluginType } from 'embla-carousel'
import { 
    getVal, 
    getLayoutStyles,
    getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)
const injectedRelatedPosts = inject<Record<string, string>[]>('relatedPosts', [])

// Fallback currentSlide
const currentSlide = computed(() => 0) 

const carouselOptions = computed(() => ({
    align: 'start' as const,
    loop: settings.value.loop !== false,
}))

const carouselPlugins = computed(() => {
    const plugins: EmblaPluginType[] = []
    if (settings.value.autoplay !== false && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: (settings.value.autoplaySpeed as number) || 5000,
            stopOnInteraction: false
        }) as EmblaPluginType)
    }
    return plugins
})

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        height: `${getVal<number>(settings.value, 'height', props.device) || 600}px`
    } as CSSProperties
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device) as CSSProperties)
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device) as CSSProperties)
</script>

<style scoped>
.fullwidth-post-slider-block { width: 100%; position: relative; }
</style>
