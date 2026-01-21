<template>
  <BaseBlock :module="module" :settings="settings" class="sidebar-block">
    <!-- Sidebar Widgets -->
    <div class="sidebar-widgets-container flex flex-col gap-10">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <template v-if="nestedBlocks && nestedBlocks.length">
            <div v-for="child in nestedBlocks" :key="child.id" class="sidebar-widget-item">
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

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

// We avoid direct import of BlockRenderer to prevent circular dependencies
// In the renderer environment, it will be globally available or passed down
const BlockRenderer = inject('BlockRenderer', null)

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const showTitle = computed(() => getResponsiveValue(settings.value, 'showTitle', device.value) !== false)
</script>

<style scoped>
.sidebar-block { width: 100%; }
.sidebar-widget-item { transition: all 0.3s ease; }
.sidebar-widget-item:hover { transform: translateX(5px); }
</style>
