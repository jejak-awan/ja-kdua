<template>
  <div class="video-block" :style="wrapperStyles">
    <div class="video-container" :style="containerStyles">
      <!-- YouTube -->
      <iframe 
        v-if="videoType === 'youtube' && youtubeId"
        :src="youtubeEmbedUrl"
        class="video-iframe"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
      />
      
      <!-- Vimeo -->
      <iframe 
        v-else-if="videoType === 'vimeo' && vimeoId"
        :src="vimeoEmbedUrl"
        class="video-iframe"
        frameborder="0"
        allow="autoplay; fullscreen; picture-in-picture"
        allowfullscreen
      />
      
      <!-- Self-hosted -->
      <video 
        v-else-if="videoType === 'selfHosted' && videoUrl"
        :src="videoUrl"
        :poster="settings.posterImage"
        :autoplay="settings.autoplay"
        :loop="settings.loop"
        :muted="settings.muted"
        :controls="settings.controls !== false"
        class="video-element"
      />
      
      <!-- Placeholder -->
      <div v-else class="video-placeholder">
        <Play class="placeholder-icon" />
        <span>Add a video URL</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Play } from 'lucide-vue-next'
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
const device = computed(() => builder?.device?.value || 'desktop')

// Unified URL
const videoUrl = computed(() => getResponsiveValue(settings.value, 'url', device.value) || '')

// Detect Type
const videoType = computed(() => {
  const url = videoUrl.value
  if (!url) return 'none'
  if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube'
  if (url.includes('vimeo.com')) return 'vimeo'
  return 'selfHosted'
})

// Extract YouTube ID
const youtubeId = computed(() => {
  const url = videoUrl.value
  const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/)
  return match ? match[1] : null
})

const youtubeEmbedUrl = computed(() => {
  if (!youtubeId.value) return ''
  const params = new URLSearchParams({
    autoplay: settings.value.autoplay ? '1' : '0',
    loop: settings.value.loop ? '1' : '0',
    mute: settings.value.muted ? '1' : '0',
    controls: settings.value.controls !== false ? '1' : '0'
  })
  return `https://www.youtube.com/embed/${youtubeId.value}?${params.toString()}`
})

// Extract Vimeo ID
const vimeoId = computed(() => {
  const url = videoUrl.value
  const match = url.match(/vimeo\.com\/(\d+)/)
  return match ? match[1] : null
})

const vimeoEmbedUrl = computed(() => {
  if (!vimeoId.value) return ''
  const params = new URLSearchParams({
    autoplay: settings.value.autoplay ? '1' : '0',
    loop: settings.value.loop ? '1' : '0',
    muted: settings.value.muted ? '1' : '0'
  })
  return `https://player.vimeo.com/video/${vimeoId.value}?${params.toString()}`
})

// Aspect ratio padding
const aspectRatioPadding = computed(() => {
  const ratioMap = {
    '16:9': '56.25%',
    '4:3': '75%',
    '1:1': '100%',
    '9:16': '177.78%',
    '21:9': '42.86%'
  }
  const ratio = getResponsiveValue(settings.value, 'aspectRatio', device.value) || '16:9'
  return ratioMap[ratio] || '56.25%'
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  if (alignment === 'center') {
    styles.textAlign = 'center'
  } else if (alignment === 'right') {
    styles.textAlign = 'right'
  } else {
    styles.textAlign = 'left'
  }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const containerStyles = computed(() => {
  const styles = {
    position: 'relative',
    display: 'inline-block',
    width: '100%',
    overflow: 'hidden',
    paddingTop: aspectRatioPadding.value
  }
  
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  
  return styles
})
</script>

<style scoped>
.video-block {
  width: 100%;
}

.video-container {
  background: #000;
}

.video-iframe,
.video-element {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #1a1a1a;
  color: #666;
}

.placeholder-icon {
  width: 48px;
  height: 48px;
}
</style>
