<template>
  <BaseBlock :module="module" :mode="mode" :device="device" class="column-block">
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

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Plus } from 'lucide-vue-next'
import { 
  getResponsiveValue,
  getVal,
  getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance } from '../../types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
  nestedBlocks?: BlockInstance[];
}>(), {
  mode: 'view',
  device: 'desktop',
  nestedBlocks: () => []
})

const builder = inject<BuilderInstance>('builder', null as any)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const columnStyles = computed(() => {
    const layout = getVal(settings.value, 'layout_type', device.value) || 'flex'
    const align = getVal(settings.value, 'align_items', device.value) || 'stretch'
    const justify = getVal(settings.value, 'justify_content', device.value) || 'flex-start'
    const gap = getVal(settings.value, 'gap', device.value) || 20

    const styles: Record<string, any> = {
        '--col-align': align,
        '--col-justify': justify,
        '--col-gap': `${gap}px`,
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column' as const,
        ...getLayoutStyles(settings.value, device.value) // Use standard layout styles if available
    }

    if (layout === 'flex') {
        styles.alignItems = 'var(--col-align)'
        styles.justifyContent = 'var(--col-justify)'
        styles.gap = 'var(--col-gap)'
    } else if (layout === 'grid') {
        styles.display = 'grid'
        styles.gap = 'var(--col-gap)'
    }

    return styles
})
</script>

<style scoped>
.column-block { height: 100% !important; display: flex; flex-direction: column; width: 100%; }
.column-child { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits layout */
.column-inner :deep(.children-container) {
    display: inherit;
    flex-direction: inherit;
    width: 100%;
    height: 100%;
    min-height: 50px;
    gap: inherit;
    align-items: inherit;
    justify-content: inherit;
}
</style>
