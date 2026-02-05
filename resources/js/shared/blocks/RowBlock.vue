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
    
    // Column structure logic (flex-based)
    const columns = getVal<string>(settings.value, 'columns', props.device) || '1-1'
    const colConfigs = columns.split('-').map((fraction: string) => {
        const parts = fraction.split('/').map(Number)
        // If it's a fraction like 1/3, we use numerator as grow weight
        if (parts.length === 2) return { grow: parts[0], basis: '0%' }
        // Simple numeric/legacy format
        const growCount = Number(fraction) || 1
        return { grow: growCount, basis: '0%' }
    })

    colConfigs.forEach((config, i: number) => {
        // If auto-stacking on mobile/tablet, force basis to 100% and grow to 0
        const isStacked = (!hasResponsiveColumns && (isMobile || isTablet))
        styles[`--col-grow-${i}`] = isStacked ? '0' : config.grow
        styles[`--col-basis-${i}`] = isStacked ? '100%' : config.basis
    })

    return styles
})

const getColumnStyles = (index: number) => {
    return {
        flex: `var(--col-grow-${index}, 1) 1 var(--col-basis-${index}, 0%)`,
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
.row-inner:deep(.children-container > :nth-child(1)) { flex: var(--col-grow-0, 1) 1 var(--col-basis-0, 0%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(2)) { flex: var(--col-grow-1, 1) 1 var(--col-basis-1, 0%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(3)) { flex: var(--col-grow-2, 1) 1 var(--col-basis-2, 0%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(4)) { flex: var(--col-grow-3, 1) 1 var(--col-basis-3, 0%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(5)) { flex: var(--col-grow-4, 1) 1 var(--col-basis-4, 0%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(6)) { flex: var(--col-grow-5, 1) 1 var(--col-basis-5, 0%); height: 100%; }
</style>
