<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="toggle-block w-full">
        <Accordion 
          type="single" 
          collapsible 
          :defaultValue="defaultOpenValue ? 'item-1' : ''"
          class="w-full"
        >
          <AccordionItem value="item-1" class="border-none">
            <AccordionTrigger 
              class="toggle-header flex items-center justify-between py-4 px-6 rounded-xl hover:no-underline transition-all duration-300"
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
                        :style="{ color: iconColorValue }"
                    />
                </div>
              </template>
            </AccordionTrigger>
            
            <AccordionContent class="mt-1">
              <div 
                class="toggle-content p-6 rounded-xl prose max-w-none text-slate-600 font-medium leading-relaxed" 
                :style="contentStyles"
                v-html="settings.content || 'Toggle content goes here...'"
              >
              </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module?.settings || {})

const titleValue = computed(() => getVal(settings.value, 'title', props.device) || 'Toggle Title')
const toggleIconValue = computed(() => getVal(settings.value, 'toggleIcon', props.device) || 'chevron')
const iconPositionValue = computed(() => getVal(settings.value, 'iconPosition', props.device) || 'right')
const iconColorValue = computed(() => getVal(settings.value, 'iconColor', props.device) || 'currentColor')
const defaultOpenValue = computed(() => !!getVal(settings.value, 'defaultOpen', props.device))

const headerStyles = computed(() => {
  const styles = {
    backgroundColor: getVal(settings.value, 'headerBackgroundColor', props.device) || '#f8fafc',
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'header_', props.device))
  return styles
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: getVal(settings.value, 'contentBackgroundColor', props.device) || '#ffffff',
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', props.device))
  return styles
})

const computedIconName = computed(() => {
    if (toggleIconValue.value === 'plus') return 'Plus'
    return 'ChevronDown'
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'header_', props.device))
const contentTypographyStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device))
</script>

<style scoped>
.toggle-block { width: 100%; }
</style>
