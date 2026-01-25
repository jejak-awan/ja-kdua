<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="group-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Group Container'"
  >
    <component
      :is="settings.link_url ? 'a' : 'div'"
      class="group-inner relative transition-all duration-300 w-full min-h-[50px]"
      :href="mode === 'view' ? settings.link_url : undefined"
      :target="mode === 'view' && settings.link_url ? (settings.link_target || '_self') : undefined"
      :style="containerStyles"
    >
        <!-- Overlay -->
        <div v-if="settings.overlayColor" class="group-overlay absolute inset-0 pointer-events-none z-0" :style="overlayStyles" />
        
        <!-- Content Container -->
        <div class="group-content relative z-10 w-full h-full" :style="contentStyles">
            <!-- Builder Mode -->
            <template v-if="mode === 'edit'">
                <slot />
                <div v-if="!module.children?.length" class="group-placeholder flex flex-col items-center justify-center p-12 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-3xl text-slate-400 gap-4 transition-colors hover:border-primary/50 group-hover:bg-slate-50/50 dark:group-hover:bg-slate-900/50">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center shadow-sm">
                        <Square class="w-8 h-8 opacity-40 text-primary" />
                    </div>
                    <div class="text-center">
                        <span class="font-black text-slate-600 dark:text-slate-400 block tracking-tight">Group Container</span>
                        <small class="opacity-60 font-medium">Drag and drop components here to group them</small>
                    </div>
                </div>
            </template>

            <!-- Renderer Mode -->
            <template v-else>
                <div 
                    v-for="child in (module.children || [])" 
                    :key="child.id" 
                    class="group-child-wrapper w-full"
                >
                    <BlockRenderer
                      v-if="BlockRenderer"
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
import { Square } from 'lucide-vue-next'
import { 
  getVal,
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const BlockRenderer = inject<any>('BlockRenderer', null)

const containerStyles = computed(() => {
    const styles = getLayoutStyles(settings.value, device.value)
    return {
        ...styles,
        overflow: 'hidden'
    }
})

const overlayStyles = computed(() => ({
  backgroundColor: settings.value.overlayColor || 'transparent'
}))

const contentStyles = computed(() => {
  const direction = getVal(settings.value, 'direction', device.value) || 'column'
  const alignItems = getVal(settings.value, 'alignItems', device.value) || 'stretch'
  const justifyContent = getVal(settings.value, 'justifyContent', device.value) || 'flex-start'
  const gap = parseInt(getVal(settings.value, 'gap', device.value)) ?? 20
  const wrap = getVal(settings.value, 'wrap', device.value)

  return {
    display: 'flex',
    flexDirection: direction,
    alignItems: alignItems,
    justifyContent: justifyContent,
    gap: `${gap}px`,
    flexWrap: (wrap ? 'wrap' : 'nowrap') as any
  }
})
</script>

<style scoped>
.group-block { width: 100%; }
a.group-inner { cursor: pointer; text-decoration: none; color: inherit; display: block; }
a.group-inner:hover { transform: translateY(-2px); }
</style>

