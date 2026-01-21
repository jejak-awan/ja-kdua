<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="icon-list-wrapper"
  >
    <div class="icon-list-container" :style="listStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="icon-list-item"
        :style="itemStyles"
      >
        <div class="icon-wrapper" :style="iconWrapperStyles">
          <LucideIcon 
            :name="item.icon || defaultIcon" 
            :size="iconSize" 
            :style="iconStyles"
          />
        </div>
        <div class="icon-list-content">
          <div 
            class="icon-list-text" 
            :style="textStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateItemText(index, $event.target.innerText)"
            v-text="item.text || item.title || 'List Item'"
          ></div>
          <div 
            v-if="item.description || mode === 'edit'" 
            class="icon-list-desc"
            :contenteditable="mode === 'edit'"
            @blur="updateItemDesc(index, $event.target.innerText)"
            v-text="item.description"
          ></div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="items.length === 0 && mode === 'edit'" class="empty-list-placeholder">
        <LucideIcon name="List" :size="24" class="placeholder-icon" />
        <span>Add items in settings</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const items = computed(() => props.settings.items || [])
const defaultIcon = computed(() => getVal(props.settings, 'defaultIcon') || 'check')
const iconSize = computed(() => parseInt(getVal(props.settings, 'iconSize')) || 20)

const listStyles = computed(() => ({
  display: 'flex',
  flexDirection: getVal(props.settings, 'layout') === 'horizontal' ? 'row' : 'column',
  flexWrap: 'wrap',
  gap: `${getVal(props.settings, 'gap') || 12}px`
}))

const itemStyles = computed(() => {
  const align = getVal(props.settings, 'alignment') || 'left'
  return {
    display: 'flex',
    alignItems: 'flex-start',
    gap: '12px',
    justifyContent: align === 'center' ? 'center' : 'flex-start',
    textAlign: align,
    flex: getVal(props.settings, 'layout') === 'horizontal' ? '1 1 auto' : '0 0 auto'
  }
})

const iconWrapperStyles = computed(() => {
  const s = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0
  }
  
  const bgColor = getVal(props.settings, 'iconBgColor')
  if (bgColor && bgColor !== 'transparent') {
    s.backgroundColor = bgColor
    s.padding = '8px'
    const shape = getVal(props.settings, 'iconBackgroundShape')
    s.borderRadius = shape === 'circle' ? '50%' : shape === 'square' ? '4px' : '0'
  }
  
  return s
})

const iconStyles = computed(() => ({
  color: getVal(props.settings, 'iconColor') || 'inherit'
}))

const textStyles = computed(() => getTypographyStyles(props.settings, 'text_'))

const updateItemText = (index, value) => {
  if (props.mode !== 'edit' || !builder) return
  const newItems = [...items.value]
  newItems[index] = { ...newItems[index], text: value }
  builder.updateModuleSettings(props.id, { items: newItems })
}

const updateItemDesc = (index, value) => {
  if (props.mode !== 'edit' || !builder) return
  const newItems = [...items.value]
  newItems[index] = { ...newItems[index], description: value }
  builder.updateModuleSettings(props.id, { items: newItems })
}
</script>

<style scoped>
.icon-list-container {
  width: 100%;
}

.icon-list-item {
  width: 100%;
}

.icon-wrapper {
  transition: all 0.2s ease;
}

.icon-list-content {
  flex: 1;
}

.icon-list-text {
  font-weight: 600;
  outline: none;
}

.icon-list-desc {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 4px;
  outline: none;
}

.icon-list-desc[contenteditable="true"]:empty:before {
  content: 'Add description...';
  opacity: 0.3;
}

.empty-list-placeholder {
  padding: 24px;
  text-align: center;
  color: #94a3b8;
  border: 2px dashed #e2e8f0;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.placeholder-icon {
  opacity: 0.5;
}
</style>
