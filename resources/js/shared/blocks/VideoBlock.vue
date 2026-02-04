<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="video-block transition-colors duration-300"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="video-container relative group overflow-hidden rounded-[40px] shadow-2xl bg-slate-900 border border-slate-100/10" 
        :style="containerStyles"
      >
        <!-- YouTube -->
        <iframe 
          v-if="videoType === 'youtube' && youtubeId"
          :src="youtubeEmbedUrl"
          class="video-iframe absolute inset-0 w-full h-full grayscale-[0.2] group-hover:grayscale-0 transition-colors duration-700"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        />
        
        <!-- Vimeo -->
        <iframe 
          v-else-if="videoType === 'vimeo' && vimeoId"
          :src="vimeoEmbedUrl"
          class="video-iframe absolute inset-0 w-full h-full grayscale-[0.2] group-hover:grayscale-0 transition-colors duration-700"
          frameborder="0"
          allow="autoplay; fullscreen; picture-in-picture"
          allowfullscreen
        />
        
        <!-- Self-hosted -->
        <video 
          v-else-if="videoType === 'selfHosted' && videoUrl"
          :src="videoUrl"
          :poster="(blockSettings.posterImage as string)"
          :autoplay="(blockSettings.autoplay as boolean)"
          :loop="(blockSettings.loop as boolean)"
          :muted="(blockSettings.muted as boolean)"
          :controls="blockSettings.controls !== false"
          class="video-element absolute inset-0 w-full h-full object-cover"
        />
        
        <!-- Placeholder (Mode Edit Only or empty URL) -->
        <div v-else class="video-placeholder absolute inset-0 flex flex-col items-center justify-center gap-6 bg-slate-900 text-slate-700">
          <div class="w-20 h-20 rounded-full border-2 border-slate-800 flex items-center justify-center transition-[width] duration-500 group-hover:scale-110 group-hover:border-primary group-hover:text-primary">
              <Play :size="32" class="translate-x-0.5" />
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">{{ mode === 'edit' ? 'Video Stream Ready' : 'No Source' }}</span>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import BaseBlock from '../components/BaseBlock.vue'
import { 
    getVal,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

// Unified URL
const videoUrl = computed(() => getVal<string>(settings.value, 'url', props.device) || '')

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
    autoplay: (settings.value.autoplay as boolean) ? '1' : '0',
    loop: (settings.value.loop as boolean) ? '1' : '0',
    mute: (settings.value.muted as boolean) ? '1' : '0',
    controls: (settings.value.controls as boolean) !== false ? '1' : '0',
    playlist: (settings.value.loop as boolean) ? youtubeId.value : '' 
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
    autoplay: (settings.value.autoplay as boolean) ? '1' : '0',
    loop: (settings.value.loop as boolean) ? '1' : '0',
    muted: (settings.value.muted as boolean) ? '1' : '0'
  })
  return `https://player.vimeo.com/video/${vimeoId.value}?${params.toString()}`
})

// Aspect ratio padding
const aspectRatioPadding = computed(() => {
  const ratioMap: Record<string, string> = {
    '16:9': '56.25%',
    '4:3': '75%',
    '1:1': '100%',
    '9:16': '177.78%',
    '21:9': '42.86%'
  }
  const ratio = getVal<string>(settings.value, 'aspectRatio', props.device) || '16:9'
  return ratioMap[ratio] || '56.25%'
})

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const alignment = getVal<string>(settings.value, 'alignment', props.device) || 'center'
    
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        paddingTop: aspectRatioPadding.value,
        marginLeft: alignment === 'left' ? '0' : (alignment === 'right' ? 'auto' : 'auto'),
        marginRight: alignment === 'right' ? '0' : (alignment === 'left' ? 'auto' : 'auto')
    }
    return styles
})
</script>

<style scoped>
.video-block { width: 100%; }
.video-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.video-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
