<template>
  <BaseBlock :module="module" :settings="settings" class="video-slider-block">
    <div class="slider-viewport relative overflow-hidden p-2" :style="viewportStyles">
      <div 
        class="slider-track flex transition-transform duration-500 cubic-bezier(0.4, 0, 0.2, 1)" 
        :style="trackStyles"
      >
        <div 
          v-for="(video, index) in items" 
          :key="index"
          class="video-slide flex-shrink-0 min-w-0 px-2"
          :style="slideStyles"
        >
          <div 
            class="video-card bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-lg border border-gray-100 dark:border-gray-700 transition-all hover:shadow-2xl hover:-translate-y-1 group cursor-pointer"
            @click="handlePlay(video)"
          >
            <div class="video-thumbnail relative overflow-hidden aspect-video bg-gray-900" :style="thumbnailContainerStyles(video)">
              <img v-if="getThumb(video)" :src="getThumb(video)" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
              <div v-else class="thumbnail-placeholder absolute inset-0 flex items-center justify-center text-gray-500">
                <Film class="w-12 h-12 opacity-20" />
              </div>
              
              <div v-if="showPlayButton" class="play-overlay absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="play-button w-16 h-16 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white shadow-2xl transition-transform duration-300 group-hover:scale-110" :style="playButtonStyles">
                  <Play class="w-8 h-8 fill-current translate-x-0.5" />
                </div>
              </div>
            </div>
            <div class="video-info p-5">
              <h4 v-if="video.title" class="video-title text-lg font-bold line-clamp-1" :style="titleStyles">{{ video.title }}</h4>
              <p v-if="video.description" class="text-sm opacity-60 mt-2 line-clamp-2">{{ video.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Navigation Buttons -->
    <template v-if="items.length > slidesPerView">
        <button 
            v-if="showArrows" 
            class="slider-arrow prev absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-100 dark:border-gray-700 z-10 transition-all hover:scale-110 active:scale-95" 
            @click="prev"
        >
            <ChevronLeft class="w-6 h-6" />
        </button>
        <button 
            v-if="showArrows" 
            class="slider-arrow next absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-12 h-12 flex items-center justify-center bg-white dark:bg-gray-800 rounded-full shadow-xl border border-gray-100 dark:border-gray-700 z-10 transition-all hover:scale-110 active:scale-95" 
            @click="next"
        >
            <ChevronRight class="w-6 h-6" />
        </button>
        
        <div v-if="showDots" class="slider-dots flex justify-center gap-3 mt-8">
            <button 
                v-for="i in Math.ceil(items.length / slidesPerView)" 
                :key="i" 
                class="slider-dot h-2 rounded-full transition-all duration-300" 
                :class="currentPage === i - 1 ? 'w-8 bg-blue-500' : 'w-2 bg-gray-300 dark:bg-gray-700'" 
                @click="currentPage = i - 1" 
            />
        </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Film, Play, ChevronLeft, ChevronRight } from 'lucide-vue-next'
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

const items = computed(() => settings.value.items || [
    { title: 'Sample Video 1', type: 'youtube', videoId: 'dQw4w9WgXcQ' },
    { title: 'Sample Video 2', type: 'youtube', videoId: 'dQw4w9WgXcQ' },
    { title: 'Sample Video 3', type: 'youtube', videoId: 'dQw4w9WgXcQ' }
])
const currentPage = ref(0)

const slidesPerView = computed(() => parseInt(getResponsiveValue(settings.value, 'slidesPerView', device.value)) || 1)
const gap = computed(() => parseInt(getResponsiveValue(settings.value, 'gap', device.value)) || 20)
const showArrows = computed(() => getResponsiveValue(settings.value, 'showArrows', device.value) !== false)
const showDots = computed(() => getResponsiveValue(settings.value, 'showDots', device.value) !== false)
const showPlayButton = computed(() => getResponsiveValue(settings.value, 'showPlayButton', device.value) !== false)

const handlePlay = (video) => {
    if (props.mode === 'edit') return
    console.log('Playing video:', video)
}

const getThumb = (video) => {
    if (video.thumbnail) return video.thumbnail
    if (video.type === 'youtube' && video.videoId) {
        return `https://img.youtube.com/vi/${video.videoId}/hqdefault.jpg`
    }
    return null
}

const next = () => { 
  const maxPage = Math.ceil(items.value.length / slidesPerView.value) - 1
  currentPage.value = (currentPage.value + 1) % (maxPage + 1)
}
const prev = () => { 
  const maxPage = Math.ceil(items.value.length / slidesPerView.value) - 1
  currentPage.value = (currentPage.value - 1 + (maxPage + 1)) % (maxPage + 1)
}

const viewportStyles = computed(() => ({}))

const trackStyles = computed(() => ({
    transform: `translateX(-${currentPage.value * 100}%)`
}))

const slideStyles = computed(() => ({
    flex: `0 0 ${100 / slidesPerView.value}%`
}))

const thumbnailContainerStyles = (video) => ({})

const playButtonStyles = computed(() => {
    const size = getResponsiveValue(settings.value, 'playButtonSize', device.value) || 64
    const color = getResponsiveValue(settings.value, 'playButtonColor', device.value) || '#ffffff'
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: color
    }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
</script>

<style scoped>
.video-slider-block { width: 100%; position: relative; }
</style>
