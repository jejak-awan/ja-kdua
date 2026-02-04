<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="gallery-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Gallery'"
    :style="(cardStyles as any)"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="gallery-grid w-full" 
        :style="(containerStyles as any)"
      >
        <Card 
          v-for="(image, index) in galleryImages" 
          :key="index"
          class="gallery-item group relative aspect-square overflow-hidden rounded-[32px] bg-slate-100 dark:bg-slate-900 border-none shadow-lg cursor-pointer transition-colors duration-700 hover:shadow-2xl hover:-translate-y-2"
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
              <div 
                v-if="hoverEffect !== 'none'"
                class="absolute inset-0 z-10 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-[width] duration-500 backdrop-blur-sm"
                :style="{ backgroundColor: (blockSettings.overlayColor as string) || 'rgba(0,0,0,0.2)' }"
              >
                  <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center transform scale-90 group-hover:scale-100 transition-transform duration-500">
                      <Plus class="w-6 h-6" />
                  </div>
              </div>

              <CardContent v-if="showCaptions && image.caption && captionPosition === 'overlay'" class="absolute inset-x-0 bottom-0 z-20 p-8 pt-12 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-[width] duration-500 translate-y-4 group-hover:translate-y-0 text-white pointer-events-none">
                  <p class="text-sm font-bold tracking-tight line-clamp-2" :style="(captionStyles as any)">{{ image.caption }}</p>
              </CardContent>
          </div>
          <p v-if="showCaptions && image.caption && captionPosition === 'below'" class="mt-4 text-center text-xs font-bold text-slate-500 px-2" :style="(captionStyles as any)">{{ image.caption }}</p>
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
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent } from '../ui'
import { 
    getVal, 
    getTypographyStyles,
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

const galleryImages = computed(() => {
  const imgs = getVal<Array<{ url: string; alt?: string; caption?: string }>>(settings.value, 'images', props.device)
  return Array.isArray(imgs) ? imgs : []
})

const showCaptions = computed(() => getVal<boolean>(settings.value, 'showCaptions', props.device))
const captionPosition = computed(() => getVal<string>(settings.value, 'captionPosition', props.device) || 'overlay')
const hoverEffect = computed(() => getVal<string>(settings.value, 'hoverEffect', props.device) || 'zoom')

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
    const cols = getVal<number>(settings.value, 'columns', props.device) || 3
    const gap = getVal<string | number>(settings.value, 'gap', props.device) || 24
    
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: typeof gap === 'number' ? `${gap}px` : gap
    }
    return styles
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', props.device))
</script>

<style scoped>
.gallery-block { width: 100%; }
.gallery-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.gallery-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
