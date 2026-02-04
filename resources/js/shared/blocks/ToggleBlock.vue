<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="toggle-block w-full transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Toggle'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div class="toggle-container w-full" :style="containerStyles">
        <Accordion 
          type="single" 
          collapsible 
          :default-value="defaultOpenValue ? 'item-1' : ''"
          class="w-full"
        >
          <AccordionItem value="item-1" class="border-none toggle-item">
            <AccordionTrigger 
              class="toggle-header flex items-center justify-between py-4 px-6 rounded-xl hover:no-underline transition-colors"
              :class="[iconPositionValue === 'left' ? 'flex-row-reverse' : '']"
              :style="headerStyles"
            >
              <template #default>
                <span class="toggle-title font-bold text-lg" :style="titleStyles">{{ titleValue }}</span>
              </template>
              <template #icon>
                <div class="toggle-icon-wrapper transition-transform duration-300 [&[data-state=open]]:rotate-180">
                    <LucideIcon 
                        v-if="toggleIconValue !== 'none'"
                        :name="computedIconName"
                        class="w-5 h-5 shrink-0"
                        :style="{ color: (iconColorValue as string) }"
                    />
                </div>
              </template>
            </AccordionTrigger>
            
            <AccordionContent class="mt-1">
              <div 
                class="toggle-content p-6 rounded-xl prose max-w-none text-slate-600 font-medium leading-relaxed" 
                :style="contentStyles"
                v-html="getVal<string>(blockSettings as ModuleSettings, 'content') || 'Toggle content goes here...'"
              >
              </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import { LucideIcon } from '@/components/ui';
import { getVal, getLayoutStyles, getTypographyStyles } from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module?.settings || {}) as ModuleSettings)

const titleValue = computed(() => getVal<string>(settings.value, 'title', props.device) || 'Toggle Title')
const toggleIconValue = computed(() => getVal<string>(settings.value, 'toggleIcon', props.device) || 'chevron')
const iconPositionValue = computed(() => getVal<string>(settings.value, 'iconPosition', props.device) || 'right')
const iconColorValue = computed(() => getVal<string>(settings.value, 'iconColor', props.device) || 'currentColor')
const defaultOpenValue = computed(() => !!getVal<boolean>(settings.value, 'defaultOpen', props.device))

const headerStyles = computed(() => {
  const styles: Record<string, string | number> = {
    backgroundColor: getVal<string>(settings.value, 'headerBackgroundColor', props.device) || '#f8fafc',
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'header_', props.device))
  const padding = getVal<string | number>(settings.value, 'headerPadding', props.device)
  if (padding) styles.padding = padding
  return styles
})

const contentStyles = computed(() => {
  const styles: Record<string, string | number> = {
    backgroundColor: getVal<string>(settings.value, 'contentBackgroundColor', props.device) || '#ffffff',
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', props.device))
  const padding = getVal<string | number>(settings.value, 'contentPadding', props.device)
  if (padding) styles.padding = padding
  return styles
})

const computedIconName = computed(() => {
    if (toggleIconValue.value === 'plus') return 'Plus'
    return 'ChevronDown'
})

const titleStyles = computed(() => (getTypographyStyles(settings.value, 'header_', props.device) || {}) as Record<string, string | number>)

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1.05
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return (getLayoutStyles(settings.value, props.device) || {}) as Record<string, string | number>
})
</script>

<style scoped>
.toggle-block { width: 100%; }
.toggle-item {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.toggle-item:hover {
    transform: scale(var(--hover-scale, 1.05));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
