<template>
  <BaseBlock :module="module" :settings="settings" class="group-block">
    <component
      :is="settings.link_url ? 'a' : 'div'"
      class="group-inner relative overflow-hidden transition-all duration-300 w-full min-h-[50px]"
      :href="mode === 'view' ? settings.link_url : null"
      :target="mode === 'view' && settings.link_url ? (settings.link_target || '_self') : null"
      :style="innerStyles"
    >
        <!-- Overlay -->
        <div v-if="settings.overlayColor" class="group-overlay absolute inset-0 pointer-events-none z-0" :style="overlayStyles" />
        
        <!-- Content Container -->
        <div class="group-content relative z-10 w-full h-full" :style="contentStyles">
            <!-- Builder Mode -->
            <template v-if="mode === 'edit'">
                <slot />
                <div v-if="!module.children?.length" class="group-placeholder flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400 gap-3">
                    <Square class="w-10 h-10 opacity-30" />
                    <span class="font-bold">Group Container</span>
                    <small>Drop modules here</small>
                </div>
            </template>

            <!-- Renderer Mode -->
            <template v-else>
                <div 
                    v-for="child in nestedBlocks" 
                    :key="child.id" 
                    class="group-child-wrapper"
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

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Square } from 'lucide-vue-next'
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
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const innerStyles = computed(() => ({}))

const overlayStyles = computed(() => ({
  backgroundColor: settings.value.overlayColor || 'transparent'
}))

const contentStyles = computed(() => {
  const direction = getResponsiveValue(settings.value, 'direction', device.value) || 'column'
  const alignItems = getResponsiveValue(settings.value, 'alignItems', device.value) || 'stretch'
  const justifyContent = getResponsiveValue(settings.value, 'justifyContent', device.value) || 'flex-start'
  const gap = getResponsiveValue(settings.value, 'gap', device.value) || 20
  const wrap = getResponsiveValue(settings.value, 'wrap', device.value)

  return {
    display: 'flex',
    flexDirection: direction,
    alignItems: alignItems,
    justifyContent: justifyContent,
    gap: `${gap}px`,
    flexWrap: wrap ? 'wrap' : 'nowrap'
  }
})
</script>

<style scoped>
.group-block { width: 100%; }
.group-child-wrapper { width: 100%; }
a.group-inner { cursor: pointer; text-decoration: none; color: inherit; }
a.group-inner:hover { transform: translateY(-2px); }
</style>
