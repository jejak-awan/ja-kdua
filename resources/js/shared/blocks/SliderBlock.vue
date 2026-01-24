<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div 
        class="slider-block relative group" 
        :style="sliderBlockStyles"
        @mouseenter="pause" 
        @mouseleave="resume"
      >
        <div class="slider-container overflow-hidden relative w-full h-full" :style="containerStyles">
          <!-- Slides -->
          <div class="slider-track w-full h-full relative">
            <div 
              v-for="(slide, index) in items" 
              :key="index"
              class="slider-slide absolute inset-0 w-full h-full flex flex-col justify-center transition-all duration-1000 ease-in-out overflow-hidden"
              :class="getSlideClasses(settings, index)"
              :style="getSlideStyles(settings, slide, index)"
            >
              <!-- Background Image Zoom Effect -->
              <div 
                v-if="slide.image"
                class="absolute inset-0 z-[-1] transition-transform duration-[10000ms] ease-linear"
                :class="{ 'scale-110': currentSlide === index }"
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
                    class="slider-title font-bold mb-4 animate-fade-up opacity-0" 
                    :class="{ 'animation-trigger': currentSlide === index }"
                    :style="titleStyles"
                >
                    {{ slide.title || 'Slide Title' }}
                </h2>
                
                <div 
                    class="slider-text prose prose-lg prose-invert mb-8 animate-fade-up opacity-0 animation-delay-200" 
                    :class="{ 'animation-trigger': currentSlide === index }"
                    :style="contentStyles" 
                    v-html="slide.subtitle"
                ></div>
                
                <div 
                    v-if="slide.button_text"
                    class="animate-fade-up opacity-0 animation-delay-400"
                    :class="{ 'animation-trigger': currentSlide === index }"
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
          </div>
          
          <!-- Arrows -->
          <template v-if="(getVal(settings, 'show_arrows') !== false) && items.length > 1">
            <button 
                class="slider-arrow absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full flex items-center justify-center bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-black transition-all opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0" 
                @click="prevSlide"
            >
              <LucideIcon name="ChevronLeft" class="w-6 h-6" />
            </button>
            <button 
                class="slider-arrow absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full flex items-center justify-center bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-black transition-all opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0" 
                @click="nextSlide"
            >
              <LucideIcon name="ChevronRight" class="w-6 h-6" />
            </button>
          </template>
          
          <!-- Dots -->
          <div 
            v-if="(getVal(settings, 'show_dots') !== false) && items.length > 1" 
            class="slider-dots absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2"
          >
            <button 
              v-for="(_, index) in items" 
              :key="index"
              class="slider-dot w-2 h-2 rounded-full transition-all duration-300"
              :class="index === currentSlide ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/80'"
              @click="currentSlide = index"
            />
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])
const currentSlide = ref(0)
const autoplayInterval = ref(null)

const autoplay = computed(() => getVal(settings.value, 'autoplay', props.device) !== false)
const autoplaySpeed = computed(() => parseInt(getVal(settings.value, 'autoplaySpeed', props.device)) || 5000)

const nextSlide = () => {
  if (items.value.length <= 1) return
  currentSlide.value = (currentSlide.value + 1) % items.value.length
}

const prevSlide = () => {
  if (items.value.length <= 1) return
  currentSlide.value = currentSlide.value === 0 
    ? items.value.length - 1 
    : currentSlide.value - 1
}

const pause = () => {
  if (autoplayInterval.value) clearInterval(autoplayInterval.value)
}

const resume = () => {
  if (autoplay.value && items.value.length > 1) {
    pause()
    autoplayInterval.value = setInterval(nextSlide, autoplaySpeed.value)
  }
}

onMounted(resume)
onUnmounted(pause)

watch([autoplay, autoplaySpeed, items], resume)

const sliderBlockStyles = computed(() => ({ width: '100%' }))

const containerStyles = computed(() => {
  const height = getVal(settings.value, 'height', props.device) || 'h-[500px]'
  // If height is a class like 'h-[500px]', we rely on Tailwind class if applied, 
  // but here we are in style. We need to handle Tailwind height classes manually or via class binding.
  // Actually the definitions use 'h-[500px]' as value.
  // We can just set height style if it looks like a pixel value, or use class binding if it's a class.
  // For simplicity, let's just parse the pixel value if possible or default to style.
  if (height.includes('h-screen')) return { height: '100vh' }
  const match = height.match(/\d+/)
  if (match) return { height: `${match[0]}px` }
  return { height: '500px' } 
})

const getSlideClasses = (settings, index) => {
    const transition = getVal(settings, 'slideTransition') || 'fade'
    const isActive = currentSlide.value === index
    
    if (transition === 'fade') {
        return isActive ? 'opacity-100 z-10' : 'opacity-0 z-0'
    }
    
    // Slide transition logic (transform based)
    // We need to position them.
    // Ideally we use a transform logic in style, not class.
    return '' 
}

const getSlideStyles = (settings, slide, index) => {
    const transition = getVal(settings, 'slideTransition') || 'fade'
    const style = {
        backgroundColor: slide.image ? 'transparent' : '#1a1a1a'
    }

    if (transition === 'slide') {
        const offset = (index - currentSlide.value) * 100
        style.transform = `translateX(${offset}%)`
        style.zIndex = index === currentSlide.value ? 10 : 0
        // Hide slides that are far away to prevent glitching/performance
        if (Math.abs(index - currentSlide.value) > 1 && items.value.length > 2) {
            style.visibility = 'hidden'
        }
    }
    
    return style
}

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
        color: styles.color ? '#ffffff' : '#000000', // Invert/Contrast logic simplified
        borderRadius: '99px',
        textDecoration: 'none'
    }
})
</script>

<style scoped>
.animation-trigger { opacity: 1; transform: translateY(0); }
.animate-fade-up { transition: opacity 0.8s ease-out, transform 0.8s ease-out; transform: translateY(20px); }
.animation-delay-200 { transition-delay: 0.2s; }
.animation-delay-400 { transition-delay: 0.4s; }
</style>
