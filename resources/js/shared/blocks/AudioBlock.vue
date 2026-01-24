<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <Card class="audio-block group w-full border-none shadow-xl rounded-[32px] overflow-hidden bg-white dark:bg-slate-900 px-8 py-6 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1">
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
                        {{ settings.trackName || 'Ethereal Soundscapes' }}
                    </div>
                    <div v-if="settings.artistName" class="audio-artist text-[10px] font-black uppercase tracking-[0.2em] text-slate-400" :style="artistNameStyles">
                        {{ settings.artistName || 'Antigravity Ensemble' }}
                    </div>
                  </div>
                  
                  <div class="progress-time text-[10px] font-black tabular-nums text-slate-400" :style="timeStyles">
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
            v-if="settings.showDownload && settings.url" 
            as="a" 
            :href="settings.url" 
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

<script setup>
import { computed, ref, inject } from 'vue'
import { Play, Pause, Download } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module?.settings || {})

const isPlaying = ref(false)
const togglePlay = () => {
    if (props.mode === 'edit') return
    isPlaying.value = !isPlaying.value
}

const playButtonStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', currentDevice.value) || 'currentColor'
  return {
    backgroundColor: accent,
    color: '#ffffff',
  }
})

const trackNameStyles = computed(() => getTypographyStyles(settings.value, 'trackName_', currentDevice.value))
const artistNameStyles = computed(() => getTypographyStyles(settings.value, 'artistName_', currentDevice.value))

const progressFillStyles = computed(() => {
  const accent = getVal(settings.value, 'accentColor', currentDevice.value) || '#2059ea'
  return {
    width: '30%',
    backgroundColor: accent
  }
})

const timeStyles = computed(() => getTypographyStyles(settings.value, 'time_', currentDevice.value))
</script>

<style scoped>
.audio-block { width: 100%; }
</style>
