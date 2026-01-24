<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="video-block w-full">
        <div class="video-container relative group overflow-hidden rounded-[40px] shadow-2xl bg-slate-900 border border-slate-100/10" :style="containerStyles">
          <!-- YouTube -->
          <iframe 
            v-if="videoType === 'youtube' && youtubeId"
            :src="youtubeEmbedUrl"
            class="video-iframe absolute inset-0 w-full h-full grayscale-[0.2] group-hover:grayscale-0 transition-all duration-700"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          />
          
          <!-- Vimeo -->
          <iframe 
            v-else-if="videoType === 'vimeo' && vimeoId"
            :src="vimeoEmbedUrl"
            class="video-iframe absolute inset-0 w-full h-full grayscale-[0.2] group-hover:grayscale-0 transition-all duration-700"
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
            class="video-element absolute inset-0 w-full h-full object-cover"
          />
          
          <!-- Placeholder (Mode Edit Only or empty URL) -->
          <div v-else class="video-placeholder absolute inset-0 flex flex-col items-center justify-center gap-6 bg-slate-900 text-slate-700">
            <div class="w-20 h-20 rounded-full border-2 border-slate-800 flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:border-primary group-hover:text-primary">
                <Play :size="32" class="translate-x-0.5" />
            </div>
            <span class="text-[10px] font-black uppercase tracking-[0.3em] opacity-40">{{ mode === 'edit' ? 'Video Stream Ready' : 'No Source' }}</span>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Play } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')
const settings = computed(() => props.module?.settings || {})

// Unified URL
const videoUrl = computed(() => getVal(settings.value, 'url', currentDevice.value) || '')

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
    playlist: settings.value.loop ? youtubeId.value : '' 
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
  const ratio = getVal(settings.value, 'aspectRatio', currentDevice.value) || '16:9'
  return ratioMap[ratio] || '56.25%'
})

const containerStyles = computed(() => {
  return {
    paddingTop: aspectRatioPadding.value,
  }
})
</script>

<style scoped>
.video-block { width: 100%; }
</style>
