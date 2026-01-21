<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="prose max-w-none" 
        :style="textStyles(settings, blockDevice)"
      >
        <template v-if="blockMode === 'edit'">
          <!-- In edit mode, we use the TiptapEditor component -->
          <TiptapEditor 
            :model-value="getVal(settings, 'content')"
            @update:model-value="onContentUpdate($event, settings)"
          />
        </template>
        <template v-else>
          <!-- In view mode, we render the HTML directly -->
          <div v-html="getVal(settings, 'content') || 'Click to add text content...'"></div>
        </template>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal } from '../utils/styleUtils'
import TiptapEditor from '../../components/TiptapEditor.vue'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)

const textStyles = (settings, device) => {
  return getTypographyStyles(settings, '', device)
}

const onContentUpdate = (newContent, settings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, content: newContent }
  })
}
</script>

<style scoped>
/* Any block specific overrides */
</style>
