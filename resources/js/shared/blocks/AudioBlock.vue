<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="audio-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Audio Player'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <Card 
        class="audio-player-card group w-full border-none shadow-xl rounded-[32px] overflow-hidden bg-white dark:bg-slate-900 px-8 py-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1"
        :style="containerStyles"
      >
        <div class="audio-player flex items-center gap-6">
          <!-- Play Button -->
          <div class="relative shrink-0">
              <div v-if="isPlaying" class="absolute inset-0 bg-primary/20 rounded-full animate-ping"></div>
              <Button 
                class="play-button w-14 h-14 rounded-full shadow-lg transition-all duration-300 hover:scale-110 active:scale-95 flex items-center justify-center p-0 border-none" 
                :style="playButtonStyles" 
                @click="togglePlay"
              >
                <component :is="isPlaying ? Pause : Play" class="w-6 h-6 fill-current translate-x-0.5" />
              </Button>
          </div>
          
          <!-- Info & Progress Container -->
          <div class="flex-grow flex flex-col gap-4">
              <div class="audio-header flex justify-between items-end">
                  <div class="audio-info flex flex-col">
                    <div class="audio-title font-black text-slate-900 dark:text-white tracking-tight leading-none mb-1" :style="trackNameStyles">
                        {{ blockSettings.trackName || 'Ethereal Soundscapes' }}
                    </div>
                    <div v-if="blockSettings.artistName" class="audio-artist text-[10px] font-black uppercase tracking-[0.2em] text-slate-400" :style="artistNameStyles">
                        {{ blockSettings.artistName || 'Antigravity Ensemble' }}
                    </div>
                  </div>
                  
                  <div class="progress-time text-[10px] font-black tabular-nums text-slate-400">
                    0:42 / 3:15
                  </div>
              </div>
              
              <!-- Progress -->
              <div class="audio-progress relative w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="progress-fill absolute inset-y-0 left-0 transition-all duration-500 rounded-full" :style="progressFillStyles" />
              </div>
          </div>
          
          <!-- Download (Right Side) -->
          <Button 
            v-if="blockSettings.showDownload && blockSettings.url" 
            as="a" 
            :href="blockSettings.url" 
            download 
            variant="ghost" 
            class="download-button w-10 h-10 p-0 rounded-full opacity-40 hover:opacity-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
          >
            <Download class="w-5 h-5" />
          </Button>
        </div>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Play, Pause, Download } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button } from '../ui'
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const isPlaying = ref(false)
const togglePlay = () => {
    if (props.mode === 'edit') return
    isPlaying.value = !isPlaying.value
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const playButtonStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', props.device) || 'currentColor'
  return {
    backgroundColor: accent,
    color: '#ffffff',
  }
})

const trackNameStyles = computed(() => getTypographyStyles(settings.value, 'trackName_', props.device))
const artistNameStyles = computed(() => getTypographyStyles(settings.value, 'artistName_', props.device))

const progressFillStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', props.device) || '#2059ea'
  return {
    width: '30%',
    backgroundColor: accent
  }
})
</script>

<style scoped>
.audio-block { width: 100%; }
.audio-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.audio-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
