<template>
  <div v-if="hasVideo" class="background-video-container">
    <video
      ref="videoRef"
      class="background-video"
      :src="videoUrl"
      :muted="isMuted"
      :loop="isLoop"
      :autoplay="true"
      :style="videoStyles"
      playsinline
      @loadeddata="onVideoLoaded"
    ></video>
    
    <!-- Video Overlay -->
    <div 
      v-if="overlayColor" 
      class="video-overlay" 
      :style="{ backgroundColor: (overlayColor as string) }"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { computed, ref, type CSSProperties } from 'vue'
import { getVal } from '../utils/styleUtils'
import type { ModuleSettings } from '@/types/builder'

const props = defineProps<{
  settings: ModuleSettings;
  device?: 'desktop' | 'tablet' | 'mobile';
}>();

const videoRef = ref<HTMLVideoElement | null>(null)

const hasVideo = computed(() => {
  return getVal<string>(props.settings, 'backgroundVideoMp4', props.device || 'desktop') || 
         getVal<string>(props.settings, 'backgroundVideoWebm', props.device || 'desktop')
})

const videoUrl = computed(() => {
  return getVal<string>(props.settings, 'backgroundVideoWebm', props.device || 'desktop') || 
         getVal<string>(props.settings, 'backgroundVideoMp4', props.device || 'desktop')
})

const isMuted = computed(() => {
  return getVal<boolean>(props.settings, 'backgroundVideoMute', props.device || 'desktop') !== false
})

const isLoop = computed(() => {
  return getVal<boolean>(props.settings, 'backgroundVideoLoop', props.device || 'desktop') !== false
})

const overlayColor = computed(() => {
  return getVal<string>(props.settings, 'backgroundVideoOverlayColor', props.device || 'desktop') || ''
})

const videoStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number | undefined> = {}
    const device = props.device || 'desktop'
    
    const w = getVal<string | number>(props.settings, 'backgroundVideoWidth', device)
    const h = getVal<string | number>(props.settings, 'backgroundVideoHeight', device)
    
    if (w) {
        styles.width = String(w).includes('%') || String(w).includes('px') ? String(w) : `${w}px`
        styles.minWidth = '0'
    }
    
    if (h) {
        styles.height = String(h).includes('%') || String(h).includes('px') ? String(h) : `${h}px`
        styles.minHeight = '0'
    }
    
    styles.objectFit = (w || h) ? 'contain' : 'cover'
    
    return styles as CSSProperties
})

const onVideoLoaded = () => {
    if (videoRef.value) {
        videoRef.value.play().catch(err => {
            logger.warning('Background video autoplay failed:', err)
        })
    }
}
</script>

<style scoped>
.background-video-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
  pointer-events: none;
}

.background-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}
</style>
