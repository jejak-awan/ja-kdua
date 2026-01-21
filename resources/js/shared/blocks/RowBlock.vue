<template>
  <BaseBlock :module="module" :settings="settings" class="row-block">
    <div class="row-inner flex flex-wrap w-full" :style="rowStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Auto Generated Columns if empty -->
        <div v-if="!module.children?.length" class="row-empty w-full flex justify-center p-8 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-xl text-gray-300">
           <Layout class="w-8 h-8 opacity-20" />
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div 
          v-for="(child, index) in nestedBlocks" 
          :key="child.id" 
          class="row-column h-full"
          :style="getColumnStyles(index)"
        >
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
import { Layout } from 'lucide-vue-next'
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

const rowStyles = computed(() => {
    const gap = getResponsiveValue(settings.value, 'gap', device.value) || 20
    const align = getResponsiveValue(settings.value, 'alignItems', device.value) || 'stretch'
    const justify = getResponsiveValue(settings.value, 'justifyContent', device.value) || 'flex-start'
    
    // We use CSS variables so the inner draggable container can inherit them
    return {
        '--row-gap': `${gap}px`,
        '--row-align': align,
        '--row-justify': justify,
        gap: 'var(--row-gap)',
        alignItems: 'var(--row-align)',
        justifyContent: 'var(--row-justify)'
    }
})

const getColumnStyles = (index) => {
    // Basic auto-width columns for now, 
    // real logic would calculate based on settings.layout
    return {
        flex: '1 1 0%',
        minWidth: '200px'
    }
}
</script>

<style scoped>
.row-block { width: 100%; }

/* Builder Mode: Ensure the draggable container inherits the flex layout */
.row-inner :deep(.children-container) {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    min-height: 50px; /* Ensure drop zone is visible */
    gap: var(--row-gap);
    align-items: var(--row-align);
    justify-content: var(--row-justify);
}

</style>
