<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div 
        class="faq-block mx-auto w-full transition-all duration-300"
      >
        <Accordion 
          :type="getVal(settings, 'allowMultiple') ? 'multiple' : 'single'" 
          collapsible 
          class="w-full"
          :class="[getVal(settings, 'variant') === 'boxed' ? 'space-y-4' : '']"
        >
          <AccordionItem 
            v-for="(item, index) in items" 
            :key="index" 
            :value="`item-${index}`"
            :class="getItemClasses(settings, index)"
            :style="getItemStyles(settings, index)"
          >
            <AccordionTrigger 
              class="w-full text-left font-bold text-lg hover:no-underline group"
              :class="headerPadding(settings)"
            >
              <template #default>
                 <span 
                    class="faq-question transition-colors group-hover:text-primary"
                  >
                    {{ item.question || 'Question text...' }}
                  </span>
              </template>
              <template #icon>
                  <div 
                    v-if="layout(settings) === 'accordion'"
                    class="faq-icon ml-4 shrink-0 transition-transform duration-200"
                  >
                    <ChevronDown 
                        class="w-5 h-5 opacity-60 group-hover:opacity-100" 
                        :style="{ color: getVal(settings, 'iconColor') || 'currentColor' }"
                    />
                  </div>
              </template>
            </AccordionTrigger>
            <AccordionContent>
              <div 
                  class="text-slate-600 font-medium leading-relaxed max-w-4xl"
                  :contenteditable="mode === 'edit'"
                  @blur="e => updateItemField(index, 'answer', e.target.innerText)"
                  v-text="item.answer || 'Answer text goes here...'"
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
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import { ChevronDown } from 'lucide-vue-next'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])

const layout = (settings) => getVal(settings, 'layout') || 'accordion'

const getItemClasses = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    
    if (variant === 'boxed') {
        return 'rounded-3xl bg-white shadow-xl border border-transparent transition-all duration-300 data-[state=open]:shadow-2xl data-[state=open]:border-primary/10'
    }
    if (variant === 'minimal') {
        return 'border-b border-gray-100'
    }
    return 'border-b border-gray-200'
}

const getItemStyles = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    if (variant === 'boxed') {
        return {
            backgroundColor: getVal(settings, 'activeBgColor') || 'white',
        }
    }
    return {}
}

const headerPadding = (settings) => {
    const variant = getVal(settings, 'variant') || 'boxed'
    return variant === 'boxed' ? 'px-6 py-4' : 'py-6'
}

const updateItemField = (index, key, value) => {
    // Handling update logic
}
</script>

<style scoped>
.faq-block { width: 100%; }
</style>
