<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="text-block text-content"
        :style="[textStyles(settings as ModuleSettings, blockDevice as string), getLayoutStyles(settings as ModuleSettings, blockDevice as string)] as any"
      >
        <component
          :is="getVal<string>(settings as ModuleSettings, 'titleTag') || 'h2'"
          v-if="getVal<boolean>(settings as ModuleSettings, 'showTitle')"
          class="text-block-title"
          :contenteditable="blockMode === 'edit'"
          @blur="updateTitle($event, settings as ModuleSettings)"
        >
{{ getVal<string>(settings, 'title') }}
</component>
        
        <template v-if="blockMode === 'edit'">
          <InlineRichtext
            :model-value="getVal<string>(settings as ModuleSettings, 'content')"
            @update:model-value="onContentUpdate($event, settings as ModuleSettings)"
            placeholder="Type your text..."
          />
        </template>
        <template v-else>
          <div v-html="getVal<string>(settings as ModuleSettings, 'content') || '<p>Your text goes here.</p>'" class="prose max-w-none"></div>
        </template>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal, getLayoutStyles } from '../utils/styleUtils'
import InlineRichtext from '../../components/builder/canvas/InlineRichtext.vue'
import type { BuilderInstance, BlockProps, ModuleSettings } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)

const textStyles = (settings: ModuleSettings, device: string) => {
  const styles: Record<string, string | number> = (getTypographyStyles(settings, '', device) || {}) as Record<string, string | number>
  
  // 1. Text Columns (Flow logic)
  const cols = getVal<number>(settings, 'text_column_count', device)
  if (cols && cols > 1) {
    styles.columnCount = cols
    styles.columnGap = getVal<string | number>(settings, 'text_column_gap', device) || '30px'
    
    const ruleWidth = getVal<number>(settings, 'text_column_rule_width', device)
    if (ruleWidth && ruleWidth > 0) {
      const ruleColor = getVal<string>(settings, 'text_column_rule_color', device) || '#eeeeee'
      const ruleStyle = getVal<string>(settings, 'text_column_rule_style', device) || 'solid'
      styles.columnRule = `${ruleWidth}px ${ruleStyle} ${ruleColor}`
    }
  }

  // 2. Hover Variables
  const hoverColor = getVal<string>(settings, 'hover_text_color', device)
  if (hoverColor) styles['--hover-color'] = hoverColor

  // 3. Drop Cap Variables
  if (getVal<boolean>(settings, 'use_drop_cap', device)) {
    styles['--use-drop-cap'] = 'block'
    const dcColor = getVal<string>(settings, 'drop_cap_color', device)
    const dcSize = getVal<string | number>(settings, 'drop_cap_font_size', device)
    const dcFont = getVal<string>(settings, 'drop_cap_font_family', device)
    
    if (dcColor) styles['--dc-color'] = dcColor
    if (dcSize) styles['--dc-size'] = dcSize
    if (dcFont) styles['--dc-font'] = dcFont
  }

  return styles
}

const updateTitle = (e: FocusEvent, settings: ModuleSettings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, title: (e.target as HTMLElement).innerText }
  })
}

const onContentUpdate = (newContent: string, settings: ModuleSettings) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModule(props.module.id, {
    settings: { ...settings, content: newContent }
  })
}
</script>

<style scoped>
.text-block {
  word-wrap: break-word;
  width: 100%;
  transition: all 0.3s ease;
}

/* Hover Effect */
.text-block:hover {
  color: var(--hover-color) !important;
}

/* Drop Cap Styling */
.text-block:deep(p:first-of-type::first-letter) {
  display: var(--use-drop-cap, none);
  float: left;
  font-size: var(--dc-size, 3em);
  line-height: 0.8;
  margin: 0.1em 0.1em 0.1em 0;
  color: var(--dc-color, inherit);
  font-family: var(--dc-font, inherit);
  font-weight: 700;
}

.text-block:deep(p) {
  margin: 0 0 1.5em;
  line-height: inherit;
  color: inherit;
}
.text-block:deep(p:last-child) {
  margin-bottom: 0;
}
</style>
