<template>
  <BaseBlock 
    :module="module" 
    :settings="settings" 
    :mode="mode"
    class="sidebar-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Sidebar'"
  >
    <div class="sidebar-widgets-container flex flex-col" :style="containerStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <template v-if="nestedBlocks && nestedBlocks.length">
            <div v-for="child in nestedBlocks" :key="child.id" class="sidebar-widget-item mb-10 last:mb-0">
                <BlockRenderer
                  :block="child"
                  :mode="mode"
                />
            </div>
        </template>
        <!-- Mock widgets for demonstration if empty -->
        <div v-else class="mock-widgets flex flex-col gap-8 opacity-40 grayscale">
            <div class="mock-widget">
                <h4 class="font-bold border-b pb-2 mb-4">Search</h4>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded-lg"></div>
            </div>
            <div class="mock-widget">
                <h4 class="font-bold border-b pb-2 mb-4">Latest Posts</h4>
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-4/5"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-5/6"></div>
                </div>
            </div>
        </div>
      </template>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'
import type { Component } from 'vue'

// We avoid direct import of BlockRenderer to prevent circular dependencies
// In the renderer environment, it will be globally available or passed down
const BlockRenderer = inject<Component | null>('BlockRenderer', null)

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
  nestedBlocks?: BlockInstance[]
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const containerStyles = computed(() => {
    const layout = getLayoutStyles(settings.value, device.value)
    const styles: Record<string, string | number> = {
        ...layout,
        gap: '2.5rem' // Standard 10 spacing
    }
    return styles
})
</script>

<style scoped>
.sidebar-block { width: 100%; }
.sidebar-widget-item { transition: all 0.3s ease; }
.sidebar-widget-item:hover { transform: translateX(5px); }
</style>

