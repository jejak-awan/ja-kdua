<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="before-after-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Before After Comparison'"
    :style="cardStyles"
  >
    <div 
        class="comparison-container relative bg-slate-100 dark:bg-slate-800 rounded-[2rem] overflow-hidden cursor-ew-resize group shadow-2xl transition-colors duration-700" 
        :style="containerStyles" 
        @mousemove="handleMove" 
        @touchmove="handleTouch"
    >
      <!-- Before Image Container (Dynamic Width Layer) -->
      <div class="comparison-before absolute inset-0 z-10 overflow-hidden transition-colors duration-100 ease-out" :style="beforeStyles">
        <div v-if="!settings.beforeImage" class="placeholder-image w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-200 dark:bg-slate-700">
            <ImageIcon class="w-12 h-12 mb-4 opacity-50 animate-pulse" />
            <span class="text-[10px] font-black uppercase tracking-widest">Premium Before</span>
        </div>
        <img v-else :src="settings.beforeImage" class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none transition-transform duration-[2000ms] group-hover:scale-105" />
        
        <span v-if="settings.showLabels !== false" class="comparison-label absolute bottom-10 left-10 px-6 py-3 bg-black/40 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-2xl border border-white/10 z-20 animate-in fade-in slide-in-from-left-4 duration-700" :style="labelStyles">
            {{ settings.beforeLabel || 'Original' }}
        </span>
      </div>
      
      <!-- After Image Container (Background Layer) -->
      <div class="comparison-after absolute inset-0 z-0">
        <div v-if="!settings.afterImage" class="placeholder-image w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-200 dark:bg-slate-700">
            <ImageIcon class="w-12 h-12 mb-4 opacity-50 animate-pulse" />
            <span class="text-[10px] font-black uppercase tracking-widest">Premium After</span>
        </div>
        <img v-else :src="settings.afterImage" class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none transition-transform duration-[2000ms] group-hover:scale-105" />
        
        <span v-if="settings.showLabels !== false" class="comparison-label absolute bottom-10 right-10 px-6 py-3 bg-black/40 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl shadow-2xl border border-white/10 z-20 animate-in fade-in slide-in-from-right-4 duration-700" :style="labelStyles">
            {{ settings.afterLabel || 'Refined' }}
        </span>
      </div>
      
      <!-- Slider Handle -->
      <div 
        class="comparison-slider absolute top-0 bottom-0 z-30 flex flex-col items-center justify-center transition-colors duration-100 ease-out" 
        :style="sliderStyles"
      >
        <div class="slider-line h-full shadow-2xl transition-[width] duration-500" :style="sliderLineStyles"></div>
        <div 
            class="slider-handle w-14 h-14 bg-white/10 backdrop-blur-xl flex items-center justify-center rounded-full shadow-2xl border-2 border-white/20 text-white transition-[width] duration-500 hover:scale-125 active:scale-90 hover:bg-white hover:text-slate-900 group" 
            :style="handleStyles"
        >
          <GripVertical class="w-6 h-6 pointer-events-none transition-transform duration-500 group-hover:rotate-12" />
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import GripVertical from 'lucide-vue-next/dist/esm/icons/grip-vertical.js';import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const position = ref(getResponsiveValue(settings.value, 'sliderPosition', props.device) || 50)

const handleMove = (e: MouseEvent) => {
  const rect = (e.currentTarget as HTMLElement).getBoundingClientRect()
  position.value = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100))
}

const handleTouch = (e: TouchEvent) => {
  const rect = (e.currentTarget as HTMLElement).getBoundingClientRect()
  const touch = e.touches[0]
  position.value = Math.max(0, Math.min(100, ((touch.clientX - rect.left) / rect.width) * 100))
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
    const height = getResponsiveValue(settings.value, 'height', props.device) || 450
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        height: typeof height === 'number' ? `${height}px` : height,
        width: '100%'
    }
})

const beforeStyles = computed(() => ({ 
  width: `${position.value}%`
}))

const sliderStyles = computed(() => ({ 
  left: `${position.value}%`, 
  transform: 'translateX(-50%)'
}))

const sliderLineStyles = computed(() => {
  const sliderWidth = getResponsiveValue(settings.value, 'sliderWidth', props.device) || 4
  return { 
    width: `${sliderWidth}px`, 
    backgroundColor: settings.value.sliderColor || '#ffffff'
  }
})

const handleStyles = computed(() => ({ 
  borderColor: settings.value.sliderColor || 'rgba(255,255,255,0.2)'
}))

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', props.device))
</script>

<style scoped>
.before-after-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.before-after-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.comparison-container { user-select: none; }
</style>
