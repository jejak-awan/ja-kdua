<template>
  <div class="post-slider-block" :style="wrapperStyles">
    <h2 
      v-if="settings.title || builder?.isEditing" 
      class="slider-title-main" 
      :style="titleStyles"
      contenteditable="true"
      @blur="e => builder.updateModuleSetting(module.id, 'title', e.target.innerText)"
    >
      {{ settings.title }}
    </h2>
    <div class="slider-container" :style="containerStyles">
      <div v-for="(post, index) in mockPosts" :key="index" class="slider-slide" :class="{ 'slider-slide--active': currentSlide === index }">
        <div v-if="overlayEnabled" class="slide-overlay" :style="overlayStyles" />
        <div class="slide-content" :style="contentStyles">
          <span v-if="settings.showMeta !== false" class="slide-meta" :style="metaStyles">Jan 10, 2026 • 5 min read</span>
          <h2 class="slide-title" :style="titleStyles">{{ post.title }}</h2>
          <p v-if="settings.showExcerpt !== false" class="slide-excerpt" :style="excerptStyles">{{ post.excerpt }}</p>
          <a href="#" class="slide-button" :style="buttonStyles">Read More</a>
        </div>
      </div>
      
      <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prevSlide"><ChevronLeft /></button>
      <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="nextSlide"><ChevronRight /></button>
      
      <div v-if="settings.showDots !== false" class="slider-dots">
        <button v-for="(_, index) in mockPosts" :key="index" class="slider-dot" :class="{ 'slider-dot--active': currentSlide === index }" @click="currentSlide = index" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, inject } from 'vue'
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

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const currentSlide = ref(0)
const overlayEnabled = computed(() => settings.value.overlayEnabled !== false)
let interval = null

const mockPosts = computed(() => Array.from({ length: settings.value.totalPosts || 5 }, (_, i) => ({
  title: `Featured Post Title ${i + 1}`,
  excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.'
})))

const nextSlide = () => { currentSlide.value = (currentSlide.value + 1) % mockPosts.value.length }
const prevSlide = () => { currentSlide.value = currentSlide.value === 0 ? mockPosts.value.length - 1 : currentSlide.value - 1 }

onMounted(() => { if (settings.value.autoplay !== false) interval = setInterval(nextSlide, settings.value.autoplaySpeed || 5000) })
onUnmounted(() => { if (interval) clearInterval(interval) })

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
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

const containerStyles = computed(() => {
    const height = getResponsiveValue(settings.value, 'height', device.value) || 500
    // Check if height has unit, if not add px
    const hValue = (typeof height === 'number' || !isNaN(Number(height))) ? `${height}px` : height
    
    return {
        position: 'relative',
        height: hValue,
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        overflow: 'hidden'
    }
})

const overlayStyles = computed(() => ({ backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)' }))
const contentStyles = computed(() => ({ color: settings.value.textColor || '#ffffff' }))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', device.value))

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#fff',
        display: 'inline-block',
        padding: '12px 28px',
        borderRadius: '6px',
        textDecoration: 'none'
    }
})
</script>

<style scoped>
.post-slider-block { width: 100%; }
.slider-title-main { 
    margin-bottom: 32px; 
    text-align: center; 
    width: 100%;
}
.slider-slide { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.5s; }
.slider-slide--active { opacity: 1; }
.slide-overlay { position: absolute; inset: 0; }
.slide-content { position: relative;z-index: 1; text-align: center; padding: 40px; max-width: 800px; }
.slide-meta { font-size: 14px; opacity: 0.8; }
.slide-title { margin: 16px 0; font-weight: 700; line-height: 1.2; }
.slide-excerpt { margin: 0 0 24px; opacity: 0.9; line-height: 1.6; }
.slide-button { display: inline-block; padding: 12px 28px; background: #fff; color: #333; text-decoration: none; border-radius: 6px; font-weight: 600; }
.slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; color: white; cursor: pointer; }
.slider-arrow--prev { left: 20px; }
.slider-arrow--next { right: 20px; }
.slider-dots { position: absolute; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 2; display: flex; gap: 8px; }
.slider-dot { width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.4); cursor: pointer; }
.slider-dot--active { background: white; }
</style>
