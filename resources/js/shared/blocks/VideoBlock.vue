<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperStyles, settings }">
      <div class="video-block" :style="videoBlockStyles">
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
          
          <!-- Placeholder (Mode Edit Only or empty URL) -->
          <div v-else class="video-placeholder">
            <Play class="placeholder-icon" />
            <span>{{ mode === 'edit' ? 'Add a video URL' : '' }}</span>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import { Play } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

// Unified URL
const videoUrl = computed(() => getVal(settings.value, 'url', props.device) || '')

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
    controls: settings.value.controls !== false ? '1' : '0',
    playlist: settings.value.loop ? youtubeId.value : '' // Needed for looping
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
  const ratio = getVal(settings.value, 'aspectRatio', props.device) || '16:9'
  return ratioMap[ratio] || '56.25%'
})

const videoBlockStyles = computed(() => {
  return {
    width: '100%',
    textAlign: getVal(settings.value, 'alignment', props.device) || 'center'
  }
})

const containerStyles = computed(() => {
  return {
    position: 'relative',
    display: 'inline-block',
    width: '100%',
    overflow: 'hidden',
    paddingTop: aspectRatioPadding.value,
    backgroundColor: '#000'
  }
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
