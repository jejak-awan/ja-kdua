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
      :style="{ backgroundColor: overlayColor }"
    ></div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  settings: { type: Object, required: true },
  device: { type: String, default: 'desktop' }
})

const videoRef = ref(null)

const hasVideo = computed(() => {
  return getVal(props.settings, 'backgroundVideoMp4', props.device) || 
         getVal(props.settings, 'backgroundVideoWebm', props.device)
})

const videoUrl = computed(() => {
  return getVal(props.settings, 'backgroundVideoWebm', props.device) || 
         getVal(props.settings, 'backgroundVideoMp4', props.device)
})

const isMuted = computed(() => {
  return getVal(props.settings, 'backgroundVideoMute', props.device) !== false
})

const isLoop = computed(() => {
  return getVal(props.settings, 'backgroundVideoLoop', props.device) !== false
})

const overlayColor = computed(() => {
  return getVal(props.settings, 'backgroundVideoOverlayColor', props.device) || ''
})

const videoStyles = computed(() => {
    const styles = {}
    
    const w = getVal(props.settings, 'backgroundVideoWidth', props.device)
    const h = getVal(props.settings, 'backgroundVideoHeight', props.device)
    
    if (w) {
        styles.width = w
        styles.minWidth = '0'
    }
    
    if (h) {
        styles.height = h
        styles.minHeight = '0'
    }
    
    styles.objectFit = (w || h) ? 'contain' : 'cover'
    
    return styles
})

const onVideoLoaded = () => {
    if (videoRef.value) {
        videoRef.value.play().catch(err => {
            console.warn('Background video autoplay failed:', err)
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
