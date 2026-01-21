<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="gallery-block" :style="galleryBlockStyles">
        <div class="gallery-grid" :style="gridStyles">
          <div 
            v-for="(image, index) in galleryImages" 
            :key="index"
            class="gallery-item group relative aspect-square overflow-hidden rounded-2xl bg-muted/50 border border-primary/5 cursor-pointer shadow-lg transition-all duration-500 hover:shadow-2xl hover:border-primary/20"
            :style="itemStyles"
          >
            <div class="gallery-image-wrapper w-full h-full" :style="imageWrapperStyles">
                <img 
                    v-if="image.url"
                    :src="image.url" 
                    :alt="image.alt || 'Gallery Image'"
                    class="gallery-image w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                >
                 <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-400">
                    <ImageIcon :size="48" />
                 </div>

                <div v-if="showCaptions && image.caption && captionPosition === 'overlay'" class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-6 opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0 text-white">
                    <p class="text-sm font-semibold tracking-wide" :style="captionStyles">{{ image.caption }}</p>
                </div>
            </div>
            <p v-if="showCaptions && image.caption && captionPosition === 'below'" class="mt-2 text-center text-sm" :style="captionStyles">{{ image.caption }}</p>
          </div>
          
          <!-- Placeholder when empty (Mode Edit Only) -->
          <template v-if="galleryImages.length === 0 && mode === 'edit'">
            <div v-for="i in 3" :key="i" class="gallery-item gallery-placeholder" :style="itemStyles">
              <div class="gallery-image-wrapper p-8">
                <ImageIcon class="placeholder-icon text-gray-300" :size="32" />
                <span class="text-xs text-gray-400 mt-2">Add images in settings</span>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import { Image as ImageIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const galleryImages = computed(() => {
  const imgs = getVal(settings.value, 'images', props.device)
  return Array.isArray(imgs) ? imgs : []
})

const showCaptions = computed(() => getVal(settings.value, 'showCaptions', props.device))
const captionPosition = computed(() => getVal(settings.value, 'captionPosition', props.device) || 'below')

const galleryBlockStyles = computed(() => {
  return {
    width: '100%'
  }
})

const gridStyles = computed(() => {
  const cols = getVal(settings.value, 'columns', props.device) || 3
  const gap = getVal(settings.value, 'gap', props.device) || 16
  return {
    display: 'grid',
    gridTemplateColumns: `repeat(${cols}, 1fr)`,
    gap: typeof gap === 'number' ? `${gap}px` : gap
  }
})

const itemStyles = computed(() => {
  return {
    overflow: 'hidden'
  }
})

const imageWrapperStyles = computed(() => {
  const ratio = getVal(settings.value, 'aspectRatio', props.device) || '1:1'
  const ratioMap = {
    '1:1': '100%',
    '4:3': '75%',
    '16:9': '56.25%',
    'auto': 'auto'
  }
  // If we want to support dynamic ratio via paddingTop hack:
  // return { paddingTop: ratioMap[ratio] || '100%' }
  // But the class 'aspect-square' is already there for 1:1. 
  // Let's make it more robust in the next step if needed.
  return {}
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', props.device))
</script>

<style scoped>
.gallery-block { width: 100%; box-sizing: border-box; }
.gallery-placeholder { display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.05); border-radius: 8px; aspect-ratio: 1; }
.gallery-item { transition: all 0.3s ease; }
</style>
