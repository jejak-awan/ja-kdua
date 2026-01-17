<template>
  <div class="fullwidth-slider-block" :style="containerStyles">
    <div v-for="(slide, index) in slideList" :key="index" class="slider-slide" :class="{ 'slider-slide--active': currentSlide === index }">
      <div class="slide-overlay" :style="overlayStyles" />
      <div class="slide-content" :style="contentStyles">
        <h2 class="slide-title" :style="titleStyles">{{ slide.title || 'Slide Title' }}</h2>
        <p v-if="slide.subtitle" class="slide-subtitle" :style="subtitleStyles">{{ slide.subtitle }}</p>
        <a v-if="slide.buttonText" :href="slide.buttonUrl || '#'" class="slide-button">{{ slide.buttonText }}</a>
      </div>
    </div>
    
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prev"><ChevronLeft /></button>
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="next"><ChevronRight /></button>
    
    <div v-if="settings.showDots !== false" class="slider-dots">
      <button v-for="(_, i) in slideList" :key="i" class="slider-dot" :class="{ 'slider-dot--active': currentSlide === i }" @click="currentSlide = i" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const currentSlide = ref(0)
let interval = null

const slideList = computed(() => {
  return (props.module.children || []).map(child => ({
    title: child.settings.title || 'New Slide',
    subtitle: child.settings.subtitle || '',
    buttonText: child.settings.buttonText || '',
    buttonUrl: child.settings.buttonUrl || '#',
    image: child.settings.image || ''
  }))
})

const next = () => { currentSlide.value = (currentSlide.value + 1) % slideList.value.length }
const prev = () => { currentSlide.value = currentSlide.value === 0 ? slideList.value.length - 1 : currentSlide.value - 1 }

onMounted(() => { if (settings.value.autoplay !== false && slideList.value.length > 0) interval = setInterval(next, settings.value.autoplaySpeed || 5000) })
onUnmounted(() => { if (interval) clearInterval(interval) })

const containerStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    overflow: 'hidden', 
    width: '100%',
    height: `${getResponsiveValue(settings.value, 'height', device.value) || 600}px`
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const overlayStyles = computed(() => ({ 
  position: 'absolute', 
  inset: 0, 
  backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)' 
}))

const contentStyles = computed(() => ({ 
  position: 'relative', 
  zIndex: 1, 
  textAlign: getResponsiveValue(settings.value, 'contentAlignment', device.value) || 'center', 
  width: '100%',
  maxWidth: '1200px', 
  margin: '0 auto' 
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  return {
    ...styles,
    display: 'inline-block',
    padding: '14px 32px',
    backgroundColor: settings.value.buttonBackgroundColor || styles.backgroundColor || '#fff',
    color: settings.value.buttonTextColor || styles.color || '#333',
    textDecoration: 'none',
    borderRadius: '6px',
    fontWeight: styles.fontWeight || 600
  }
})
</script>

<style scoped>
.fullwidth-slider-block { width: 100%; }
.slider-slide { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.5s; }
.slider-slide--active { opacity: 1; }
.slide-subtitle { margin: 0 0 24px; font-size: 20px; opacity: 0.9; }
.slide-button { display: inline-block; padding: 14px 32px; background: #fff; color: #333; text-decoration: none; border-radius: 6px; font-weight: 600; }
.slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; color: white; cursor: pointer; }
.slider-arrow--prev { left: 24px; }
.slider-arrow--next { right: 24px; }
.slider-dots { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 2; display: flex; gap: 8px; }
.slider-dot { width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.4); cursor: pointer; }
.slider-dot--active { background: white; }
</style>
