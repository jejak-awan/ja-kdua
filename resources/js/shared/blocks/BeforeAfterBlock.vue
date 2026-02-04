<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="before-after-block py-20"
  >
    <div class="before-after-container container mx-auto px-6 max-w-5xl" :style="containerStyles">
        <div class="relative group rounded-[3rem] overflow-hidden shadow-2xl border-4 border-white dark:border-slate-900 aspect-video">
            <!-- Labels -->
            <div class="absolute top-8 left-8 z-20 px-6 py-2 rounded-full bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl shadow-xl font-black text-xs uppercase tracking-widest text-slate-900 dark:text-white">Before</div>
            <div class="absolute top-8 right-8 z-20 px-6 py-2 rounded-full bg-primary shadow-xl font-black text-xs uppercase tracking-widest text-white">After</div>

            <!-- Images -->
            <div class="absolute inset-0">
                <img :src="(settings.afterImage as string) || 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&q=80&w=2000'" class="w-full h-full object-cover" alt="After" />
            </div>
            
            <div 
                class="absolute inset-0 overflow-hidden" 
                :style="{ width: `${sliderPos}%` }"
            >
                <img :src="(settings.beforeImage as string) || 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000'" class="absolute h-full object-cover left-0" :style="{ width: `${100 * (100/sliderPos)}%` }" alt="Before" />
            </div>

            <!-- Slider Control -->
            <div 
                class="absolute inset-y-0 z-30 group/slider cursor-col-resize"
                :style="{ left: `${sliderPos}%` }"
                @mousedown="startDragging"
                @touchstart="startDragging"
            >
                <div class="absolute inset-y-0 -left-px w-0.5 bg-white/50 backdrop-blur-sm" />
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 rounded-2xl bg-white shadow-2xl flex items-center justify-center transition-transform group-hover/slider:scale-110 active:scale-90">
                    <MoveHorizontal class="w-6 h-6 text-primary" />
                </div>
            </div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import MoveHorizontal from 'lucide-vue-next/dist/esm/icons/move-horizontal.js';
import { 
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

const sliderPos = ref(50)
const isDragging = ref(false)

const startDragging = () => {
    isDragging.value = true
}

const stopDragging = () => {
    isDragging.value = false
}

const onMove = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return
    
    const container = document.querySelector('.before-after-container')
    if (!container) return
    
    const rect = container.getBoundingClientRect()
    const x = 'touches' in e ? e.touches[0].clientX : e.clientX
    const position = ((x - rect.left) / rect.width) * 100
    
    sliderPos.value = Math.max(0, Math.min(100, position))
}

onMounted(() => {
    window.addEventListener('mousemove', onMove)
    window.addEventListener('mouseup', stopDragging)
    window.addEventListener('touchmove', onMove)
    window.addEventListener('touchend', stopDragging)
})

onUnmounted(() => {
    window.removeEventListener('mousemove', onMove)
    window.removeEventListener('mouseup', stopDragging)
    window.removeEventListener('touchmove', onMove)
    window.removeEventListener('touchend', stopDragging)
})

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.before-after-block { width: 100%; position: relative; }
</style>
