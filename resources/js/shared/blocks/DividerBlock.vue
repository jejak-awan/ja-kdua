<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperStyles, settings }">
      <div class="divider-block" :style="dividerWrapperStyles">
        <hr v-if="settings.visible !== false" class="divider-line" :style="lineStyles" />
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, toCSS } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const dividerWrapperStyles = computed(() => {
  const align = getVal(settings.value, 'alignment', props.device) || 'center'
  return {
    width: '100%',
    display: 'flex',
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start'
  }
})

const lineStyles = computed(() => {
  const weight = getVal(settings.value, 'lineWeight', props.device) || 1
  const width = getVal(settings.value, 'lineWidth', props.device) || '100%'
  const style = getVal(settings.value, 'lineStyle', props.device) || 'solid'
  const color = getVal(settings.value, 'lineColor', props.device) || '#cccccc'
  
  return {
    border: 'none',
    borderTop: `${weight}px ${style} ${color}`,
    width: toCSS(width),
    margin: 0
  }
})
</script>

<style scoped>
.divider-block { box-sizing: border-box; }
.divider-line { flex-shrink: 0; }
</style>
