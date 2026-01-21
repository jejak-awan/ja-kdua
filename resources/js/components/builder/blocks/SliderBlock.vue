<template>
  <div class="slider-block" :style="wrapperStyles">
    <div class="slider-container" :style="containerStyles" @mouseenter="pause" @mouseleave="resume">
      <!-- Slides -->
      <div class="slider-slides">
        <div 
          v-for="(slide, index) in items" 
          :key="index"
          class="slider-slide"
          :class="{ 'slider-slide--active': currentSlide === index }"
          :style="getSlideStyles(slide)"
        >
          <!-- Overlay -->
          <div v-if="overlayEnabled" class="slider-overlay" :style="overlayStyles"></div>
          
          <!-- Content -->
          <div class="slider-content" :style="getContentStyles(slide)">
            <h2 class="slider-title" :style="titleStyles">{{ slide.title || 'Slide Title' }}</h2>
            <div class="slider-text prose prose-invert" :style="contentStyles" v-html="slide.content"></div>
            <a v-if="slide.buttonText" :href="slide.buttonUrl || '#'" class="slider-button" :style="buttonStyles" @click.prevent>
              {{ slide.buttonText }}
            </a>
          </div>
        </div>
      </div>
      
      <!-- Arrows -->
      <template v-if="showArrows && items.length > 1">
        <button class="slider-arrow slider-arrow--prev" @click="prevSlide">
          <ChevronLeft />
        </button>
        <button class="slider-arrow slider-arrow--next" @click="nextSlide">
          <ChevronRight />
        </button>
      </template>
      
      <!-- Dots -->
      <div v-if="showDots && items.length > 1" class="slider-dots">
        <button 
          v-for="(_, index) in items" 
          :key="index"
          class="slider-dot"
          :class="{ 'slider-dot--active': currentSlide === index }"
          @click="currentSlide = index"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const currentSlide = ref(0)

const showArrows = computed(() => getResponsiveValue(settings.value, 'showArrows', device.value) !== false)
const showDots = computed(() => getResponsiveValue(settings.value, 'showDots', device.value) !== false)
const overlayEnabled = computed(() => getResponsiveValue(settings.value, 'overlayEnabled', device.value) !== false)
const autoplay = computed(() => getResponsiveValue(settings.value, 'autoplay', device.value) !== false)
const autoplaySpeed = computed(() => parseInt(getResponsiveValue(settings.value, 'autoplaySpeed', device.value)) || 5000)

let autoplayInterval = null

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
  if (autoplayInterval) clearInterval(autoplayInterval)
}

const resume = () => {
  if (autoplay.value && items.value.length > 1) {
    pause()
    autoplayInterval = setInterval(nextSlide, autoplaySpeed.value)
  }
}

onMounted(resume)
onUnmounted(pause)

watch([autoplay, autoplaySpeed, items], resume)

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 500
  const styles = {
    position: 'relative',
    height: `${height}px`,
    overflow: 'hidden'
  }
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  return styles
})

const getSlideStyles = (slide) => {
    const transition = getResponsiveValue(settings.value, 'slideTransition', device.value) || 'fade'
    return {
        backgroundImage: slide.image ? `url(${slide.image})` : 'none',
        backgroundColor: slide.image ? 'transparent' : '#1a1a1a',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        transition: transition === 'fade' ? 'opacity 0.6s ease' : 'transform 0.6s ease'
    }
}

const overlayStyles = computed(() => ({
  backgroundColor: getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.4)',
  position: 'absolute',
  inset: 0,
  zIndex: 1
}))

const getContentStyles = (slide) => {
  const alignment = slide.alignment || 'center'
  return {
    textAlign: alignment,
    maxWidth: '800px',
    padding: '40px',
    zIndex: 2,
    position: 'relative'
  }
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', device.value))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        display: 'inline-block',
        marginTop: '24px',
        padding: '12px 32px',
        backgroundColor: styles.color || '#ffffff',
        color: styles.color ? '#ffffff' : '#000000',
        filter: styles.color ? 'invert(1)' : 'none',
        textDecoration: 'none',
        borderRadius: '99px',
        fontWeight: '700'
    }
})
</script>

<style scoped>
.slider-block { width: 100%; }
.slider-container { width: 100%; }

.slider-slides { position: relative; width: 100%; height: 100%; }
.slider-slide {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  pointer-events: none;
}

.slider-slide--active {
  opacity: 1;
  pointer-events: auto;
}

.slider-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 50%;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.slider-arrow:hover { background: white; color: black; transform: translateY(-50%) scale(1.1); }
.slider-arrow--prev { left: 20px; }
.slider-arrow--next { right: 20px; }

.slider-dots {
  position: absolute;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  display: flex;
  gap: 8px;
}

.slider-dot {
  width: 8px;
  height: 8px;
  border-radius: 40px;
  border: none;
  background: rgba(255,255,255,0.3);
  cursor: pointer;
  transition: all 0.3s ease;
}

.slider-dot--active {
  width: 24px;
  background: white;
}
</style>
