<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="slider-block relative group transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Slider'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="slider-container relative" 
        :style="containerStyles"
      >
        <Carousel 
          class="w-full h-full" 
          :opts="{ loop: true }"
          :plugins="[plugin]"
        >
          <CarouselContent class="h-full">
            <CarouselItem 
              v-for="(slide, index) in items" 
              :key="index"
              class="relative w-full h-full"
            >
               <!-- Slide Container (Absolute to fill height) -->
               <div class="relative w-full h-full flex flex-col justify-center overflow-hidden">
                  <!-- Background Image Zoom Effect -->
                  <div 
                    v-if="(slide as Record<string, any>).image"
                    class="absolute inset-0 z-[-1] transition-transform duration-[10000ms] ease-linear hover:scale-110"
                    :style="{ backgroundImage: `url(${(slide as Record<string, any>).image})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
                  ></div>
                  <!-- Overlay -->
                  <div 
                    v-if="getVal<boolean>(blockSettings, 'overlayEnabled') !== false" 
                    class="absolute inset-0 z-0 pointer-events-none" 
                    :style="{ backgroundColor: (getVal<string>(blockSettings, 'overlayColor') || 'rgba(0,0,0,0.4)') }"
                  ></div>
                  
                  <!-- Content -->
                  <div 
                    class="slider-content relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl flex flex-col" 
                    :class="getContentAlignmentClass(slide as { alignment?: string })"
                  >
                    <h2 
                        class="slider-title font-bold mb-4" 
                        :style="titleStyles"
                    >
                        {{ (slide as Record<string, any>).title || 'Slide Title' }}
                    </h2>
                    
                    <div 
                        class="slider-text prose prose-lg prose-invert mb-8" 
                        :style="contentStyles" 
                        v-html="(slide as Record<string, any>).content"
                    ></div>
                    
                    <div 
                        v-if="(slide as Record<string, any>).buttonText"
                    >
                         <a 
                            :href="(slide as Record<string, any>).buttonUrl || '#'" 
                            class="inline-flex items-center justify-center transition-colors hover:-translate-y-1 active:translate-y-0"
                            :style="buttonStyles" 
                            @click="mode === 'edit' ? ($event: Event) => $event.preventDefault() : undefined"
                        >
                            {{ (slide as Record<string, any>).buttonText }}
                        </a>
                    </div>
                  </div>
               </div>
            </CarouselItem>
          </CarouselContent>
          
          <CarouselPrevious v-if="(getVal<boolean>(blockSettings, 'showArrows', device) !== false) && items.length > 1" class="left-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          <CarouselNext v-if="(getVal<boolean>(blockSettings, 'showArrows', device) !== false) && items.length > 1" class="right-4 opacity-0 group-hover:opacity-100 transition-opacity" />
        </Carousel>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Carousel from '../ui/Carousel.vue'
import CarouselContent from '../ui/CarouselContent.vue'
import CarouselItem from '../ui/CarouselItem.vue'
import CarouselNext from '../ui/CarouselNext.vue'
import CarouselPrevious from '../ui/CarouselPrevious.vue'
import Autoplay from 'embla-carousel-autoplay'
import { getVal, getLayoutStyles, getTypographyStyles } from '../utils/styleUtils'
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
const items = computed(() => (settings.value.items as unknown[]) || [])
const autoplaySpeed = computed(() => {
    const val = getVal<string | number>(settings.value, 'autoplaySpeed', props.device)
    return (typeof val === 'number' ? val : parseInt(val as string)) || 5000
})

const plugin = Autoplay({
  delay: autoplaySpeed.value,
  stopOnInteraction: false,
  stopOnMouseEnter: true,
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device) as CSSProperties)
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device) as CSSProperties)

const buttonStyles = computed((): CSSProperties => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device) as Record<string, string | number>
    return {
        ...styles,
        padding: '12px 32px',
        backgroundColor: styles.color || '#ffffff',
        color: styles.color ? '#000000' : '#000000', 
        borderRadius: '99px',
        textDecoration: 'none'
    } as CSSProperties
})

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed((): CSSProperties => {
    const layout = getLayoutStyles(settings.value, props.device)
    const heightVal = getVal<string | number>(settings.value, 'height', props.device)
    const height = heightVal || 500
    
    const styles: Record<string, string | number> = {
        ...layout,
        height: typeof height === 'number' ? `${height}px` : height
    }
    return styles
})

const getContentAlignmentClass = (slide: {alignment?: string}) => {
    const align = slide.alignment || 'center'
    if (align === 'left') return 'items-start text-left'
    if (align === 'right') return 'items-end text-right'
    return 'items-center text-center'
}
</script>

<style scoped>
.slider-block { width: 100%; }
:deep(.embla__container) {
  height: 100%;
}
</style>
