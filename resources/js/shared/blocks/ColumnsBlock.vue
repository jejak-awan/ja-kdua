<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="columns-block py-12"
  >
    <div 
        class="columns-container container mx-auto flex flex-wrap" 
        :class="[gapClass]"
        :style="containerStyles"
    >
        <slot />
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
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

const gapClass = computed(() => {
    const gap = getVal<string>(settings.value, 'gap', props.device) || 'medium'
    const gaps: Record<string, string> = {
        none: 'gap-0',
        small: 'gap-4',
        medium: 'gap-8',
        large: 'gap-12'
    }
    return gaps[gap] || 'gap-8'
})

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.columns-block { width: 100%; position: relative; }
</style>
