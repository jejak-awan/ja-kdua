<template>
  <div 
    class="column-block transition-all duration-300" 
    :class="[columnSize, alignmentClass]"
    :style="columnStyles"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import { getVal, getLayoutStyles } from '../utils/styleUtils'
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

const columnSize = computed(() => {
    const size = getVal<string>(settings.value, 'width', props.device) || '1/2'
    const sizes: Record<string, string> = {
        '1/1': 'w-full',
        '1/2': 'w-1/2',
        '1/3': 'w-1/3',
        '2/3': 'w-2/3',
        '1/4': 'w-1/4',
        '3/4': 'w-3/4'
    }
    return sizes[size] || 'w-1/2'
})

const alignmentClass = computed(() => {
    const align = getVal<string>(settings.value, 'alignment', props.device) || 'center'
    if (align === 'left') return 'flex flex-col items-start'
    if (align === 'right') return 'flex flex-col items-end'
    return 'flex flex-col items-center'
})

const columnStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.column-block { position: relative; }
</style>
