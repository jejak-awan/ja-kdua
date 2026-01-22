<template>
  <div class="video-slider-block" :style="containerStyles">
    <div class="slider-viewport" :style="viewportStyles">
      <div class="slider-track" :style="trackStyles">
        <div 
          v-for="(video, index) in items" 
          :key="index"
          class="video-slide"
          :style="slideStyles"
        >
          <div class="video-thumbnail" :style="thumbnailContainerStyles">
            <img v-if="getThumb(video)" :src="getThumb(video)" class="w-full h-full object-cover" />
            <div v-else class="thumbnail-placeholder">
              <Film />
            </div>
            
            <div v-if="showPlayButton" class="play-overlay" :style="overlayStyles">
              <div class="play-button" :style="playButtonStyles">
                <Play fill="currentColor" />
              </div>
            </div>
          </div>
          <h4 v-if="video.title" class="video-title" :style="titleStyles">{{ video.title }}</h4>
        </div>
      </div>
    </div>
    
    <!-- Navigation -->
    <template v-if="items.length > slidesPerView">
        <button v-if="showArrows" class="slider-arrow slider-arrow--prev" @click="prev"><ChevronLeft /></button>
        <button v-if="showArrows" class="slider-arrow slider-arrow--next" @click="next"><ChevronRight /></button>
        
        <div v-if="showDots" class="slider-dots">
            <button 
                v-for="i in Math.ceil(items.length / slidesPerView)" 
                :key="i" 
                class="slider-dot" 
                :class="{ 'slider-dot--active': currentPage === i - 1 }" 
                @click="currentPage = i - 1" 
            />
        </div>
    </template>
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

const items = computed(() => settings.value.items || [])
const currentPage = ref(0)

const slidesPerView = computed(() => parseInt(getResponsiveValue(settings.value, 'slidesPerView', device.value)) || 1)
const gap = computed(() => parseInt(getResponsiveValue(settings.value, 'gap', device.value)) || 20)
const showArrows = computed(() => getResponsiveValue(settings.value, 'showArrows', device.value) !== false)
const showDots = computed(() => getResponsiveValue(settings.value, 'showDots', device.value) !== false)
const showPlayButton = computed(() => getResponsiveValue(settings.value, 'showPlayButton', device.value) !== false)

const aspectRatio = computed(() => {
  const ar = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '16:9'
  const [w, h] = ar.split(':').map(Number)
  return (h / w) * 100
})

const getThumb = (video) => {
    if (video.thumbnail) return video.thumbnail
    if (video.type === 'youtube' && video.videoId) {
        return `https://img.youtube.com/vi/${video.videoId}/hqdefault.jpg`
    }
    return null
}

const next = () => { 
  const maxPage = Math.ceil(items.value.length / slidesPerView.value) - 1
  currentPage.value = Math.min(currentPage.value + 1, maxPage)
}
const prev = () => { currentPage.value = Math.max(currentPage.value - 1, 0) }

const containerStyles = computed(() => {
  const styles = { position: 'relative', width: '100%' }
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

const viewportStyles = computed(() => ({ overflow: 'hidden', padding: '10px 0' }))

const trackStyles = computed(() => {
    const totalGap = (slidesPerView.value - 1) * gap.value
    const offset = currentPage.value * (100 + (gap.value / 100)) // Approximation
    return {
        display: 'flex',
        gap: `${gap.value}px`,
        transform: `translateX(-${currentPage.value * 100}%)`,
        transition: 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
    }
})

const slideStyles = computed(() => ({
    flex: `0 0 calc(${100 / slidesPerView.value}% - ${(gap.value * (slidesPerView.value - 1)) / slidesPerView.value}px)`,
    minWidth: 0
}))

const thumbnailContainerStyles = computed(() => ({
    paddingTop: `${aspectRatio.value}%`,
    position: 'relative',
    overflow: 'hidden',
    borderRadius: '12px',
    background: '#1a1a1b'
}))

const overlayStyles = computed(() => ({
    backgroundColor: getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.3)',
    position: 'absolute',
    inset: 0,
    zIndex: 1,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
}))

const playButtonStyles = computed(() => {
    const size = getResponsiveValue(settings.value, 'playButtonSize', device.value) || 80
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: getResponsiveValue(settings.value, 'playButtonColor', device.value) || '#ffffff',
        borderRadius: '50%',
        background: 'rgba(255,255,255,0.2)',
        backdropFilter: 'blur(8px)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        border: '1px solid rgba(255,255,255,0.3)'
    }
})

const titleStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'title_', device.value)
    return {
        ...styles,
        marginTop: '12px',
        fontWeight: '600'
    }
})
</script>

<style scoped>
.video-slider-block { width: 100%; }
.video-thumbnail img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: #2d2d2e; color: #4b5563; }
.thumbnail-placeholder svg { width: 48px; height: 48px; opacity: 0.3; }

.play-button svg { width: 40%; height: 40%; transform: translateX(2px); }

.slider-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
}

.slider-arrow:hover { background: #f8fafc; scale: 1.1; }
.slider-arrow--prev { left: -22px; }
.slider-arrow--next { right: -22px; }

.slider-dots { display: flex; justify-content: center; gap: 8px; margin-top: 24px; }
.slider-dot { width: 8px; height: 8px; border-radius: 50%; border: none; background: #cbd5e1; cursor: pointer; transition: all 0.3s; }
.slider-dot--active { background: var(--builder-accent); width: 24px; border-radius: 12px; }
</style>
