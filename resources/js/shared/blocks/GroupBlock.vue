<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="group-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Group Container'"
  >
    <component
      :is="settings.link_url ? 'a' : 'div'"
      class="group-inner relative transition-colors duration-300 w-full min-h-[50px]"
      :href="mode === 'view' ? (settings.link_url as string) : undefined"
      :target="mode === 'view' && settings.link_url ? ((settings.link_target as string) || '_self') : undefined"
      :style="(containerStyles as any)"
    >
        <!-- Overlay -->
        <div v-if="settings.overlayColor" class="group-overlay absolute inset-0 pointer-events-none z-0" :style="(overlayStyles as any)" />
        
        <!-- Content Container -->
        <div class="group-content relative z-10 w-full h-full" :style="(contentStyles as any)">
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
import { computed, inject, type Component } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import { 
  getVal,
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const BlockRenderer = inject<Component | null>('BlockRenderer', null)

const containerStyles = computed(() => {
    const layoutStyles = getLayoutStyles(settings.value, device.value)
    const styles: Record<string, string | number> = {
        ...layoutStyles,
        overflow: 'hidden'
    }
    return styles
})

const overlayStyles = computed(() => ({
  backgroundColor: settings.value.overlayColor || 'transparent'
}))

const contentStyles = computed(() => {
  const direction = getVal<string>(settings.value, 'direction', device.value) || 'column'
  const alignItems = getVal<string>(settings.value, 'alignItems', device.value) || 'stretch'
  const justifyContent = getVal<string>(settings.value, 'justifyContent', device.value) || 'flex-start'
  const gap = parseInt(getVal<string>(settings.value, 'gap', device.value) || '20')
  const wrap = getVal<boolean>(settings.value, 'wrap', device.value)

  const styles: Record<string, string | number> = {
    display: 'flex',
    flexDirection: direction,
    alignItems: alignItems,
    justifyContent: justifyContent,
    gap: `${gap}px`,
    flexWrap: (wrap ? 'wrap' : 'nowrap') as 'wrap' | 'nowrap'
  }
  return styles
})
</script>

<style scoped>
.group-block { width: 100%; }
a.group-inner { cursor: pointer; text-decoration: none; color: inherit; display: block; }
a.group-inner:hover { transform: translateY(-2px); }
</style>

