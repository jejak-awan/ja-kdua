<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="portfolio-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Portfolio'"
    :style="(cardStyles as any)"
  >
    <div class="portfolio-wrapper w-full" :style="(containerStyles as any)">
      <h2 
        v-if="settings.title || mode === 'edit'" 
        class="portfolio-main-title text-center mb-16 tracking-tighter outline-none" 
        :style="(mainTitleStyles as any)"
        :contenteditable="mode === 'edit'"
        @blur="(e: FocusEvent) => updateField('title', (e.target as HTMLElement).innerText)"
        v-text="settings.title || 'Selected Works'"
      ></h2>

      <!-- Filter -->
      <div v-if="settings.showFilter !== false" class="portfolio-filter flex justify-center gap-4 mb-16 flex-wrap">
        <Button 
          v-for="cat in categories" 
          :key="cat" 
          :variant="activeFilter === cat ? 'default' : 'outline'"
          class="rounded-full px-8 py-6 font-black uppercase tracking-widest text-[10px] transition-[width] duration-500 shadow-xl hover:shadow-2xl"
          :style="activeFilter === cat ? {} : (filterTypographyStyles as any)"
          @click="activeFilter = cat"
        >
          {{ cat }}
        </Button>
      </div>
      
      <!-- Grid -->
      <div class="portfolio-grid transition-[width] duration-500" :style="(gridStyles as any)">
        <Card 
          v-for="item in mockItems" 
          :key="item.id" 
          class="portfolio-item group relative overflow-hidden bg-slate-50 dark:bg-slate-900 rounded-[3.5rem] border-none aspect-square cursor-pointer transition-colors duration-700 shadow-2xl hover:-translate-y-4"
        >
          <!-- Image Area -->
          <div class="absolute inset-0 z-0 bg-slate-100 dark:bg-slate-950 flex items-center justify-center transition-transform duration-1000 group-hover:scale-110">
             <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10" />
             <LucideIcon name="Layers" class="w-24 h-24 text-slate-200 dark:text-slate-800 opacity-30" />
          </div>

          <!-- Overlay -->
          <div 
              class="item-overlay absolute inset-0 z-20 flex flex-col items-center justify-center p-12 opacity-0 group-hover:opacity-100 transition-colors duration-700 backdrop-blur-xl bg-primary/80"
              :style="(overlayStyles as any)"
          >
            <Badge 
              v-if="settings.showCategory !== false" 
              variant="secondary"
              class="mb-6 rounded-2xl font-black px-6 py-2 text-[10px] uppercase tracking-[0.3em] bg-white/10 text-white border-white/20 backdrop-blur-md shadow-2xl transform -translate-y-4 group-hover:translate-y-0 transition-transform duration-500"
            >
              {{ item.category }}
            </Badge>
            <h4 
              v-if="settings.showTitle !== false" 
              class="item-title text-white text-3xl font-black text-center tracking-tighter leading-none max-w-xs transform -translate-y-2 group-hover:translate-y-0 transition-transform duration-500 delay-75"
              :style="(itemTitleStyles as any)"
            >
              {{ item.title }}
            </h4>
            
            <div class="mt-10 w-16 h-16 rounded-3xl bg-white text-primary flex items-center justify-center transform translate-y-8 group-hover:translate-y-0 transition-colors duration-700 delay-150 shadow-2xl hover:scale-110">
               <LucideIcon name="ArrowUpRight" class="w-7 h-7" />
            </div>
          </div>
        </Card>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, Button, Badge } from '../ui'
import { LucideIcon } from '@/components/ui';
import { 
  getVal, 
  getLayoutStyles, 
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const categories = ['All', 'UX/UI', 'Development', 'Branding']
const activeFilter = ref('All')

const mockItems = computed(() => {
    const count = getVal<number>(settings.value, 'itemsPerPage', props.device) || 9
    return Array.from({ length: count }, (_, i) => ({
        id: i + 1, title: `Creative Nexus ${i + 1}`, category: categories[1 + (i % 3)]
    }))
})

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const gridStyles = computed(() => {
    const cols = getVal<string | number>(settings.value, 'columns', props.device) || 3
    const gap = getVal<string | number>(settings.value, 'gap', props.device) || 24
    const styles: Record<string, string | number> = { 
        display: 'grid', 
        gridTemplateColumns: props.device === 'mobile' ? '1fr' : (props.device === 'tablet' ? 'repeat(2, 1fr)' : `repeat(${cols}, 1fr)`), 
        gap: `${gap}px`,
        width: '100%'
    }
    return styles
})

const overlayStyles = computed(() => ({ 
    backgroundColor: getVal<string>(settings.value, 'overlayColor', props.device) || '', 
}))

const filterTypographyStyles = computed(() => getTypographyStyles(settings.value, 'filter_', props.device))
const mainTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'item_title_', props.device))

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.portfolio-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.portfolio-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.portfolio-item:hover { z-index: 30; }
</style>
