<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="prose max-w-none" 
        :style="(textStyles(settings, blockDevice) as Record<string, string | number>)"
      >
        <template v-if="blockMode === 'edit'">
          <!-- In edit mode, we use the TiptapEditor component -->
          <TiptapEditor 
            :model-value="getVal<string>(settings, 'content') || ''"
            @update:model-value="onContentUpdate($event, settings)"
          />
        </template>
        <template v-else>
          <!-- In view mode, we render the HTML directly -->
          <div v-html="getVal<string>(settings, 'content') || 'Click to add text content...'"></div>
        </template>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal } from '../utils/styleUtils'
import TiptapEditor from '../../components/editor/TiptapEditor.vue'
import type { BlockProps, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)

const textStyles = (settings: ModuleSettings, device: string) => {
  return (getTypographyStyles(settings, '', device) || {}) as Record<string, string | number>
}

const onContentUpdate = (newContent: string, settings: ModuleSettings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, content: newContent }
  })
}
</script>

<style scoped>
/* Any block specific overrides */
.prose {
  width: 100%;
  max-width: 100%;
}
</style>
