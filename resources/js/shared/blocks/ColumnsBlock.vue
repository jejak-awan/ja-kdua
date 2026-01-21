<template>
  <BaseBlock :module="module" :settings="settings" class="columns-block">
    <div class="columns-inner flex flex-wrap" :class="[reverseClasses]" :style="flexStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <div v-if="!module.children?.length" class="columns-empty w-full p-8 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-xl text-center text-gray-300">
           No columns added.
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <template v-for="(column, index) in computedColumns" :key="column.id || index">
            <div 
                class="column-wrapper relative flex items-stretch flex-shrink-0 flex-grow-0"
                :class="getColumnClasses()"
                :style="{ '--desktop-width': colWidths[index] }"
            >
                 <BlockRenderer 
                    :block="column" 
                    :mode="mode"
                    class="h-full w-full"
                />
            </div>
        </template>
      </template>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const isModern = computed(() => props.nestedBlocks && props.nestedBlocks.length > 0)
const computedColumns = computed(() => props.nestedBlocks || [])

const colWidths = computed(() => {
    const numCols = computedColumns.value.length
    if (numCols === 0) return []
    
    let widths = []
    const layout = getVal(settings.value, 'layout') || '1-1'
    
    switch (layout) {
        case '1': widths = [100]; break
        case '1-1': widths = [50, 50]; break
        case '1-2': widths = [33.333, 66.667]; break
        case '2-1': widths = [66.667, 33.333]; break
        case '1-1-1': widths = [33.333, 33.333, 33.333]; break
        default: widths = Array(numCols).fill(100 / numCols)
    }

    const gap = 2 // rem
    const totalGap = (numCols - 1) * gap
    return widths.map(w => {
        const gapSubtraction = (totalGap * (w / 100)).toFixed(3)
        return `calc(${w}% - ${gapSubtraction}rem)`
    })
})

const getColumnClasses = () => {
    const stackOn = getVal(settings.value, 'stackOn') || 'sm'
    if (stackOn === 'never') return 'w-[var(--desktop-width)]'
    const bp = stackOn === 'sm' ? 'md' : (stackOn === 'md' ? 'lg' : 'xl')
    return `w-full ${bp}:w-[var(--desktop-width)]`
}

const reverseClasses = computed(() => {
    const stackOn = getVal(settings.value, 'stackOn') || 'sm'
    const mobileDir = getVal(settings.value, 'mobileDirection') || 'column'
    const desktopDir = getVal(settings.value, 'direction') || 'row'
    
    const directionMap = {
        'row': 'flex-row',
        'row-reverse': 'flex-row-reverse',
        'column': 'flex-col',
        'column-reverse': 'flex-col-reverse'
    }
    
    if (stackOn === 'never') return directionMap[desktopDir] || 'flex-row'
    
    const bp = stackOn === 'sm' ? 'md' : (stackOn === 'md' ? 'lg' : 'xl')
    return `${directionMap[mobileDir] || 'flex-col'} ${bp}:${directionMap[desktopDir] || 'flex-row'}`
})

const flexStyles = computed(() => ({}))
</script>

<style scoped>
.columns-block { width: 100%; }
.column-wrapper { min-width: 0; }
</style>
