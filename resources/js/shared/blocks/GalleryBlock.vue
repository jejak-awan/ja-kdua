<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="gallery-block w-full">
        <div class="gallery-grid" :style="gridStyles">
          <Card 
            v-for="(image, index) in galleryImages" 
            :key="index"
            class="gallery-item group relative aspect-square overflow-hidden rounded-[32px] bg-slate-100 dark:bg-slate-900 border-none shadow-lg cursor-pointer transition-all duration-700 hover:shadow-2xl hover:-translate-y-2"
          >
            <div class="gallery-image-wrapper w-full h-full relative">
                <img 
                    v-if="image.url"
                    :src="image.url" 
                    :alt="image.alt || 'Gallery Image'"
                    class="gallery-image w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                >
                 <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                    <ImageIcon :size="48" class="opacity-30" />
                 </div>

                <!-- Overlay -->
                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-primary/20 opacity-0 group-hover:opacity-100 transition-all duration-500 backdrop-blur-sm">
                    <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-500">
                        <Plus class="w-6 h-6" />
                    </div>
                </div>

                <CardContent v-if="showCaptions && image.caption && captionPosition === 'overlay'" class="absolute inset-x-0 bottom-0 z-20 p-8 pt-12 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0 text-white pointer-events-none">
                    <p class="text-sm font-bold tracking-tight line-clamp-2" :style="captionStyles">{{ image.caption }}</p>
                </CardContent>
            </div>
            <p v-if="showCaptions && image.caption && captionPosition === 'below'" class="mt-4 text-center text-xs font-bold text-slate-500 px-2" :style="captionStyles">{{ image.caption }}</p>
          </Card>
          
          <!-- Placeholder when empty (Mode Edit Only) -->
          <template v-if="galleryImages.length === 0 && mode === 'edit'">
            <Card v-for="i in 3" :key="i" class="gallery-item gallery-placeholder aspect-square rounded-[32px] border-2 border-dashed border-slate-200 bg-transparent flex items-center justify-center p-8 text-center text-slate-300">
              <div class="flex flex-col items-center gap-3">
                <ImageIcon :size="32" class="opacity-30" />
                <span class="text-[10px] font-black uppercase tracking-widest">Empty Slot</span>
              </div>
            </Card>
          </template>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Image as ImageIcon, Plus } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')

const settings = computed(() => props.module?.settings || {})

const galleryImages = computed(() => {
  const imgs = getVal(settings.value, 'images', currentDevice.value)
  return Array.isArray(imgs) ? imgs : []
})

const showCaptions = computed(() => getVal(settings.value, 'showCaptions', currentDevice.value))
const captionPosition = computed(() => getVal(settings.value, 'captionPosition', currentDevice.value) || 'overlay')

const gridStyles = computed(() => {
  const cols = getVal(settings.value, 'columns', currentDevice.value) || 3
  const gap = getVal(settings.value, 'gap', currentDevice.value) || 24
  return {
    display: 'grid',
    gridTemplateColumns: `repeat(${cols}, 1fr)`,
    gap: typeof gap === 'number' ? `${gap}px` : gap
  }
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', currentDevice.value))
</script>

<style scoped>
.gallery-block { width: 100%; }
</style>
