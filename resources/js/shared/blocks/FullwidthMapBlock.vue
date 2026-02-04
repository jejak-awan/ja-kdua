<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-map-block transition-all duration-700 group hover:scale-[1.02]"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Location Map'"
  >
    <div class="map-container relative w-full overflow-hidden bg-slate-100 dark:bg-slate-900 border-y-2 border-slate-100 dark:border-slate-800" :style="containerStyles">
        <!-- Static Map Overlay if provided -->
        <img v-if="settings.staticMapUrl" :src="(settings.staticMapUrl as string)" class="w-full h-full object-cover grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700" alt="Map" />
        
        <!-- Interactive Elements Overlay -->
        <div class="absolute inset-0 pointer-events-none group-hover:bg-primary/5 transition-colors duration-700 "></div>
        
        <!-- Center Destination UI -->
        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 gap-6">
             <div class="w-24 h-24 rounded-full bg-white dark:bg-slate-950 shadow-2xl flex items-center justify-center transition-all duration-700 group-hover:scale-110 group-hover:shadow-primary/40 relative">
                <div class="absolute inset-0 rounded-full border-4 border-primary/20 animate-ping"></div>
                <MapPin class="w-10 h-10 text-primary" />
             </div>
             
             <div class="text-center group-hover:translate-y-2 transition-transform duration-700">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-primary mb-2 block">Our Base</span>
                <h3 class="font-black text-slate-900 dark:text-white text-3xl tracking-tighter">{{ settings.locationName || 'Global Operations Hub' }}</h3>
                <p class="font-medium opacity-60 text-lg">{{ settings.address || 'Click to view directions' }}</p>
             </div>
        </div>

        <!-- Simulated Map Patterns (Replaces static images for premium feel) -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.5" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <!-- Controls UI -->
        <div class="absolute bottom-12 right-12 z-20 flex flex-col gap-4">
             <Button variant="secondary" size="icon" class="rounded-2xl shadow-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-slate-200/50 hover:bg-primary hover:text-white transition-all w-14 h-14">
                <Plus class="w-6 h-6" />
             </Button>
             <Button variant="secondary" size="icon" class="rounded-2xl shadow-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-slate-200/50 hover:bg-primary hover:text-white transition-all w-14 h-14">
                <Minus class="w-6 h-6" />
             </Button>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Minus from 'lucide-vue-next/dist/esm/icons/minus.js';
import { 
    getVal,
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

const containerStyles = computed((): CSSProperties => {
    const layout = getLayoutStyles(settings.value, props.device)
    const height = getVal<number>(settings.value, 'height', props.device) || 600
    return { 
        ...layout,
        width: '100%', 
        height: `${height}px`
    } as CSSProperties
})
</script>

<style scoped>
.fullwidth-map-block { width: 100%; position: relative; }
</style>
