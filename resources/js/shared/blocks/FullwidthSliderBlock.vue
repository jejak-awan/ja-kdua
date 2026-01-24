<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-slider-block" :style="containerStyles">
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
        <template v-if="settings.showArrows !== false">
            <CarouselPrevious class="left-8 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-12 w-12" />
            <CarouselNext class="right-8 opacity-0 group-hover:opacity-100 transition-opacity bg-white/10 backdrop-blur-md border-white/20 text-white hover:bg-white/20 h-12 w-12" />
        </template>
    </Carousel>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, provide } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import Autoplay from 'embla-carousel-autoplay'
import { getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

// Fallback currentSlide for provided state (Embla handles this internally, but we might need it for children legacy compatibility)
const currentSlide = computed(() => 0) 

// Provide state to FullwidthSlideItemBlock
provide('fullwidthSliderState', {
    parentSettings: settings,
    currentSlide,
    mode: computed(() => props.mode)
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

const containerStyles = computed(() => {
  return { 
    position: 'relative', 
    overflow: 'hidden', 
    width: '100%',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 600}px`
  }
})

const getChildComponent = (type) => {
  if (builder?.getComponent) return builder.getComponent(type)
  return 'FullwidthSlideItemBlock' 
}
</script>

<style scoped>
.fullwidth-slider-block { width: 100%; position: relative; }
</style>
