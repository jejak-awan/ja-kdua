<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div 
        class="slider-block relative group" 
        :style="sliderBlockStyles"
        @mouseenter="plugin?.stop"
        @mouseleave="plugin?.play"
      >
        <Carousel 
          class="w-full h-full" 
          :opts="{ loop: true }"
          :plugins="[plugin]"
        >
          <CarouselContent :style="containerStyles">
            <CarouselItem 
              v-for="(slide, index) in items" 
              :key="index"
              class="relative w-full h-full"
            >
               <!-- Slide Container (Absolute to fill height) -->
               <div class="relative w-full h-full flex flex-col justify-center overflow-hidden">
                  <!-- Background Image Zoom Effect -->
                  <div 
                    v-if="slide.image"
                    class="absolute inset-0 z-[-1] transition-transform duration-[10000ms] ease-linear hover:scale-110"
                    :style="{ backgroundImage: `url(${slide.image})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
                  ></div>
                  <!-- Overlay -->
                  <div 
                    v-if="getVal(settings, 'overlayEnabled') !== false" 
                    class="absolute inset-0 z-0 pointer-events-none" 
                    :style="{ backgroundColor: getVal(settings, 'overlay_color') || 'rgba(0,0,0,0.4)' }"
                  ></div>
                  
                  <!-- Content -->
                  <div 
                    class="slider-content relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl flex flex-col" 
                    :class="getContentAlignmentClass(slide)"
                  >
                    <h2 
                        class="slider-title font-bold mb-4" 
                        :style="titleStyles"
                    >
                        {{ slide.title || 'Slide Title' }}
                    </h2>
                    
                    <div 
                        class="slider-text prose prose-lg prose-invert mb-8" 
                        :style="contentStyles" 
                        v-html="slide.subtitle"
                    ></div>
                    
                    <div 
                        v-if="slide.button_text"
                    >
                         <a 
                            :href="slide.button_url || '#'" 
                            class="inline-flex items-center justify-center transition-transform hover:-translate-y-1 active:translate-y-0"
                            :style="buttonStyles" 
                            @click="mode === 'edit' ? e => e.preventDefault() : undefined"
                        >
                            {{ slide.button_text }}
                        </a>
                    </div>
                  </div>
               </div>
            </CarouselItem>
          </CarouselContent>
          
          <CarouselPrevious v-if="(getVal(settings, 'show_arrows') !== false) && items.length > 1" class="left-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          <CarouselNext v-if="(getVal(settings, 'show_arrows') !== false) && items.length > 1" class="right-4 opacity-0 group-hover:opacity-100 transition-opacity" />
        </Carousel>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import Autoplay from 'embla-carousel-autoplay'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])

const autoplayEnabled = computed(() => getVal(settings.value, 'autoplay', props.device) !== false)
const autoplaySpeed = computed(() => parseInt(getVal(settings.value, 'autoplaySpeed', props.device)) || 5000)

const plugin = Autoplay({
  delay: autoplaySpeed.value,
  stopOnInteraction: false,
  stopOnMouseEnter: true,
})

// Since plugin creation is static, we might need a key to force re-render if autoplay settings change substantially,
// or we can just rely on the fact that these are usually set once. For builder live preview, a watcher might be needed.
// However, Autoplay instance config is not reactive by default. Simple fix: recreation on component mount/update isn't ideal for carousel.
// For now, simple implementation.

const sliderBlockStyles = computed(() => ({ width: '100%' }))

const containerStyles = computed(() => {
  const height = getVal(settings.value, 'height', props.device) || 'h-[500px]'
  if (height.includes('h-screen')) return { height: '100vh' }
  const match = height.match(/\d+/)
  if (match) return { height: `${match[0]}px` }
  return { height: '500px' } 
})

const getContentAlignmentClass = (slide) => {
    const align = slide.alignment || 'center'
    if (align === 'left') return 'items-start text-left'
    if (align === 'right') return 'items-end text-right'
    return 'items-center text-center'
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    return {
        ...styles,
        padding: '12px 32px',
        backgroundColor: styles.color || '#ffffff',
        color: styles.color ? '#ffffff' : '#000000', 
        borderRadius: '99px',
        textDecoration: 'none'
    }
})
</script>

<style scoped>
/* Ensure Carousel item takes full height */
:deep(.embla__container) {
  height: 100%;
}
</style>
