<template>
  <BaseBlock 
    :module="module" 
    :settings="settings" 
    :mode="mode"
    class="group-carousel-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Group Carousel'"
  >
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
                <template v-if="!module.children?.length">
                  <CarouselItem v-for="i in 3" :key="i" class="pl-4" :class="slideBasisClass">
                      <div class="slide-placeholder h-[300px] flex flex-col items-center justify-center p-12 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[2rem] text-slate-400 gap-4 transition-colors hover:border-primary/50 group-hover:bg-slate-50/50 dark:group-hover:bg-slate-900/50">
                          <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center shadow-sm">
                              <Layers class="w-8 h-8 opacity-40 text-primary" />
                          </div>
                          <div class="text-center">
                              <span class="font-black text-slate-600 dark:text-slate-400 block tracking-tight">Slide {{ i }}</span>
                              <small class="opacity-60 font-medium tracking-wide">Drop components here</small>
                          </div>
                      </div>
                  </CarouselItem>
                </template>
             </template>

             <!-- Renderer Mode -->
             <template v-else>
                <CarouselItem 
                  v-for="child in (module.children || [])" 
                  :key="child.id" 
                  class="pl-4"
                  :class="slideBasisClass"
                >
                    <BlockRenderer
                      v-if="BlockRenderer"
                      :block="child"
                      :mode="mode"
                    />
                </CarouselItem>
             </template>
          </CarouselContent>

          <!-- Navigation -->
          <template v-if="showControls">
              <CarouselPrevious v-if="settings.showArrows !== false" class="left-2 opacity-0 group-hover:opacity-100 transition-[width] duration-500 hover:scale-110 active:scale-95 shadow-xl border-none" :style="arrowStyles" />
              <CarouselNext v-if="settings.showArrows !== false" class="right-2 opacity-0 group-hover:opacity-100 transition-[width] duration-500 hover:scale-110 active:scale-95 shadow-xl border-none" :style="arrowStyles" />
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
import Autoplay from 'embla-carousel-autoplay'
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';import { 
  getVal,
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const BlockRenderer = inject<any>('BlockRenderer', null)

const slidesPerView = computed(() => {
  const val = getVal(settings.value, 'slidesPerView', device.value)
  return parseInt(val) || 1
})

const slideBasisClass = computed(() => {
    const spv = slidesPerView.value
    if (device.value === 'mobile') return 'basis-full'
    if (device.value === 'tablet') return spv > 1 ? 'basis-1/2' : 'basis-full'
    
    if (spv === 1) return 'basis-full'
    if (spv === 2) return 'basis-1/2'
    if (spv === 3) return 'basis-1/3'
    if (spv === 4) return 'basis-1/4'
    if (spv === 5) return 'basis-1/5'
    if (spv === 6) return 'basis-1/6'
    return 'basis-full'
})

const totalSlides = computed(() => props.module.children?.length || 0)
const showControls = computed(() => totalSlides.value > slidesPerView.value)

const carouselOptions = computed(() => ({
    align: 'start' as const,
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

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const arrowStyles = computed(() => ({
  backgroundColor: settings.value.arrowBackgroundColor || 'var(--primary)',
  color: settings.value.arrowColor || '#ffffff'
}))
</script>

<style scoped>
.group-carousel-block { width: 100%; }
</style>

