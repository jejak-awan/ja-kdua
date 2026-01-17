<template>
  <div class="audio-block" :style="wrapperStyles">
    <div class="audio-player" :style="playerStyles">
      <!-- Play Button -->
      <button class="play-button" :style="playButtonStyles" @click="togglePlay">
        <component :is="isPlaying ? Pause : Play" class="play-icon" />
      </button>
      
      <!-- Info -->
      <div class="audio-info">
        <div class="audio-title" :style="titleStyles">{{ settings.title || 'Audio Track' }}</div>
        <div v-if="settings.artist" class="audio-artist" :style="artistStyles">{{ settings.artist }}</div>
      </div>
      
      <!-- Progress -->
      <div class="audio-progress">
        <div class="progress-bar" :style="progressBarStyles">
          <div class="progress-fill" :style="progressFillStyles" />
        </div>
        <div class="progress-time" :style="timeStyles">0:00 / 0:00</div>
      </div>
      
      <!-- Download -->
      <a v-if="settings.showDownload && settings.audioUrl" :href="settings.audioUrl" download class="download-button">
        <Download class="download-icon" />
      </a>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Play, Pause, Download } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles,
  getResponsiveValue 
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const isPlaying = ref(false)
const togglePlay = () => {
  isPlaying.value = !isPlaying.value
}

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  return styles
})

const playerStyles = computed(() => {
  const styles = {
    display: 'flex',
    alignItems: 'center',
    gap: '16px'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const playButtonStyles = computed(() => {
  const accent = getResponsiveValue(settings.value, 'accentColor', device.value) || '#2059ea'
  return {
    width: '44px',
    height: '44px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: accent,
    color: '#ffffff',
    border: 'none',
    borderRadius: '50%',
    cursor: 'pointer',
    flexShrink: 0
  }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const artistStyles = computed(() => getTypographyStyles(settings.value, 'artist_', device.value))

const progressBarStyles = computed(() => ({
  height: '4px',
  backgroundColor: 'rgba(255,255,255,0.2)',
  borderRadius: '2px',
  overflow: 'hidden'
}))

const progressFillStyles = computed(() => {
  const accent = getResponsiveValue(settings.value, 'accentColor', device.value) || '#2059ea'
  return {
    width: '30%',
    height: '100%',
    backgroundColor: accent
  }
})

const timeStyles = computed(() => {
  const base = getTypographyStyles(settings.value, 'artist_', device.value)
  return {
    fontSize: base.fontSize || '11px',
    opacity: 0.6,
    marginTop: '4px',
    color: base.color
  }
})
</script>

<style scoped>
.audio-block { width: 100%; }
.audio-info { flex-shrink: 0; min-width: 120px; }
.audio-progress { flex: 1; }
.play-icon { width: 20px; height: 20px; }
.download-button { display: flex; align-items: center; justify-content: center; padding: 8px; color: inherit; opacity: 0.6; transition: opacity 0.2s; }
.download-button:hover { opacity: 1; }
.download-icon { width: 18px; height: 18px; }
</style>
