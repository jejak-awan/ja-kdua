<template>
  <BaseBlock :module="module" :settings="settings" class="video-popup-block">
    <div 
        class="video-popup-container relative group h-[400px] rounded-2xl overflow-hidden cursor-pointer shadow-xl border border-gray-100 dark:border-gray-800" 
        :style="containerStyles"
        @click="openPopup"
    >
      <!-- Thumbnail Overlay -->
      <div v-if="settings.thumbnailImage" class="thumbnail-layer absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" :style="{ backgroundImage: `url(${settings.thumbnailImage})` }"></div>
      
      <!-- Overlay -->
      <div class="video-popup-overlay absolute inset-0 transition-opacity group-hover:opacity-80" :style="overlayStyles" />
      
      <!-- Play Button -->
      <div class="video-content-wrap relative z-10 flex flex-col items-center justify-center h-full gap-6">
          <button class="video-popup-button flex items-center justify-center transition-all group-hover:scale-110 active:scale-95 shadow-2xl" :style="buttonStyles">
            <Play class="play-icon drop-shadow-lg" :style="iconStyles" />
          </button>
          
          <span v-if="settings.buttonStyle === 'text'" class="button-text font-bold text-lg tracking-tight text-white drop-shadow-md" :style="buttonTextStyles">
            {{ settings.buttonText || 'Watch Video' }}
          </span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Play } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const openPopup = () => {
    if (props.mode === 'edit') return
    const videoUrl = settings.value.videoUrl
    if (!videoUrl) return
    // In a real implementation, this would trigger a global modal
    console.log('Opening video:', videoUrl)
}

const iconSizeValue = computed(() => getResponsiveValue(settings.value, 'iconSize', device.value) || 96)
const iconColorValue = computed(() => getResponsiveValue(settings.value, 'iconColor', device.value) || '#ffffff')
const iconBackgroundColorValue = computed(() => getResponsiveValue(settings.value, 'iconBackgroundColor', device.value) || 'rgba(59, 130, 246, 0.95)')
const overlayColorValue = computed(() => getResponsiveValue(settings.value, 'overlayColor', device.value) || 'rgba(0, 0, 0, 0.4)')

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 450
  return {
    height: typeof height === 'number' ? `${height}px` : height
  }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: overlayColorValue.value 
}))

const buttonStyles = computed(() => ({
  background: iconBackgroundColorValue.value,
  borderRadius: '50%',
  width: `${iconSizeValue.value}px`,
  height: `${iconSizeValue.value}px`,
  minWidth: `${iconSizeValue.value}px`
}))

const iconStyles = computed(() => ({ 
    width: `${iconSizeValue.value * 0.4}px`, 
    height: `${iconSizeValue.value * 0.4}px`, 
    color: iconColorValue.value 
}))

const buttonTextStyles = computed(() => getTypographyStyles(settings.value, 'button_', device.value))
</script>

<style scoped>
.video-popup-block { width: 100%; }
.video-popup-button { border: none; cursor: pointer; }
</style>
