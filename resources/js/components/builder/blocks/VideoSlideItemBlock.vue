<template>
  <div class="video-slide-item-block" :style="itemStyles">
    <div class="video-thumbnail" :style="{ aspectRatio: aspectRatioValue }">
      <img v-if="settings.thumbnail" :src="settings.thumbnail" :alt="settings.title" />
      <div v-else class="thumbnail-placeholder">
        <Film />
      </div>
      <div v-if="parentSettings.showPlayButton !== false" class="play-overlay" :style="overlayStyles">
        <button class="play-button" :style="playButtonStyles">
          <Play />
        </button>
      </div>
    </div>
    <h4 v-if="settings.title" class="video-title" :style="titleStyles">{{ settings.title }}</h4>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Film, Play } from 'lucide-vue-next'
import { getTypographyStyles, getResponsiveValue } from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

// Injected from VideoSliderBlock
const videoSliderState = inject('videoSliderState', {
    parentSettings: {},
    slidesPerView: computed(() => 1),
    gap: computed(() => 20)
})

const parentSettings = computed(() => videoSliderState.parentSettings.value || {})
const slidesPerView = videoSliderState.slidesPerView
const gap = videoSliderState.gap

const aspectRatioValue = computed(() => {
  const ar = getResponsiveValue(parentSettings.value, 'aspectRatio', device.value) || '16:9'
  return ar.replace(':', '/')
})

const itemStyles = computed(() => ({
  flex: `0 0 calc(${100 / slidesPerView.value}% - ${gap.value}px)`,
  minWidth: 0,
  position: 'relative'
}))

const overlayColorValue = computed(() => getResponsiveValue(parentSettings.value, 'overlayColor', device.value) || 'rgba(0,0,0,0.3)')
const overlayStyles = computed(() => ({ backgroundColor: overlayColorValue.value }))

const playButtonColorValue = computed(() => getResponsiveValue(parentSettings.value, 'playButtonColor', device.value) || '#ffffff')
const playButtonStyles = computed(() => {
  const size = getResponsiveValue(parentSettings.value, 'playButtonSize', device.value) || 64
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: playButtonColorValue.value
  }
})

const titleStyles = computed(() => getTypographyStyles(parentSettings.value, 'title_', device.value))
</script>

<style scoped>
.video-slide-item-block { flex-shrink: 0; }
.video-thumbnail { position: relative; overflow: hidden; border-radius: 8px; background: #1a1a1a; width: 100%; }
.video-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.thumbnail-placeholder svg { width: 48px; height: 48px; opacity: 0.6; }
.play-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
.video-thumbnail:hover .play-overlay { opacity: 1; }
.play-button { border-radius: 50%; background: rgba(255,255,255,0.2); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(4px); }
.play-button svg { width: 40%; height: 40%; }
.video-title { margin: 12px 0 0; }
</style>
