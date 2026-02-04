<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="circle-counter-block transition-all duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Circle Counter'"
    :style="cardStyles"
  >
    <div class="circle-container flex flex-col items-center justify-center p-8 gap-6" :style="containerStyles">
        <div class="relative w-48 h-48 group">
            <!-- Background Ring -->
            <svg class="w-full h-full -rotate-90">
                <circle
                    cx="96"
                    cy="96"
                    r="84"
                    stroke="currentColor"
                    stroke-width="12"
                    fill="transparent"
                    class="text-slate-100 dark:text-slate-800"
                />
                <!-- Progress Ring -->
                <circle
                    cx="96"
                    cy="96"
                    r="84"
                    stroke="currentColor"
                    stroke-width="12"
                    fill="transparent"
                    :stroke-dasharray="dashArray"
                    :stroke-dashoffset="dashOffset"
                    stroke-linecap="round"
                    class="text-primary transition-all duration-1000 ease-out"
                />
            </svg>
            
            <!-- Value Center -->
            <div class="absolute inset-0 flex flex-col items-center justify-center animate-in zoom-in duration-700">
                <span class="text-5xl font-black tracking-tighter">{{ settings.prefix }}{{ animatedValue }}{{ settings.suffix }}</span>
                <span v-if="settings.label" class="text-[10px] font-black uppercase tracking-widest opacity-40 mt-1">{{ settings.label }}</span>
            </div>
            
            <!-- Glow Effect -->
            <div class="absolute inset-0 rounded-full border-4 border-primary/20 scale-110 opacity-0 group-hover:opacity-100 group-hover:scale-125 transition-all duration-1000"></div>
        </div>

        <p v-if="settings.description" class="text-sm font-medium text-slate-500 dark:text-slate-400 text-center max-w-[200px] leading-relaxed">
            {{ settings.description }}
        </p>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
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

const animatedValue = ref(0)
const targetValue = computed(() => (settings.value.value as number) || 0)
const dashArray = 2 * Math.PI * 84
const dashOffset = computed(() => dashArray * (1 - animatedValue.value / 100))

onMounted(() => {
    setTimeout(() => {
        animatedValue.value = targetValue.value
    }, 500)
})

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        width: '100%'
    } as CSSProperties
})
</script>

<style scoped>
.circle-counter-block { width: 100%; position: relative; }
.circle-counter-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
