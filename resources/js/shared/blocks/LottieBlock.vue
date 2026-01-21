<template>
  <BaseBlock :module="module" :settings="settings" class="lottie-block">
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
                {{ settings.animationUrl || 'No animation URL set' }}
              </div>
            </div>
        </template>
        
        <!-- In view mode, we render the actual player -->
        <template v-else>
            <div 
                class="lottie-view-container w-full h-full"
                :data-lottie-url="settings.animationUrl"
                :data-lottie-loop="settings.loop !== false"
                :data-lottie-autoplay="settings.autoplay !== false"
            >
                <!-- Actual lottie library would be initialized here -->
                <!-- For now, we show a refined placeholder that looks like an animation is loading -->
                <div class="animate-pulse bg-gray-100 dark:bg-gray-800 w-full h-full flex items-center justify-center">
                    <Film class="w-8 h-8 text-gray-300" />
                </div>
            </div>
        </template>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Film } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const wrapperStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  return {
    justifyContent: alignment === 'left' ? 'flex-start' : 
                    alignment === 'right' ? 'flex-end' : 'center'
  }
})

const playerStyles = computed(() => {
  const width = getResponsiveValue(settings.value, 'width', device.value) || 300
  const height = getResponsiveValue(settings.value, 'height', device.value) || 300
  const maxWidth = getResponsiveValue(settings.value, 'maxWidth', device.value) || 100
  return {
    width: typeof width === 'number' ? `${width}px` : width,
    height: typeof height === 'number' ? `${height}px` : height,
    maxWidth: typeof maxWidth === 'number' ? `${maxWidth}%` : maxWidth
  }
})
</script>

<style scoped>
.lottie-block { width: 100%; }
.icon-blob { animation: blobby 4s ease-in-out infinite; }
@keyframes blobby {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
</style>
