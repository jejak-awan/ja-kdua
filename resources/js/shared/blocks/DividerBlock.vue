<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="divider-block py-12"
  >
    <div class="divider-wrapper container mx-auto" :style="containerStyles">
        <div 
            class="divider-line w-full h-px bg-slate-200 dark:bg-slate-800 transition-all duration-700"
            :style="lineStyles"
        />
        
        <!-- Center Accent -->
        <div v-if="settings.showAccent !== false" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 px-4 bg-white dark:bg-slate-950">
             <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse" />
        </div>
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

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        position: 'relative',
        width: '100%'
    } as CSSProperties
})

const lineStyles = computed((): CSSProperties => {
    return {
        backgroundColor: (settings.value.lineColor as string) || 'currentColor',
        height: `${getVal<number>(settings.value, 'thickness', props.device) || 1}px`,
        opacity: (getVal<number>(settings.value, 'opacity', props.device) || 100) / 100
    } as CSSProperties
})
</script>

<style scoped>
.divider-block { width: 100%; position: relative; }
</style>
