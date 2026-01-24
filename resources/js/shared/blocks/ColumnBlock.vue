<template>
  <BaseBlock :module="module" :settings="settings" class="column-block">
    <div class="column-inner w-full h-full flex flex-col" :style="columnStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <div v-if="module.children?.length" class="column-content w-full h-full">
          <slot />
        </div>
        <!-- Empty State / Add Module Button Area -->
        <div 
          v-else 
          class="column-empty flex-grow flex items-center justify-center p-4 min-h-[60px] cursor-pointer group/col-empty"
          @click.stop="openInsertModal"
        >
           <div class="add-module-ui flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 group-hover/col-empty:bg-gray-200 dark:group-hover/col-empty:bg-gray-700 transition-colors">
              <Plus class="w-5 h-5 text-gray-400 group-hover/col-empty:text-gray-600 dark:group-hover/col-empty:text-gray-300" />
           </div>
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
    const align = getVal(settings.value, 'align_items', device.value) || 'stretch'
    const justify = getVal(settings.value, 'justify_content', device.value) || 'flex-start'
    const gap = getVal(settings.value, 'gap', device.value) || 20

    return {
        '--col-align': align,
        '--col-justify': justify,
        '--col-gap': `${gap}px`,
        alignItems: 'var(--col-align)', // Fallback for view mode
        justifyContent: 'var(--col-justify)',
        gap: 'var(--col-gap)',
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column'
    }
})

const openInsertModal = () => {
    if (builder?.openInsertModal) {
        builder.openInsertModal(props.module.id)
    }
}
</script>

<style scoped>
.column-block { height: 100%; display: flex; flex-direction: column; }
.column-child { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits layout */
.column-inner:deep(.children-container) {
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
