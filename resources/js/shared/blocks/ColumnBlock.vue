<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device" 
    class="column-block transition-all duration-500"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Column'"
    :style="cardStyles"
  >
    <div class="column-inner w-full h-full flex flex-col transition-all duration-500" :style="columnStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Empty State -->
        <div v-if="!module.children?.length" class="column-empty flex-grow flex items-center justify-center p-8 border-2 border-dashed border-gray-50/50 dark:border-gray-900/50 rounded-xl text-gray-200 bg-gray-50/20 dark:bg-gray-900/20 transition-all duration-300 hover:border-emerald-400/30">
           <PlusIcon class="w-6 h-6 opacity-20 animate-pulse text-emerald-500" />
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div v-for="child in nestedBlocks" :key="child.id" class="column-child transition-all duration-300">
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
import { Plus as PlusIcon } from 'lucide-vue-next'
import { 
    getVal,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '@/types/builder'

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
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// @ts-ignore
const BlockRenderer = inject<any>('BlockRenderer', null)

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const columnStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const layout = getVal(settings.value, 'layout_type', props.device) || 'flex'
    const align = getVal(settings.value, 'align_items', props.device) || 'stretch'
    const justify = getVal(settings.value, 'justify_content', props.device) || 'flex-start'
    const gap = getVal(settings.value, 'gap', props.device) || 20

    const styles: Record<string, any> = {
        ...layoutStyles,
        '--col-align': align,
        '--col-justify': justify,
        '--col-gap': typeof gap === 'number' ? `${gap}px` : gap,
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column' as const,
    }

    if (layout === 'flex') {
        styles.alignItems = 'var(--col-align)'
        styles.justifyContent = 'var(--col-justify)'
        styles.gap = 'var(--col-gap)'
    } else if (layout === 'grid') {
        styles.display = 'grid'
        styles.gap = 'var(--col-gap)'
    }

    return styles
})
</script>

<style scoped>
.column-block { 
    height: 100% !important; 
    display: flex; 
    flex-direction: column; 
    width: 100%; 
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.column-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.column-child { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits layout */
.column-inner :deep(.children-container) {
    display: inherit;
    flex-direction: inherit;
    width: 100%;
    height: 100%;
    min-height: 50px;
    gap: inherit;
    align-items: inherit;
    justify-content: inherit;
}
</style>
