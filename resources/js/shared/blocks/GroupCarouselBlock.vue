<template>
  <BaseBlock :module="module" :settings="settings" class="group-carousel-block">
    <div class="carousel-container relative group" :style="containerStyles">
        <Carousel 
          class="w-full" 
          :opts="carouselOptions"
          :plugins="carouselPlugins"
        >
          <CarouselContent class="-ml-4">
             <!-- Builder Mode -->
             <template v-if="mode === 'edit'">
                <slot />
                <!-- Fallback placeholders if no children -->
                <CarouselItem v-if="!module.children?.length" v-for="i in 3" :key="i" class="pl-4" :class="slideBasisClass">
                    <div class="slide-placeholder h-[250px] flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400 gap-3">
                        <Layers class="w-8 h-8 opacity-30" />
                        <span class="font-bold">Slide {{ i }}</span>
                    </div>
                </CarouselItem>
             </template>

             <!-- Renderer Mode -->
             <template v-else>
                <CarouselItem 
                  v-for="child in nestedBlocks" 
                  :key="child.id" 
                  class="pl-4"
                  :class="slideBasisClass"
                >
                    <BlockRenderer
                      :block="child"
                      :mode="mode"
                    />
                </CarouselItem>
             </template>
          </CarouselContent>

          <!-- Navigation -->
          <template v-if="showControls">
              <CarouselPrevious v-if="settings.showArrows !== false" class="left-0 -translate-x-4 opacity-0 group-hover:opacity-100 transition-opacity" :style="arrowStyles" />
              <CarouselNext v-if="settings.showArrows !== false" class="right-0 translate-x-4 opacity-0 group-hover:opacity-100 transition-opacity" :style="arrowStyles" />
              
              <!-- Dots (Custom implementation as pagination is not built-in to simple Carousel primitive yet) -->
              <!-- Embla doesn't easily expose dots in the simple props, but we can stick to native if needed or keep existing logic -->
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
import Autoplay from 'embla-carousel-autoplay'
import { Layers } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

const BlockRenderer = inject('BlockRenderer', null)

const slidesPerView = computed(() => {
  const val = getResponsiveValue(settings.value, 'slidesPerView', device.value)
  return parseInt(val) || 1
})

const slideBasisClass = computed(() => {
    const spv = slidesPerView.value
    if (spv === 1) return 'basis-full'
    if (spv === 2) return 'basis-1/2'
    if (spv === 3) return 'basis-1/3'
    if (spv === 4) return 'basis-1/4'
    return 'basis-full'
})

const totalSlides = computed(() => props.mode === 'edit' ? (props.module.children?.length || 0) : props.nestedBlocks.length)
const showControls = computed(() => totalSlides.value > slidesPerView.value)

const carouselOptions = computed(() => ({
    align: 'start',
    loop: settings.value.loop !== false,
    dragFree: settings.value.dragFree || false,
    slidesToScroll: slidesPerView.value
}))

const carouselPlugins = computed(() => {
    const plugins = []
    if (settings.value.autoplay && props.mode === 'view') {
        plugins.push(Autoplay({
            delay: parseInt(settings.value.autoplaySpeed) || 5000,
            stopOnInteraction: false,
            stopOnMouseEnter: true
        }))
    }
    return plugins
})

const containerStyles = computed(() => ({}))

const arrowStyles = computed(() => ({
  backgroundColor: settings.value.arrowBackgroundColor,
  color: settings.value.arrowColor
}))
</script>

<style scoped>
.group-carousel-block { width: 100%; }
</style>
