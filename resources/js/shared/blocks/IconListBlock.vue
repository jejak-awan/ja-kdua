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
        class="icon-list-item group flex items-start gap-4"
        :style="itemStyles"
      >
        <div class="icon-wrapper shrink-0 transition-transform duration-300 group-hover:scale-110" :style="iconWrapperStyles">
          <LucideIcon 
            :name="item.icon || defaultIcon" 
            :size="iconSize" 
            :style="iconStyles"
          />
        </div>
        <div class="icon-list-content flex-1">
          <div 
            class="icon-list-text font-bold" 
            :style="textStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateItemText(index, $event.target.innerText)"
            v-text="item.text || item.title || 'List Item'"
          ></div>
          <div 
            v-if="item.description || mode === 'edit'" 
            class="icon-list-desc mt-1 text-sm text-slate-500 leading-relaxed font-medium"
            :contenteditable="mode === 'edit'"
            @blur="updateItemDesc(index, $event.target.innerText)"
            v-text="item.description"
          ></div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="items.length === 0 && mode === 'edit'" class="empty-list-placeholder p-8 text-center border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center gap-3 text-slate-400">
        <LucideIcon name="List" :size="24" class="opacity-30" />
        <span class="font-bold">Add items in settings</span>
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
  isPreview: Boolean,
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')

const items = computed(() => props.settings.items || [])
const defaultIcon = computed(() => getVal(props.settings, 'defaultIcon', currentDevice.value) || 'check')
const iconSize = computed(() => parseInt(getVal(props.settings, 'iconSize', currentDevice.value)) || 20)

const listStyles = computed(() => ({
  display: 'flex',
  flexDirection: getVal(props.settings, 'layout', currentDevice.value) === 'horizontal' ? 'row' : 'column',
  flexWrap: 'wrap',
  gap: `${getVal(props.settings, 'gap', currentDevice.value) || 12}px`,
  width: '100%'
}))

const itemStyles = computed(() => {
  const align = getVal(props.settings, 'alignment', currentDevice.value) || 'left'
  return {
    justifyContent: align === 'center' ? 'center' : 'flex-start',
    textAlign: align,
    flex: getVal(props.settings, 'layout', currentDevice.value) === 'horizontal' ? '1 1 auto' : '0 0 auto'
  }
})

const iconWrapperStyles = computed(() => {
  const s = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }
  
  const bgColor = getVal(props.settings, 'iconBgColor', currentDevice.value)
  if (bgColor && bgColor !== 'transparent') {
    s.backgroundColor = bgColor
    s.padding = '8px'
    const shape = getVal(props.settings, 'iconBackgroundShape', currentDevice.value)
    s.borderRadius = shape === 'circle' ? '50%' : shape === 'square' ? '8px' : '0'
  }
  
  return s
})

const iconStyles = computed(() => ({
  color: getVal(props.settings, 'iconColor', currentDevice.value) || 'inherit'
}))

const textStyles = computed(() => getTypographyStyles(props.settings, 'text_', currentDevice.value))

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
.icon-list-wrapper { width: 100%; }
.icon-list-text { outline: none; transition: color 0.2s ease; }
.icon-list-desc { outline: none; }
.icon-list-desc[contenteditable="true"]:empty:before {
  content: 'Add description...';
  opacity: 0.3;
}
</style>
