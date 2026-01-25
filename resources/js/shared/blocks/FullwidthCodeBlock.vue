<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="fullwidth-code-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Code'"
    :style="cardStyles"
  >
    <div class="code-wrapper w-full h-full" :style="containerStyles">
      <div v-if="mode === 'edit'" class="builder-placeholder bg-slate-950 p-10 rounded-[2rem] border border-white/10 shadow-2xl">
          <div class="flex items-center gap-3 mb-6 opacity-40">
              <Code class="w-6 h-6 text-primary" />
              <span class="text-xs font-black tracking-widest uppercase">Custom Code Module</span>
          </div>
          <p class="text-sm font-medium text-slate-400 leading-relaxed max-w-lg mb-8">
            Your custom HTML, CSS, or JavaScript code will be executed in the live view. 
            In the builder, we render this safe preview to maintain performance.
          </p>
          <pre class="bg-slate-900 p-6 rounded-2xl text-[10px] text-primary/70 font-mono overflow-auto border border-white/5">{{ settings.rawContent || '<!-- Empty -->' }}</pre>
      </div>
      <div v-else class="raw-code-container h-full" v-html="settings.rawContent"></div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Code } from 'lucide-vue-next'
import { 
    getVal,
    getLayoutStyles
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

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))
</script>

<style scoped>
.fullwidth-code-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.fullwidth-code-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
