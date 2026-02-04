<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-header-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Fullwidth Header'"
    :style="cardStyles"
  >
    <div class="header-content relative w-full overflow-hidden flex flex-col items-center justify-center text-center px-6" :style="containerStyles">
      <!-- Background Asset -->
      <div v-if="settings.backgroundImage" class="absolute inset-0 z-0">
          <img :src="(settings.backgroundImage as string)" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" alt="Background" />
          <div v-if="settings.overlayEnabled" class="absolute inset-0 bg-black/40" :style="{ backgroundColor: (settings.overlayColor as string) || 'rgba(0,0,0,0.4)' }"></div>
      </div>
      
      <!-- Content Wrapper -->
      <div class="relative z-10 max-w-4xl space-y-6 animate-in fade-in slide-in-from-bottom-8 duration-700">
          <Badge v-if="settings.badge" variant="secondary" class="bg-primary/20 text-primary border-primary/20 px-4 py-1 rounded-full uppercase tracking-widest text-[10px] font-black">
              {{ settings.badge }}
          </Badge>
          
          <h1 class="text-5xl md:text-8xl font-black text-white leading-[0.9] tracking-tighter" :style="titleStyles">
              {{ settings.title || 'Premium Design Experiences' }}
          </h1>
          
          <p v-if="settings.description" class="text-lg md:text-2xl text-white/80 font-medium max-w-2xl mx-auto leading-relaxed" :style="descriptionStyles">
              {{ settings.description }}
          </p>
          
          <div v-if="settings.showActions !== false" class="flex flex-wrap justify-center gap-4 pt-4">
              <Button size="lg" class="rounded-full px-10 font-black h-14 text-lg shadow-2xl hover:scale-110 active:scale-95 transition-all">
                  {{ settings.primaryActionText || 'Get Started' }}
              </Button>
              <Button size="lg" variant="outline" class="rounded-full px-10 font-black h-14 text-lg border-white/20 text-white hover:bg-white hover:text-black transition-all">
                  {{ settings.secondaryActionText || 'Learn More' }}
              </Button>
          </div>
      </div>

      <!-- Scroll Indicator -->
      <div v-if="settings.showScrollIndicator !== false" class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce opacity-40">
          <ChevronDown class="w-8 h-8 text-white" />
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Badge, Button } from '../ui'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layout = getLayoutStyles(settings.value, props.device)
    const height = getVal<number>(settings.value, 'height', props.device) || 800
    return { 
        ...layout,
        width: '100%', 
        height: `${height}px`,
        backgroundColor: (settings.value.backgroundColor as string) || '#0f172a'
    } as CSSProperties
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device) as CSSProperties)
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', props.device) as CSSProperties)
</script>

<style scoped>
.fullwidth-header-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-header-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
