<template>
  <BaseBlock :module="module" :settings="settings" class="row-block">
    <div class="row-inner flex flex-wrap w-full h-full" :style="rowStyles">
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

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Layout } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const rowStyles = computed(() => {
    const vAlign = getResponsiveValue(settings.value, 'verticalAlign', device.value) || 'stretch'
    const gutter = getResponsiveValue(settings.value, 'gutter', device.value) || 0
    const layout = getResponsiveValue(settings.value, 'columns', device.value) || '1-1'
    
    // Parse parts for CSS injection
    const parts = layout.split('-').map(Number)
    const totalParts = parts.reduce((a, b) => a + b, 0)
    
    const styles = {
        '--row-align': vAlign === 'start' ? 'flex-start' : (vAlign === 'end' ? 'flex-end' : vAlign),
        '--row-gutter': `${gutter}px`,
        alignItems: 'var(--row-align)',
        gap: 'var(--row-gutter)',
        justifyContent: 'flex-start',
        width: '100%',
        height: '100%'
    }

    // Inject column widths as CSS variables for deep selection
    const numColsOnCanvas = props.nestedBlocks.length
    if (numColsOnCanvas === 1) {
        styles['--col-width-0'] = '100%'
    } else {
        parts.forEach((p, i) => {
            const percentage = (p / totalParts) * 100
            const reduction = gutter > 0 && parts.length > 1 ? (gutter * (parts.length - 1)) / parts.length : 0
            styles[`--col-width-${i}`] = `calc(${percentage}% - ${reduction}px)`
        })
    }

    return styles
})

const getColumnStyles = (index) => {
    // Renderer mode uses this directly
    return {
        width: `var(--col-width-${index}, 100%)`,
        flexShrink: 0,
        flexGrow: 0
    }
}
</script>

<style scoped>
.row-block { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits the flex layout and applies widths */
.row-inner:deep(.children-container) {
    display: flex;
    flex-wrap: nowrap; /* Keep columns on one line in builder for structural clarity */
    width: 100%;
    height: 100%;
    min-height: 50px;
    gap: var(--row-gutter);
    align-items: stretch; /* Enforce full height columns */
    justify-content: flex-start;
}

/* Ensure module wrappers inside rows (which are columns) grow to fill height */
.row-inner:deep(.children-container > .module-wrapper--column) {
    display: flex;
    flex-direction: column;
}

/* Apply specific widths to children in builder mode - allow flex stretching */
.row-inner:deep(.children-container > :nth-child(1)) { width: var(--col-width-0, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(2)) { width: var(--col-width-1, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(3)) { width: var(--col-width-2, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(4)) { width: var(--col-width-3, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(5)) { width: var(--col-width-4, 100%); height: 100%; }
.row-inner:deep(.children-container > :nth-child(6)) { width: var(--col-width-5, 100%); height: 100%; }

</style>
