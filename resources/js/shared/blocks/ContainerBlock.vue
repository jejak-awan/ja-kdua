<template>
  <BaseBlock :module="module" :mode="mode" :settings="settings" class="container-block">
    <component
      :is="settings.link_url ? 'a' : 'div'"
      class="container-inner relative overflow-hidden transition-colors duration-300 w-full min-h-[50px]"
      :href="mode === 'view' ? settings.link_url : null"
      :target="mode === 'view' && settings.link_url ? (settings.link_target || '_self') : null"
      :style="innerStyles"
    >
        <!-- Overlay -->
        <div v-if="settings.overlayColor" class="container-overlay absolute inset-0 pointer-events-none z-0" :style="overlayStyles" />
        
        <!-- Content Container -->
        <div class="container-content relative z-10 w-full h-full" :style="contentStyles">
            <!-- Builder Mode -->
            <template v-if="mode === 'edit'">
                <slot />
                <div v-if="!module.children?.length" class="container-placeholder flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-100 dark:border-gray-900 rounded-2xl text-gray-400 gap-3">
                    <Box class="w-10 h-10 opacity-30" />
                    <span class="font-bold">Container Block</span>
                    <small>Drop modules here</small>
                </div>
            </template>

            <!-- Renderer Mode -->
            <template v-else>
                <div 
                    v-for="child in nestedBlocks" 
                    :key="child.id" 
                    class="container-child-wrapper"
                >
                    <BlockRenderer
                      :block="child"
                      :mode="mode"
                    />
                </div>
            </template>
        </div>
    </component>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Box from 'lucide-vue-next/dist/esm/icons/box.js';import { 
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockProps, BuilderInstance } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop',
  nestedBlocks: () => []
})

const builder = inject<BuilderInstance>('builder', null as any)
const settings = computed(() => props.settings || props.module?.settings || {})
const currentDevice = computed(() => props.device || builder?.device?.value || 'desktop')

const BlockRenderer = inject<any>('BlockRenderer', null)

const innerStyles = computed(() => ({}))

const overlayStyles = computed(() => ({
  backgroundColor: (settings.value.overlayColor as string) || 'transparent'
}))

const contentStyles = computed(() => {
  const direction = getResponsiveValue(settings.value, 'direction', currentDevice.value) || 'column'
  const alignItems = getResponsiveValue(settings.value, 'alignItems', currentDevice.value) || 'stretch'
  const justifyContent = getResponsiveValue(settings.value, 'justifyContent', currentDevice.value) || 'flex-start'
  const gap = getResponsiveValue(settings.value, 'gap', currentDevice.value) || 0
  const wrap = getResponsiveValue(settings.value, 'wrap', currentDevice.value)

  return {
    display: 'flex',
    flexDirection: direction,
    alignItems: alignItems,
    justifyContent: justifyContent,
    gap: typeof gap === 'number' ? `${gap}px` : gap,
    flexWrap: (wrap === 'flex-wrap' || wrap === true ? 'wrap' : 'nowrap') as 'wrap' | 'nowrap'
  }
})
</script>

<style scoped>
.container-block { width: 100%; }
.container-child-wrapper { width: 100%; }
a.container-inner { cursor: pointer; text-decoration: none; color: inherit; }
a.container-inner:hover { transform: translateY(-1px); }
</style>
