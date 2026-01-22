<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="text-block text-content"
        :style="textStyles(settings, blockDevice)"
      >
        <component
          :is="getVal(settings, 'titleTag') || 'h2'"
          v-if="getVal(settings, 'showTitle')"
          class="text-block-title"
          :contenteditable="blockMode === 'edit'"
          @blur="updateTitle($event, settings)"
        >{{ getVal(settings, 'title') }}</component>
        
        <template v-if="blockMode === 'edit'">
          <InlineRichtext
            :model-value="getVal(settings, 'content')"
            @update:model-value="onContentUpdate($event, settings)"
            placeholder="Type your text..."
          />
        </template>
        <template v-else>
          <div v-html="getVal(settings, 'content') || '<p>Your text goes here.</p>'" class="prose max-w-none"></div>
        </template>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal } from '../utils/styleUtils'
import InlineRichtext from '../../components/builder/canvas/InlineRichtext.vue'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)

const textStyles = (settings, device) => {
  return getTypographyStyles(settings, '', device)
}

const updateTitle = (e, settings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, title: e.target.innerText }
  })
}

const onContentUpdate = (newContent, settings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, content: newContent }
  })
}
</script>

<style scoped>
.text-block {
  word-wrap: break-word;
}
.text-block :deep(p) {
  margin: 0 0 1.5em;
  line-height: inherit;
  color: inherit;
}
.text-block :deep(p:last-child) {
  margin-bottom: 0;
}
</style>
