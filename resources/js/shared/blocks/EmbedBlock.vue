<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="embed-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Third-Party Embed'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div v-if="hasContent" class="embed-container bg-black rounded-2xl overflow-hidden shadow-2xl transition-all hover:shadow-blue-500/10" :style="containerStyles">
        <iframe 
          v-if="blockSettings.embedType === 'url' && blockSettings.embedUrl" 
          :src="blockSettings.embedUrl" 
          frameborder="0" 
          allowfullscreen 
          class="embed-iframe absolute inset-0 w-full h-full" 
        />
        <div v-else-if="blockSettings.embedCode" class="embed-code absolute inset-0 w-full h-full" v-html="blockSettings.embedCode" />
      </div>
      <div v-else class="embed-placeholder min-h-[300px] flex flex-col items-center justify-center gap-4 p-12 bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400">
        <div class="icon-wrap bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm">
          <Code2 class="w-10 h-10 opacity-50" />
        </div>
        <span class="font-bold">Third-Party Embed</span>
        <p class="text-xs opacity-60 max-w-xs text-center">Add an IFrame URL or custom HTML code to display external content</p>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Code2 } from 'lucide-vue-next'
import { 
    getVal,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const hasContent = computed(() => (settings.value.embedType === 'url' && settings.value.embedUrl) || settings.value.embedCode)

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const ratio = getVal(settings.value, 'aspectRatio', props.device) || '16:9'
    const aspectRatios: Record<string, string> = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }

    if (ratio === 'auto') {
        const height = getResponsiveValue(settings.value, 'height', props.device) || 450
        return { 
            ...layoutStyles,
            height: typeof height === 'number' ? `${height}px` : height,
            position: 'relative'
        } as any
    }
    
    return { 
        ...layoutStyles,
        paddingTop: aspectRatios[ratio] || '56.25%',
        position: 'relative'
    } as any
})
</script>

<style scoped>
.embed-block { width: 100%; }
.embed-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.embed-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
