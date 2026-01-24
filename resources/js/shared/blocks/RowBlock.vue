<template>
  <BaseBlock :module="module" :mode="mode" :device="device" class="row-block">
    <div class="row-inner flex flex-wrap w-full" :style="rowStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Auto Generated Columns if empty -->
        <div v-if="!module.children?.length" class="row-empty w-full flex justify-center p-8 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-xl text-gray-300">
           <Layout class="w-8 h-8 opacity-20" />
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div 
          v-for="(child, index) in nestedBlocks" 
          :key="child.id" 
          class="row-column h-full"
          :style="getColumnStyles(index)"
        >
          <BlockRenderer
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
import { Layout } from 'lucide-vue-next'
import { 
  getResponsiveValue,
  getVal
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '../../types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
  nestedBlocks?: BlockInstance[];
}>(), {
  mode: 'view',
  device: 'desktop',
  nestedBlocks: () => []
})

const builder = inject<BuilderInstance>('builder', null as any)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const rowStyles = computed(() => {
    const layout = getVal(settings.value, 'layout_type', device.value) || 'flex'
    const gutterX = getVal(settings.value, 'gap_x', device.value) || 0
    const gutterY = getVal(settings.value, 'gap_y', device.value) || 0
    const vAlign = getVal(settings.value, 'align_items', device.value) || 'stretch'
    const justify = getVal(settings.value, 'justify_content', device.value) || 'flex-start'
    
    // Auto-stacking logic for mobile/tablet
    const isMobile = device.value === 'mobile'
    const isTablet = device.value === 'tablet'
    const hasResponsiveColumns = settings.value[`columns_${device.value}`] || (isMobile && settings.value.columns_mobile)
    
    const styles: Record<string, any> = {
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
        
        const columns = getVal(settings.value, 'columns', device.value) || '1-1'
        const colCount = (!hasResponsiveColumns && (isMobile || isTablet)) ? 1 : columns.split('-').length
        styles.gridTemplateColumns = `repeat(${colCount}, 1fr)`
    } else if (layout === 'block') {
        styles.display = 'block'
    } else {
        // Default Flex
        styles.display = 'flex'
        styles.flexDirection = (!hasResponsiveColumns && (isMobile || isTablet)) ? 'column' : 'row'
        styles.flexWrap = 'wrap' // Allow wrapping on responsive
        styles.alignItems = 'var(--row-align)'
        styles.gap = 'var(--row-gutter-x)'
        styles.justifyContent = justify
    }
    
    // Legacy column widths support (for flex mode)
    const columns = getVal(settings.value, 'columns', device.value) || '1-1'
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
}

/* Builder Mode: Ensure the draggable container inherits the flex layout */
.row-inner :deep(.children-container) {
    display: inherit; /* Should be flex/grid based on row-inner */
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
