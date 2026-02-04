<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device" 
    class="row-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Row'"
    :style="cardStyles"
  >
    <div class="row-inner flex flex-wrap w-full transition-[width] duration-500" :style="rowStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Auto Generated Columns if empty -->
        <div v-if="!module.children?.length" class="row-empty w-full flex justify-center p-10 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-2xl text-gray-300 bg-gray-50/30 dark:bg-gray-900/30 transition-colors duration-300">
           <LayoutIcon class="w-10 h-10 opacity-10 animate-pulse" />
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div 
          v-for="(child, index) in nestedBlocks" 
          :key="child.id" 
          class="row-column h-full transition-colors duration-300"
          :style="getColumnStyles(index)"
        >
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
import LayoutIcon from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import { 
    getVal,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'
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

const rowStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    const layout = getVal<string>(settings.value, 'layout_type', props.device) || 'flex'
    const gutterX = getVal<string | number>(settings.value, 'gap_x', props.device) || 0
    const gutterY = getVal<string | number>(settings.value, 'gap_y', props.device) || 0
    const vAlign = getVal<string>(settings.value, 'align_items', props.device) || 'stretch'
    const justify = getVal<string>(settings.value, 'justify_content', props.device) || 'flex-start'
    
    // Auto-stacking logic for mobile/tablet
    const isMobile = props.device === 'mobile'
    const isTablet = props.device === 'tablet'
    const hasResponsiveColumns = settings.value[`columns_${props.device}`] || (isMobile && settings.value.columns_mobile)
    
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        '--row-align': vAlign,
        '--row-gutter-x': typeof gutterX === 'number' ? `${gutterX}px` : gutterX,
        '--row-gutter-y': typeof gutterY === 'number' ? `${gutterY}px` : gutterY,
        width: '100%',
        height: '100%'
    }

    if (layout === 'grid') {
        styles.display = 'grid'
        styles.gap = `var(--row-gutter-y) var(--row-gutter-x)`
        styles.alignItems = 'var(--row-align)'
        styles.justifyContent = justify
        
        const columns = getVal<string>(settings.value, 'columns', props.device) || '1-1'
        const colCount = (!hasResponsiveColumns && (isMobile || isTablet)) ? 1 : columns.split('-').length
        styles.gridTemplateColumns = `repeat(${colCount}, 1fr)`
    } else if (layout === 'block') {
        styles.display = 'block'
    } else {
        // Default Flex
        styles.display = 'flex'
        styles.flexDirection = (!hasResponsiveColumns && (isMobile || isTablet)) ? 'column' : 'row'
        styles.flexWrap = 'wrap' 
        styles.alignItems = 'var(--row-align)'
        styles.gap = 'var(--row-gutter-x)' as string
        styles.justifyContent = justify
    }
    
    // Legacy column widths support (for flex mode)
    const columns = getVal<string>(settings.value, 'columns', props.device) || '1-1'
    const colWidths = columns.split('-').map((fraction: string) => {
        const parts = fraction.split('/').map(Number)
        if (parts.length === 2) return `${(parts[0] / parts[1]) * 100}%`
        return `${100 / columns.split('-').length}%`
    })

    colWidths.forEach((width: string, i: number) => {
        // If auto-stacking, override width to 100%
        styles[`--col-width-${i}`] = (!hasResponsiveColumns && (isMobile || isTablet)) ? '100%' : width
    })

    return styles
})

const getColumnStyles = (index: number) => {
    return {
        width: `var(--col-width-${index}, 100%)`,
        height: '100%'
    }
}
</script>

<style scoped>
.row-block { 
    width: 100% !important; 
    margin: 0 !important;
    max-width: 100% !important;
    height: auto;
    position: relative;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.row-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}

/* Builder Mode: Ensure the draggable container inherits the flex layout */
.row-inner :deep(.children-container) {
    display: inherit; 
    flex-direction: inherit;
    flex-wrap: inherit;
    width: 100%;
    min-height: 50px;
    gap: inherit;
    align-items: var(--row-align);
    justify-content: inherit;
    grid-template-columns: inherit;
}

/* Apply specific widths to children in builder mode - allow flex stretching */
.row-inner:deep(.children-container > :nth-child(1)) { width: var(--col-width-0, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(2)) { width: var(--col-width-1, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(3)) { width: var(--col-width-2, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(4)) { width: var(--col-width-3, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(5)) { width: var(--col-width-4, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(6)) { width: var(--col-width-5, 100%); height: 100%; }
</style>
