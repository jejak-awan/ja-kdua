<template>
  <BaseBlock :module="module" :settings="settings" class="section-block">
    <div class="section-container relative w-full" :style="containerStyles">
      <!-- Builder Mode -->
      <template v-if="mode === 'edit'">
        <slot />
        <!-- Empty State -->
        <div v-if="!module.children?.length" class="section-empty flex justify-center p-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400">
           <button class="add-row-btn w-10 h-10 flex items-center justify-center bg-emerald-500 text-white rounded-full shadow-lg" @click.stop="addRow">
             <Plus class="w-6 h-6" />
           </button>
        </div>
      </template>

      <!-- Renderer Mode -->
      <template v-else>
        <div v-for="child in nestedBlocks" :key="child.id" class="section-child">
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

const fullWidth = computed(() => getResponsiveValue(settings.value, 'full_width', device.value) === true)

const containerStyles = computed(() => {
  const vAlign = getResponsiveValue(settings.value, 'verticalAlign', device.value) || 'start'
  return {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: vAlign === 'center' ? 'center' : (vAlign === 'end' ? 'flex-end' : 'flex-start'),
    minHeight: 'inherit'
  }
})

const addRow = () => {
    if (builder?.openInsertRowModal) {
        builder.openInsertRowModal(props.module.id)
    }
}
</script>

<style scoped>
.section-block { width: 100%; position: relative; }
.section-container {
    min-height: 80px;
}
.section-child { width: 100%; }
</style>
