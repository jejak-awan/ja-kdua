<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="lottie-block transition-colors duration-300"
    :style="cardStyles"
  >
    <div class="lottie-wrapper flex w-full" :style="wrapperStyles">
      <div class="lottie-player overflow-hidden rounded-xl" :style="playerStyles">
        <!-- Lottie animation player -->
        <!-- In builder/edit mode, we show a beautiful placeholder -->
        <template v-if="mode === 'edit'">
            <div class="lottie-placeholder w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-purple-400 to-pink-500 text-white gap-3 p-6 text-center">
              <div class="icon-blob bg-white/20 p-4 rounded-full backdrop-blur-sm">
                <Film class="w-10 h-10 opacity-90" />
              </div>
              <span class="font-bold text-lg tracking-tight">Lottie Animation</span>
              <div class="url-badge bg-black/10 px-3 py-1 rounded-full text-[10px] font-mono max-w-full overflow-hidden text-ellipsis whitespace-nowrap">
                {{ (settings.animationUrl as string) || (settings.animationJson as string) || 'No source set' }}
              </div>
            </div>
        </template>
        
        <!-- In view mode, we render the actual player -->
        <template v-else>
            <div 
                class="lottie-view-container w-full h-full"
                :data-lottie-url="(settings.animationUrl as string)"
                :data-lottie-loop="settings.loop !== false"
                :data-lottie-autoplay="settings.autoplay !== false"
            >
                <!-- Actual lottie library would be initialized here -->
                <div class="animate-pulse bg-gray-100 dark:bg-gray-800 w-full h-full flex items-center justify-center">
                    <Film class="w-8 h-8 text-gray-300" />
                </div>
            </div>
        </template>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Film from 'lucide-vue-next/dist/esm/icons/film.js';
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

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const wrapperStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const alignment = getVal<string>(settings.value, 'alignment', props.device) || 'center'
    
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        justifyContent: alignment === 'left' ? 'flex-start' : 
                        (alignment === 'right' ? 'flex-end' : 'center')
    }
    return styles
})

const playerStyles = computed(() => {
  const width = getVal<string | number>(settings.value, 'width', props.device) || 300
  const height = getVal<string | number>(settings.value, 'height', props.device) || 300
  const maxWidthValue = getVal<string | number>(settings.value, 'maxWidth', props.device) || 100
  return {
    width: typeof width === 'number' ? `${width}px` : width,
    height: typeof height === 'number' ? `${height}px` : height,
    maxWidth: typeof maxWidthValue === 'number' ? `${maxWidthValue}%` : maxWidthValue
  }
})
</script>

<style scoped>
.lottie-block { width: 100%; }
.lottie-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.lottie-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.icon-blob { animation: blobby 4s ease-in-out infinite; }
@keyframes blobby {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
</style>
