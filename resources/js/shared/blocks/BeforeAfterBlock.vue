<template>
  <BaseBlock :module="module" :settings="settings" class="before-after-block">
    <div 
        class="comparison-container relative bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden cursor-ew-resize group shadow-xl" 
        :style="containerStyles" 
        @mousemove="handleMove" 
        @touchmove="handleTouch"
    >
      <!-- Before Image Container (Dynamic Width Layer) -->
      <div class="comparison-before absolute inset-0 z-10 overflow-hidden" :style="beforeStyles">
        <div v-if="!settings.beforeImage" class="placeholder-image w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-200 dark:bg-gray-700">
            <ImageIcon class="w-12 h-12 mb-2 opacity-50" />
            <span class="font-bold">Before</span>
        </div>
        <img v-else :src="settings.beforeImage" class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none" />
        
        <span v-if="settings.showLabels !== false" class="comparison-label absolute bottom-6 left-6 px-4 py-2 bg-black/60 backdrop-blur-md text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow-lg border border-white/10 z-20" :style="labelStyles">
            {{ settings.beforeLabel || 'Before' }}
        </span>
      </div>
      
      <!-- After Image Container (Background Layer) -->
      <div class="comparison-after absolute inset-0 z-0">
        <div v-if="!settings.afterImage" class="placeholder-image w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-200 dark:bg-gray-700">
            <ImageIcon class="w-12 h-12 mb-2 opacity-50" />
            <span class="font-bold">After</span>
        </div>
        <img v-else :src="settings.afterImage" class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none" />
        
        <span v-if="settings.showLabels !== false" class="comparison-label absolute bottom-6 right-6 px-4 py-2 bg-black/60 backdrop-blur-md text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow-lg border border-white/10 z-20" :style="labelStyles">
            {{ settings.afterLabel || 'After' }}
        </span>
      </div>
      
      <!-- Slider Handle -->
      <div 
        class="comparison-slider absolute top-0 bottom-0 z-30 flex flex-col items-center justify-center transition-opacity" 
        :style="sliderStyles"
      >
        <div class="slider-line h-full shadow-2xl" :style="sliderLineStyles"></div>
        <div 
            class="slider-handle w-12 h-12 bg-white flex items-center justify-center rounded-full shadow-2xl border-4 text-gray-900 transition-transform group-hover:scale-110 active:scale-95" 
            :style="handleStyles"
        >
          <GripVertical class="w-5 h-5 pointer-events-none" />
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Image as ImageIcon, GripVertical } from 'lucide-vue-next'
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

const position = ref(getResponsiveValue(settings.value, 'sliderPosition', device.value) || 50)

const handleMove = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  position.value = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100))
}

const handleTouch = (e) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const touch = e.touches[0]
  position.value = Math.max(0, Math.min(100, ((touch.clientX - rect.left) / rect.width) * 100))
}

const containerStyles = computed(() => {
  const height = getResponsiveValue(settings.value, 'height', device.value) || 450
  return { 
    height: typeof height === 'number' ? `${height}px` : height
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
  const sliderWidth = getResponsiveValue(settings.value, 'sliderWidth', device.value) || 4
  return { 
    width: `${sliderWidth}px`, 
    backgroundColor: settings.value.sliderColor || '#ffffff'
  }
})

const handleStyles = computed(() => ({ 
  borderColor: settings.value.sliderColor || '#ffffff'
}))

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))
</script>

<style scoped>
.before-after-block { width: 100%; }
.comparison-container { user-select: none; }
</style>
