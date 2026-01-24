<template>
  <BaseBlock :module="module" :settings="settings" class="video-popup-block">
    <template #default="{ settings: blockSettings }">
      <Card 
          class="video-popup-container relative group h-[500px] rounded-[48px] overflow-hidden cursor-pointer shadow-2xl border-none transition-all duration-700 hover:-translate-y-2 bg-slate-900" 
          :style="containerStyles"
          @click="openPopup"
      >
        <!-- Thumbnail Overlay -->
        <div 
            v-if="blockSettings.thumbnailImage" 
            class="thumbnail-layer absolute inset-0 bg-cover bg-center transition-transform duration-1000 group-hover:scale-110" 
            :style="{ backgroundImage: `url(${blockSettings.thumbnailImage})` }"
        ></div>
        <div v-else class="absolute inset-0 flex items-center justify-center text-slate-800">
             <Film :size="120" class="opacity-10" />
        </div>
        
        <!-- Glass Overlay -->
        <div class="video-popup-overlay absolute inset-0 transition-all duration-700 bg-slate-900/40 group-hover:bg-primary/20 backdrop-blur-[2px] group-hover:backdrop-blur-none" :style="overlayStyles" />
        
        <!-- Content Wrap -->
        <div class="video-content-wrap relative z-10 flex flex-col items-center justify-center h-full gap-8 p-12 text-center">
            <!-- Pulsing Play Button -->
            <div class="relative">
                <div class="absolute inset-0 bg-white/20 rounded-full animate-ping group-hover:bg-primary/40"></div>
                <Button 
                    class="video-popup-button relative w-24 h-24 rounded-full bg-white text-primary hover:bg-white hover:scale-110 shadow-[0_0_50px_rgba(255,255,255,0.3)] transition-all duration-500 flex items-center justify-center p-0 border-none"
                    :style="buttonStyles"
                >
                  <Play class="play-icon fill-current translate-x-1" :size="32" />
                </Button>
            </div>
            
            <div class="flex flex-col items-center">
                <h4 v-if="blockSettings.buttonText" class="button-text font-black text-3xl md:text-4xl text-white tracking-tighter drop-shadow-2xl mb-2 group-hover:text-primary transition-colors duration-500" :style="buttonTextStyles">
                    {{ blockSettings.buttonText || 'Experience the Vision' }}
                </h4>
                <div class="w-12 h-1.5 bg-white/30 rounded-full group-hover:bg-primary group-hover:w-24 transition-all duration-700"></div>
            </div>
        </div>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button } from '../ui'
import { Play, Film } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module.settings || {})

const openPopup = () => {
    if (props.mode === 'edit') return
    const videoUrl = settings.value.videoUrl
    if (!videoUrl) return
    console.log('Opening video popup:', videoUrl)
}

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', currentDevice.value) || 500
  return {
    height: typeof height === 'number' ? `${height}px` : height
  }
})

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || '' 
}))

const buttonStyles = computed(() => {
    const color = settings.value.iconBackgroundColor || ''
    return color ? { backgroundColor: color } : {}
})

const buttonTextStyles = computed(() => getTypographyStyles(settings.value, 'button_', currentDevice.value))
</script>

<style scoped>
.video-popup-block { width: 100%; }
</style>
