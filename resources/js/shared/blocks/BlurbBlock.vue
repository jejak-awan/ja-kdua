<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="blurb-block transition-all duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Feature Blurb'"
    :style="cardStyles"
  >
    <div class="blurb-container flex flex-col p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-slate-800 hover:border-primary/40 hover:bg-primary/5 transition-all duration-500" :style="containerStyles">
        <!-- Icon -->
        <div 
            v-if="settings.icon"
            class="blurb-icon-wrapper w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center mb-8 group-hover:bg-primary transition-colors duration-500 shadow-sm group-hover:shadow-primary/40"
        >
             <LucideIcon :name="(settings.icon as string)" class="w-8 h-8 opacity-40 group-hover:opacity-100 group-hover:text-white transition-all transform group-hover:scale-110" />
        </div>

        <!-- Title -->
        <h3 
            class="blurb-title font-black text-2xl tracking-tight mb-4 group-hover:text-primary transition-colors"
            :style="titleStyles"
        >
            {{ settings.title || 'Innovative Solution' }}
        </h3>

        <!-- Content -->
        <p 
            class="blurb-text text-slate-500 dark:text-slate-400 font-medium leading-relaxed"
            :style="contentStyles"
        >
            {{ settings.content || 'Experience a new level of efficiency with our state-of-the-art framework built for modern developers.' }}
        </p>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { LucideIcon } from '@/components/ui'
import { 
    getVal, 
    getLayoutStyles,
    getTypographyStyles
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device) as CSSProperties)
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device) as CSSProperties)

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.blurb-block { width: 100%; position: relative; }
.blurb-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
