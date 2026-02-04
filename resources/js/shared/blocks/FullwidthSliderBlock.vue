<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-slider-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Fullwidth Slider'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div class="slider-container w-full" :style="containerStyles">
        <Carousel 
            class="w-full h-full" 
            :opts="carouselOptions"
            :plugins="carouselPlugins"
        >
            <CarouselContent class="-ml-0 h-full">
                <!-- Builder Mode -->
                <template v-if="mode === 'edit'">
                    <slot />
                </template>
                
                <!-- Renderer Mode -->
                <template v-else>
                    <CarouselItem 
                        v-for="(child, index) in module.children" 
                        :key="child.id"
                        class="pl-0 basis-full h-full"
                    >
                        <component 
                            :is="getChildComponent(child.type)" 
                            :module="child" 
                            :index="index"
                        />
                    </CarouselItem>
                </template>
            </CarouselContent>

            <!-- Navigation Arrows -->
            <template v-if="blockSettings.showArrows !== false">
                <CarouselPrevious class="left-8 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-12 w-12" />
                <CarouselNext class="right-8 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-12 w-12" />
            </template>
        </Carousel>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject, provide, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import Autoplay from 'embla-carousel-autoplay'
import type { EmblaPluginType } from 'embla-carousel'
import { 
    getVal, 
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

// Fallback currentSlide for provided state (Embla handles this internally)
const currentSlide = computed(() => 0) 

// Provide state to FullwidthSlideItemBlock
provide('fullwidthSliderState', {
    parentSettings: settings,
    currentSlide,
    mode: computed(() => props.mode)
})

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
        position: 'relative', 
        overflow: 'hidden', 
        width: '100%',
        height: `${getVal<number>(settings.value, 'height', props.device) || 600}px`
    } as CSSProperties
})

const getChildComponent = (type: string) => {
  if (builder?.getComponent) return builder.getComponent(type)
  return 'FullwidthSlideItemBlock' 
}
</script>

<style scoped>
.fullwidth-slider-block { width: 100%; position: relative; }
.fullwidth-slider-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.fullwidth-slider-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
