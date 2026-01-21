<template>
  <BaseBlock :module="module" :settings="settings" class="column-block">
    <div class="column-inner w-full h-full flex flex-col" :style="columnStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Empty State -->
        <div v-if="!module.children?.length" class="column-empty flex-grow flex items-center justify-center p-6 border-2 border-dashed border-gray-50 dark:border-gray-900 rounded-lg text-gray-200">
           <Plus class="w-5 h-5 opacity-30" />
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div v-for="child in nestedBlocks" :key="child.id" class="column-child">
          <BlockRenderer
            :block="child"
            :mode="mode"
          />
        </div>
      </template>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Plus } from 'lucide-vue-next'
import { 
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const columnStyles = computed(() => {
    const align = getResponsiveValue(settings.value, 'alignItems', device.value) || 'stretch'
    const justify = getResponsiveValue(settings.value, 'justifyContent', device.value) || 'flex-start'
    const gap = getResponsiveValue(settings.value, 'gap', device.value) || 20

    return {
        '--col-align': align,
        '--col-justify': justify,
        '--col-gap': `${gap}px`,
        alignItems: 'var(--col-align)', // Fallback for view mode
        justifyContent: 'var(--col-justify)',
        gap: 'var(--col-gap)'
    }
})
</script>

<style scoped>
.column-block { height: 100%; display: flex; flex-direction: column; }
.column-child { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits layout */
.column-inner :deep(.children-container) {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    min-height: 50px;
    gap: var(--col-gap);
    align-items: var(--col-align);
    justify-content: var(--col-justify);
}
</style>
