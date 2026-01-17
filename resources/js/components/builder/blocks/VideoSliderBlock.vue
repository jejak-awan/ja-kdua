<template>
  <div class="video-slider-block" :style="containerStyles">
    <div class="slider-viewport" :style="viewportStyles">
      <div class="slider-track" :style="trackStyles">
        <div v-for="(video, index) in videoList" :key="index" class="video-slide" :style="slideStyles">
          <div class="video-thumbnail" :style="{ aspectRatio: aspectRatioValue }">
            <img v-if="video.thumbnail" :src="video.thumbnail" :alt="video.title" />
            <div v-else class="thumbnail-placeholder">
              <Film />
            </div>
            <div v-if="settings.showPlayButton !== false" class="play-overlay" :style="overlayStyles">
              <button class="play-button" :style="playButtonStyles">
                <Play />
              </button>
            </div>
          </div>
          <h4 v-if="video.title" class="video-title" :style="titleStyles">{{ video.title }}</h4>
        </div>
      </div>
    </div>
    
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--prev" @click="prev"><ChevronLeft /></button>
    <button v-if="settings.showArrows !== false" class="slider-arrow slider-arrow--next" @click="next"><ChevronRight /></button>
    
    <div v-if="settings.showDots !== false" class="slider-dots">
      <button v-for="i in Math.ceil(videoList.length / slidesPerView)" :key="i" class="slider-dot" :class="{ 'slider-dot--active': currentPage === i - 1 }" @click="currentPage = i - 1" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Film, Play, ChevronLeft, ChevronRight } from 'lucide-vue-next'
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

const currentPage = ref(0)

const videoList = computed(() => {
  return (props.module.children || []).map(child => ({
    id: child.id,
    type: child.settings.type || 'youtube',
    videoId: child.settings.videoId || '',
    thumbnail: child.settings.thumbnail || '',
    title: child.settings.title || ''
  }))
})

const slidesPerView = computed(() => getResponsiveValue(settings.value, 'slidesPerView', device.value) || 1)
const gap = computed(() => getResponsiveValue(settings.value, 'gap', device.value) || 20)
const aspectRatioValue = computed(() => {
  // We can't use getResponsiveValue for aspectRatio yet as it returns a string '16:9' which needs conversion
  // But wait, the computed prop should handle the value retrieval.
  const ar = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '16:9'
  return ar.replace(':', '/')
})

const next = () => { 
  const maxPage = Math.ceil(videoList.value.length / slidesPerView.value) - 1
  currentPage.value = Math.min(currentPage.value + 1, maxPage)
}
const prev = () => { currentPage.value = Math.max(currentPage.value - 1, 0) }

const containerStyles = computed(() => {
  const styles = { position: 'relative' }
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

const viewportStyles = computed(() => ({ overflow: 'hidden' }))
const trackStyles = computed(() => ({
  display: 'flex',
  gap: `${gap.value}px`,
  transform: `translateX(-${currentPage.value * (100 / slidesPerView.value)}%)`,
  transition: 'transform 0.4s ease'
}))
const slideStyles = computed(() => ({
  flex: `0 0 calc(${100 / slidesPerView.value}% - ${gap.value}px)`,
  minWidth: 0
}))

const overlayColorValue = computed(() => getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.3)')
const playButtonColorValue = computed(() => getResponsiveValue(settings.value, 'playButtonColor', device.value) || '#ffffff')

// ... (containerStyles, viewportStyles, trackStyles, slideStyles same as before)

const overlayStyles = computed(() => ({ backgroundColor: overlayColorValue.value }))
const playButtonStyles = computed(() => {
  const size = getResponsiveValue(settings.value, 'playButtonSize', device.value) || 80
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: playButtonColorValue.value
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.video-slider-block { width: 100%; }
.slider-viewport { width: 100%; }
.slider-track { display: flex; }
.video-slide { flex-shrink: 0; }
.video-thumbnail { position: relative; overflow: hidden; border-radius: 8px; background: #1a1a1a; }
.video-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.thumbnail-placeholder svg { width: 48px; height: 48px; opacity: 0.6; }
.play-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.video-thumbnail:hover .play-overlay { opacity: 1; }
.play-button { border-radius: 50%; background: rgba(255,255,255,0.2); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(4px); }
.play-button svg { width: 40%; height: 40%; }
.video-title { margin: 12px 0 0; }
.slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: white; border: 1px solid #e0e0e0; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.slider-arrow--prev { left: -22px; }
.slider-arrow--next { right: -22px; }
.slider-dots { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
.slider-dot { width: 10px; height: 10px; border-radius: 50%; border: none; background: #ddd; cursor: pointer; }
.slider-dot--active { background: #2059ea; }
</style>
