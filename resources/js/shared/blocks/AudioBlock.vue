<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="audio-block" :style="audioBlockStyles">
        <div class="audio-player" :style="playerStyles">
          <!-- Play Button -->
          <button class="play-button" :style="playButtonStyles" @click="togglePlay">
            <component :is="isPlaying ? Pause : Play" class="play-icon" />
          </button>
          
          <!-- Info -->
          <div class="audio-info">
            <div class="audio-title" :style="trackNameStyles">{{ settings.trackName || 'Audio Track' }}</div>
            <div v-if="settings.artistName" class="audio-artist" :style="artistNameStyles">{{ settings.artistName }}</div>
          </div>
          
          <!-- Progress -->
          <div class="audio-progress">
            <div class="progress-bar" :style="progressBarStyles">
              <div class="progress-fill" :style="progressFillStyles" />
            </div>
            <div class="progress-time" :style="timeStyles">0:00 / 0:00</div>
          </div>
          
          <!-- Download -->
          <a v-if="settings.showDownload && settings.url" :href="settings.url" download class="download-button">
            <Download class="download-icon" />
          </a>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Play, Pause, Download } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const isPlaying = ref(false)
const togglePlay = () => {
  isPlaying.value = !isPlaying.value
  // Note: Actual audio playing logic would go here if we were using an <audio> tag
}

const audioBlockStyles = computed(() => {
  return {
    width: '100%'
  }
})

const playerStyles = computed(() => {
  return {
    display: 'flex',
    alignItems: 'center',
    gap: '16px'
  }
})

const playButtonStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', props.device) || '#2059ea'
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

const trackNameStyles = computed(() => getTypographyStyles(settings.value, 'trackName_', props.device))
const artistNameStyles = computed(() => getTypographyStyles(settings.value, 'artistName_', props.device))

const progressBarStyles = computed(() => ({
  height: '4px',
  backgroundColor: 'rgba(255,255,255,0.2)',
  borderRadius: '2px',
  overflow: 'hidden'
}))

const progressFillStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', props.device) || '#2059ea'
  return {
    width: '30%',
    height: '100%',
    backgroundColor: accent
  }
})

const timeStyles = computed(() => {
  const base = artistNameStyles.value
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
