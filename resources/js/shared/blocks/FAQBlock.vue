<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :settings="settings"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Frequently Asked Questions'"
  >
    <div 
      class="faq-block mx-auto w-full transition-all duration-300"
      :style="containerStyles"
    >
      <Accordion 
        :type="getVal(settings, 'allowMultiple', device) ? 'multiple' : 'single'" 
        collapsible 
        class="w-full"
        :class="[getVal(settings, 'variant', device) === 'boxed' ? 'space-y-4' : '']"
      >
        <AccordionItem 
          v-for="(item, index) in faqItems" 
          :key="index" 
          :value="`item-${index}`"
          :class="getItemClasses(index as number)"
          :style="getItemStyles(index as number)"
          class="faq-item transition-all duration-300"
        >
          <AccordionTrigger 
            class="w-full text-left font-bold text-lg hover:no-underline group"
            :class="headerPadding"
          >
            <template #default>
               <span 
                  class="faq-question transition-colors group-hover:text-primary"
                  :style="questionStyles"
                >
                  {{ item.question || 'Question text...' }}
                </span>
            </template>
            <template #icon>
                <div 
                  v-if="layoutMode === 'accordion'"
                  class="faq-icon ml-4 shrink-0 transition-transform duration-200"
                >
                  <ChevronDown 
                      class="w-5 h-5 opacity-60 group-hover:opacity-100" 
                      :style="{ color: accentColor }"
                  />
                </div>
            </template>
          </AccordionTrigger>
          <AccordionContent>
            <div 
                class="leading-relaxed max-w-4xl faq-answer"
                :style="answerStyles"
                :contenteditable="mode === 'edit'"
                @blur="e => updateItemField(index as number, 'answer', (e.target as HTMLElement).innerText)"
                v-text="item.answer || 'Answer text goes here...'"
            >
            </div>
          </AccordionContent>
        </AccordionItem>
      </Accordion>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import { ChevronDown } from 'lucide-vue-next'
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

const faqItems = computed(() => settings.value.items || [])

const layoutMode = computed(() => getVal(settings.value, 'layout', device.value) || 'accordion')
const accentColor = computed(() => getVal(settings.value, 'accentColor', device.value) || 'currentColor')

const getItemClasses = (index: number) => {
    const variant = getVal(settings.value, 'variant', device.value) || 'boxed'
    let classes = ''
    
    if (variant === 'boxed') {
        classes = 'rounded-3xl bg-white shadow-xl border border-transparent transition-all duration-300 data-[state=open]:shadow-2xl data-[state=open]:border-primary/10'
    } else if (variant === 'minimal') {
        classes = 'border-b border-gray-100'
    } else {
        classes = 'border-b border-gray-200'
    }
    
    return classes
}

const getItemStyles = (index: number) => {
    const styles: Record<string, any> = {}
    const variant = getVal(settings.value, 'variant', device.value) || 'boxed'
    
    if (variant === 'boxed') {
        styles.backgroundColor = getVal(settings.value, 'activeBgColor', device.value) || 'white'
    }
    
    const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    const itemBorderColor = getVal(settings.value, 'itemBorderColor', device.value)
    if (itemBorderColor) styles.borderColor = itemBorderColor
    
    return styles
}

const headerPadding = computed(() => {
    const variant = getVal(settings.value, 'variant', device.value) || 'boxed'
    return variant === 'boxed' ? 'px-6 py-4' : 'py-6'
})

const containerStyles = computed(() => getLayoutStyles(settings.value, device.value))
const questionStyles = computed(() => getTypographyStyles(settings.value, 'question_', device.value))
const answerStyles = computed(() => getTypographyStyles(settings.value, 'answer_', device.value))

const updateItemField = (index: number, key: string, value: string) => {
    if (props.mode !== 'edit') return
    const newItems = [...faqItems.value]
    newItems[index] = { ...newItems[index], [key]: value }
    builder?.updateModuleSettings(props.module.id, { items: newItems })
}
</script>

<style scoped>
.faq-block { 
  width: 100%; 
}

.faq-item {
  transform: scale(var(--hover-scale, 1));
  filter: brightness(var(--hover-brightness, 100%));
}

.faq-item:hover {
  transform: scale(var(--hover-scale, 1));
  filter: brightness(var(--hover-brightness, 100%));
}

.faq-icon {
  color: var(--accent-color, currentColor);
}
</style>

