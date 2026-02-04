<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device" 
    tag="section"
    class="section-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Section'"
    :style="(cardStyles as any)"
  >
    <div class="section-container relative w-full transition-[width] duration-500" :style="(containerStyles as any)">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Empty State -->
        <div v-if="!module.children?.length" class="section-empty flex justify-center p-12 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl text-gray-400 bg-gray-50/50 dark:bg-gray-900/50 transition-colors duration-300 hover:border-emerald-400/50">
           <button class="add-row-btn w-12 h-12 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-full shadow-lg hover:scale-110 active:scale-95 transition-colors duration-300" @click.stop="addRow">
             <PlusIcon class="w-6 h-6" />
           </button>
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div v-for="child in nestedBlocks" :key="child.id" class="section-child w-full">
          <BlockRenderer
            v-if="BlockRenderer"
            :block="child"
            :mode="mode"
          />
        </div>
      </template>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import PlusIcon from 'lucide-vue-next/dist/esm/icons/plus.js';
import { 
    getVal,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'
import type { Component } from 'vue'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
  nestedBlocks?: BlockInstance[];
}>(), {
  mode: 'view',
  device: 'desktop',
  nestedBlocks: () => []
})

const builder = inject<BuilderInstance | null>('builder', null)
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const BlockRenderer = inject<Component | null>('BlockRenderer', null)

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const vAlign = getVal<string>(settings.value, 'verticalAlignment', props.device) || 'start'
    
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        display: 'flex',
        flexDirection: 'column' as const,
        justifyContent: vAlign === 'center' ? 'center' : (vAlign === 'end' ? 'flex-end' : 'flex-start'),
        minHeight: '80px'
    }
    return styles
})

const addRow = () => {
    if (builder?.openInsertRowModal) {
        builder.openInsertRowModal(props.module.id)
    }
}
</script>

<style scoped>
.section-block { width: 100%; position: relative; }
.section-block {
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.section-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
