<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="icon-list-wrapper transition-colors duration-300"
    :style="cardStyles"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Icon List'"
  >
    <div class="icon-list-container" :style="listStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="icon-list-item group flex items-start gap-4 transition-colors duration-300"
        :style="itemStyles(index as number)"
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
            @blur="updateItemField(index as number, 'text', ($event.target as HTMLElement).innerText)"
            v-text="item.text || item.title || 'List Item'"
          ></div>
          <div 
            v-if="item.description || mode === 'edit'" 
            class="icon-list-desc mt-1 text-sm text-slate-500 leading-relaxed font-medium"
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index as number, 'description', ($event.target as HTMLElement).innerText)"
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

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { LucideIcon } from '@/components/ui';
import { 
    getVal, 
    getLayoutStyles,
    getTypographyStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)
const device = computed(() => builder?.device?.value || 'desktop')

const items = computed(() => settings.value.items || [])
const defaultIcon = computed(() => getVal(settings.value, 'defaultIcon', device.value) || 'check')
const iconSize = computed(() => parseInt(getVal(settings.value, 'iconSize', device.value)) || 20)

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    
    // Interactive states
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const listStyles = computed(() => {
  const styles = getLayoutStyles(settings.value, device.value)
  
  return {
    ...styles,
    display: 'flex',
    flexDirection: (getVal(settings.value, 'layout', device.value) === 'horizontal' ? 'row' : 'column') as any,
    flexWrap: 'wrap' as any,
    gap: `${getVal(settings.value, 'gap', device.value) || 12}px`,
    width: '100%'
  }
})

const itemStyles = (index: number) => {
  const align = getVal(settings.value, 'alignment', device.value) || 'left'
  return {
    justifyContent: align === 'center' ? 'center' : 'flex-start',
    textAlign: align as any,
    flex: getVal(settings.value, 'layout', device.value) === 'horizontal' ? '1 1 auto' : '0 0 auto'
  }
}

const iconWrapperStyles = computed(() => {
  const s: Record<string, any> = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }
  
  const bgColor = getVal(settings.value, 'iconBgColor', device.value)
  if (bgColor && bgColor !== 'transparent') {
    s.backgroundColor = bgColor
    s.padding = '8px'
    const shape = getVal(settings.value, 'iconBackgroundShape', device.value)
    s.borderRadius = shape === 'circle' ? '50%' : shape === 'square' ? '8px' : '0'
  }
  
  return s
})

const iconStyles = computed(() => ({
  color: getVal(settings.value, 'iconColor', device.value) || 'inherit'
}))

const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))

const updateItemField = (index: number, key: string, value: string) => {
    if (props.mode !== 'edit' || !builder) return
    const newItems = [...items.value]
    newItems[index] = { ...newItems[index], [key]: value }
    builder.updateModuleSettings(props.module.id, { items: newItems })
}
</script>

<style scoped>
.author-block {
    width: 100%;
}

.icon-list-wrapper:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}

.icon-list-text { outline: none; transition: color 0.2s ease; }
.icon-list-desc { outline: none; }
.icon-list-desc[contenteditable="true"]:empty:before {
  content: 'Add description...';
  opacity: 0.3;
}
</style>

