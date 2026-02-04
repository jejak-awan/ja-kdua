<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="embed-block transition-all duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Embed Content'"
    :style="cardStyles"
  >
    <div class="embed-wrapper relative w-full overflow-hidden flex flex-col items-center justify-center p-8 bg-slate-900/5 dark:bg-slate-100/5 rounded-[2.5rem]" :style="containerStyles">
        <!-- Placeholder for Builder -->
        <div v-if="mode === 'edit'" class="flex flex-col items-center justify-center gap-6 py-12 text-center opacity-40 group-hover:opacity-100 transition-opacity duration-500">
             <div class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center">
                <CodeXml class="w-8 h-8 text-primary" />
             </div>
             <div>
                <h3 class="font-black text-xs uppercase tracking-[0.4em] mb-2">Embed Engine</h3>
                <p class="text-[10px] font-medium max-w-[240px] leading-relaxed">External script and iframe execution is paused in the builder for security and performance.</p>
             </div>
             
             <!-- Preview Code -->
             <div class="bg-black/90 p-4 rounded-xl max-w-sm overflow-hidden border border-white/10 group-hover:border-primary/20 transition-colors">
                <code class="text-[9px] text-green-400 font-mono block truncate">{{ (settings.code as string) || '<!-- No Code Configured -->' }}</code>
             </div>
        </div>

        <!-- Live Content -->
        <div v-else class="w-full h-full" v-html="(settings.code as string)"></div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import CodeXml from 'lucide-vue-next/dist/esm/icons/code-xml.js';
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
        width: '100%',
        minHeight: `${getVal<number>(settings.value, 'min_height', props.device) || 300}px`
    } as CSSProperties
})
</script>

<style scoped>
.embed-block { width: 100%; position: relative; }
</style>
