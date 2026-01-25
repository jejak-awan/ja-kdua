<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-image-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Image'"
    :style="cardStyles"
  >
    <div class="image-wrapper relative w-full overflow-hidden" :style="containerStyles">
      <a 
          v-if="settings.link" 
          :href="mode === 'view' ? settings.link : '#'" 
          :target="settings.target" 
          class="block w-full h-full"
          @click="handleLinkClick"
      >
          <img 
            :src="settings.image || 'https://picsum.photos/1920/1080?random=image'" 
            :alt="settings.alt || 'Fullwidth image'"
            class="w-full h-full transition-transform duration-1000 group-hover:scale-110"
            :style="imageStyles"
          />
      </a>
      <img 
        v-else 
        :src="settings.image || 'https://picsum.photos/1920/1080?random=image'" 
        :alt="settings.alt || 'Fullwidth image'"
        class="w-full h-full transition-transform duration-1000 group-hover:scale-110"
        :style="imageStyles"
      />
      
      <!-- Overlay Content -->
      <div 
        v-if="settings.showOverlay" 
        class="absolute inset-0 z-10 flex flex-col items-center justify-center p-12 transition-opacity duration-700 opacity-0 group-hover:opacity-100" 
        :style="overlayStyles"
      >
        <div 
          v-if="settings.overlayText || mode === 'edit'" 
          class="overlay-text max-w-4xl text-center transform translate-y-8 group-hover:translate-y-0 transition-transform duration-700" 
          :style="overlayTextStyles"
          :contenteditable="mode === 'edit'"
          @blur="(e: any) => updateField('overlayText', (e.target as HTMLElement).innerText)"
        >{{ settings.overlayText || (mode === 'edit' ? 'Premium Aesthetic Design' : '') }}</div>
      </div>
      
      <!-- Caption -->
      <p 
        v-if="settings.caption || mode === 'edit'" 
        class="absolute bottom-10 left-10 z-20 bg-black/40 backdrop-blur-md px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-white/90 transform translate-x--4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-500"
        :style="captionStyles"
        :contenteditable="mode === 'edit'"
        @blur="(e: any) => updateField('caption', (e.target as HTMLElement).innerText)"
      >{{ settings.caption || (mode === 'edit' ? 'Project Insight' : '') }}</p>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
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

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const updateField = (key: string, value: string) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [key]: value })
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
    const layout = getLayoutStyles(settings.value, props.device)
    const height = getResponsiveValue(settings.value, 'height', props.device) || 500
    return { 
        ...layout,
        width: '100%', 
        height: `${height}px` 
    }
})

const imageStyles = computed(() => ({ 
    objectFit: settings.value.objectFit || 'cover' 
}))

const overlayStyles = computed(() => ({ 
    backgroundColor: settings.value.overlayColor || 'rgba(0,0,0,0.4)' 
}))

const overlayTextStyles = computed(() => {
    const typo = getTypographyStyles(settings.value, 'overlay_', props.device)
    return { ...typo, color: settings.value.overlayTextColor || '#ffffff' }
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', props.device))
</script>

<style scoped>
.fullwidth-image-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-image-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.overlay-text { outline: none; }
</style>
