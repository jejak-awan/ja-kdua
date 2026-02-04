<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-portfolio-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Portfolio'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings, device: blockDevice }">
      <div 
        class="portfolio-container relative w-full" 
        :style="containerStyles"
      >
        <div 
          class="grid w-full h-full gap-8" 
          :class="[
            getVal(blockSettings, 'columns', blockDevice) === 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' :
            getVal(blockSettings, 'columns', blockDevice) === 3 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' :
            getVal(blockSettings, 'columns', blockDevice) === 2 ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1'
          ]"
        >
          <div 
            v-for="(item, index) in portfolioItems" 
            :key="index"
            class="portfolio-item relative overflow-hidden rounded-3xl group/item aspect-[4/5] bg-slate-900 shadow-xl transition-all duration-500 hover:-translate-y-3"
          >
            <!-- Image -->
            <img 
              v-if="item.image"
              :src="item.image" 
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/item:scale-110"
              :alt="item.title || 'Portfolio Work'"
            />
            
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-0 group-hover/item:opacity-100 transition-opacity duration-500 z-10"></div>
            
            <!-- Content -->
            <div class="absolute inset-0 p-8 flex flex-col justify-end text-white z-20 translate-y-8 group-hover/item:translate-y-0 opacity-0 group-hover/item:opacity-100 transition-all duration-500">
               <Badge v-if="item.category" variant="secondary" class="w-fit mb-4 bg-primary text-white border-none py-1 px-4 text-[10px] font-black uppercase tracking-widest">
                  {{ item.category }}
               </Badge>
               <h3 class="text-2xl md:text-3xl font-black mb-3 leading-tight tracking-tighter">{{ item.title }}</h3>
               <p v-if="item.description" class="text-white/70 text-sm font-medium line-clamp-2 mb-6">{{ item.description }}</p>
               
               <a 
                v-if="item.url"
                :href="mode === 'view' ? item.url : undefined" 
                class="flex items-center gap-2 text-white font-bold hover:text-primary transition-colors text-sm uppercase tracking-widest"
                @click="mode === 'edit' ? $event.preventDefault() : null"
               >
                 View Project
                 <ArrowUpRight class="w-4 h-4" />
               </a>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Badge } from '../ui'
import ArrowUpRight from 'lucide-vue-next/dist/esm/icons/arrow-up-right.js';
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

const portfolioItems = computed(() => (settings.value.items as Record<string, string>[]) || [
  { title: 'Modern Architecture', category: 'Design', image: 'https://picsum.photos/800/1000?random=1' },
  { title: 'Branding Project', category: 'Strategy', image: 'https://picsum.photos/800/1000?random=2' },
  { title: 'UI Design System', category: 'Product', image: 'https://picsum.photos/800/1000?random=3' },
  { title: 'Creative Direction', category: 'Art', image: 'https://picsum.photos/800/1000?random=4' }
])

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
.fullwidth-portfolio-block { width: 100%; position: relative; }
</style>
