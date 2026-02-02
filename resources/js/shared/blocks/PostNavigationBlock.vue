<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="post-nav-block transition-[width] duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Post Navigation'"
    :style="cardStyles"
  >
    <div class="post-nav-container w-full" :style="containerStyles">
        <div class="flex flex-col md:flex-row items-stretch justify-between gap-8 w-full">
            <!-- Previous Post -->
            <a 
                v-if="prevPost || mode === 'edit'" 
                :href="mode === 'view' ? (prevPost?.url || '#') : '#'" 
                class="post-nav-item post-nav-item--prev group flex-1 flex items-center gap-8 p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-50 dark:border-slate-800 transition-colors duration-700 hover:-translate-x-2 hover:shadow-primary/10" 
                :style="itemStyles"
                @click="handleLinkClick"
            >
              <div class="w-16 h-16 rounded-3xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center transition-[width] duration-500 group-hover:bg-primary group-hover:text-white group-hover:scale-110 shadow-xl">
                  <LucideIcon name="ChevronLeft" class="w-7 h-7" :style="iconStyles" />
              </div>
              <div class="post-nav-content flex flex-col gap-2">
                <span 
                  v-if="settings.showLabels !== false" 
                  class="post-nav-label text-[10px] font-black uppercase tracking-[0.3em] text-primary/60 outline-none" 
                  :style="labelStyles"
                  :contenteditable="mode === 'edit'"
                  @blur="(e: any) => updateField('prevLabel', (e.target as HTMLElement).innerText)"
                >{{ settings.prevLabel || 'Back' }}</span>
                <span class="post-nav-title text-2xl font-black leading-tight tracking-tighter text-slate-900 dark:text-white" :style="titleStyles">
                    {{ prevPost?.title || (mode === 'edit' ? 'The Art of Digital Influence' : '') }}
                </span>
              </div>
            </a>
            <div v-else class="flex-1"></div>

            <!-- Next Post -->
            <a 
                v-if="nextPost || mode === 'edit'" 
                :href="mode === 'view' ? (nextPost?.url || '#') : '#'" 
                class="post-nav-item post-nav-item--next group flex-1 flex flex-row-reverse items-center text-right gap-8 p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-50 dark:border-slate-800 transition-colors duration-700 hover:translate-x-2 hover:shadow-primary/10" 
                :style="itemStyles"
                @click="handleLinkClick"
            >
              <div class="w-16 h-16 rounded-3xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center transition-[width] duration-500 group-hover:bg-primary group-hover:text-white group-hover:scale-110 shadow-xl">
                  <LucideIcon name="ChevronRight" class="w-7 h-7" :style="iconStyles" />
              </div>
              <div class="post-nav-content flex flex-col gap-2">
                <span 
                  v-if="settings.showLabels !== false" 
                  class="post-nav-label text-[10px] font-black uppercase tracking-[0.3em] text-primary/60 outline-none" 
                  :style="labelStyles"
                  :contenteditable="mode === 'edit'"
                  @blur="(e: any) => updateField('nextLabel', (e.target as HTMLElement).innerText)"
                >{{ settings.nextLabel || 'Up Next' }}</span>
                <span class="post-nav-title text-2xl font-black leading-tight tracking-tighter text-slate-900 dark:text-white" :style="titleStyles">
                    {{ nextPost?.title || (mode === 'edit' ? 'Mastering Advanced Layouts' : '') }}
                </span>
              </div>
            </a>
            <div v-else class="flex-1"></div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { LucideIcon } from '@/components/ui';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
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

// Mock dynamic data
const prevPost = inject<any>('prevPost', { title: 'The Art of Digital Influence', url: '#' })
const nextPost = inject<any>('nextPost', { title: 'Mastering Advanced Layouts', url: '#' })

const updateField = (key: string, value: string) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const itemStyles = computed(() => ({ 
    textDecoration: 'none', 
    color: 'inherit'
}))

const iconStyles = computed(() => ({ 
    color: getVal(settings.value, 'label_color', props.device) || 'currentColor'
}))

const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', props.device))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
</script>

<style scoped>
.post-nav-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.post-nav-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.post-nav-label { outline: none; }
</style>
