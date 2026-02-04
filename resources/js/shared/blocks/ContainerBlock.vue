<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="container-block transition-all duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Content Container'"
  >
    <div 
      class="container-content relative w-full h-full rounded-[2rem] overflow-hidden" 
      :style="containerStyles"
    >
      <!-- Background Asset Case -->
      <BackgroundMedia 
        v-if="settings.backgroundImage || settings.backgroundVideo"
        :settings="settings"
        :device="device"
      />
      
      <!-- Slot for Child Modules -->
      <div 
        class="relative z-10 w-full h-full flex flex-col"
        :style="paddings"
      >
        <slot />
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import BackgroundMedia from '../components/BackgroundMedia.vue'
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
        width: '100%',
        minHeight: `${getVal<number>(settings.value, 'min_height', props.device) || 100}px`
    } as CSSProperties
})

const paddings = computed((): CSSProperties => {
    const p = getVal<Record<string, string | number>>(settings.value, 'padding', props.device)
    if (!p) return {}
    return {
        paddingTop: p.top,
        paddingRight: p.right,
        paddingBottom: p.bottom,
        paddingLeft: p.left
    } as CSSProperties
})
</script>

<style scoped>
.container-block { width: 100%; position: relative; }
</style>
