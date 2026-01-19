<template>
  <div class="slider-block" :style="wrapperStyles">
    <div class="slider-container" :style="containerStyles">
      <!-- Slides Container -->
      <draggable
        v-model="module.children"
        item-key="id"
        group="slide_item"
        class="slider-slides-container"
        ghost-class="ja-builder-ghost"
      >
        <template #item="{ element: child, index }">
          <ModuleWrapper
            :module="child"
            :index="index"
          />
        </template>
      </draggable>
      
      <!-- Arrows -->
      <button v-if="showArrows" class="slider-arrow slider-arrow--prev" @click="prevSlide">
        <ChevronLeft />
      </button>
      <button v-if="showArrows" class="slider-arrow slider-arrow--next" @click="nextSlide">
        <ChevronRight />
      </button>
      
      <!-- Dots -->
      <div v-if="showDots" class="slider-dots">
        <button 
          v-for="(slide, index) in sliderSlides" 
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
import { computed, ref, onMounted, onUnmounted, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
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
  module: {
    type: Object,
    required: true
  }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const sliderSlides = computed(() => {
  return (props.module.children || []).map(child => ({
    id: child.id,
    title: child.settings.title,
    content: child.settings.content,
    image: child.settings.image,
    buttonText: child.settings.buttonText,
    buttonUrl: child.settings.buttonUrl
  }))
})

const currentSlide = ref(0)
const showArrows = computed(() => settings.value.showArrows !== false)
const showDots = computed(() => settings.value.showDots !== false)
const overlayEnabled = computed(() => settings.value.overlayEnabled !== false)

let autoplayInterval = null

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % sliderSlides.value.length
}

const prevSlide = () => {
  currentSlide.value = currentSlide.value === 0 
    ? sliderSlides.value.length - 1 
    : currentSlide.value - 1
}

onMounted(() => {
  if (settings.value.autoplay !== false) {
    autoplayInterval = setInterval(nextSlide, settings.value.autoplaySpeed || 5000)
  }
})

onUnmounted(() => {
  if (autoplayInterval) clearInterval(autoplayInterval)
})

// Provide state to SlideItemBlock
provide('sliderState', {
    currentSlide,
    parentSettings: settings
})

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
  const height = getResponsiveValue(settings.value, 'height', device.value) || 400
  const styles = {
    position: 'relative',
    height: `${height}px`,
    overflow: 'hidden'
  }
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  return styles
})

const slideStyles = (slide) => ({
  backgroundImage: slide.image ? `url(${slide.image})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  backgroundSize: 'cover',
  backgroundPosition: 'center'
})

const overlayStyles = computed(() => ({
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)',
  position: 'absolute',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%'
}))

const contentStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  return {
    textAlign: alignment,
    maxWidth: '800px',
    padding: '40px'
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const textStyles = computed(() => getTypographyStyles(settings.value, 'content_', device.value))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        display: 'inline-block',
        marginTop: '20px',
        padding: '10px 20px',
        backgroundColor: settings.value.buttonBackgroundColor || styles.color || '#ffffff',
        textDecoration: 'none',
        borderRadius: '4px'
    }
})
</script>

<style scoped>
.slider-block {
  width: 100%;
}

.slider-container {
  background: #1a1a1a;
}

.slider-slide {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.5s ease;
}

.slider-slide--active {
  opacity: 1;
}

.slider-overlay {
  position: absolute;
  inset: 0;
}

.slider-content {
  position: relative;
  z-index: 1;
  padding: 40px;
  max-width: 800px;
}

.slider-title {
  margin: 0 0 16px;
  font-weight: 700;
}

.slider-text {
  margin: 0;
  opacity: 0.9;
}

.slider-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  transition: background 0.2s ease;
}

.slider-arrow:hover {
  background: rgba(255,255,255,0.4);
}

.slider-arrow--prev {
  left: 16px;
}

.slider-arrow--next {
  right: 16px;
}

.slider-dots {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2;
  display: flex;
  gap: 8px;
}

.slider-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.4);
  cursor: pointer;
  transition: background 0.2s ease;
}

.slider-dot--active {
  background: white;
}
</style>
