<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="accordion-block mx-auto" 
        :class="[
            getVal(settings, 'width', blockDevice) || 'max-w-4xl',
            getVal(settings, 'padding', blockDevice) || 'py-20'
        ]"
      >
        <Accordion 
          :type="getVal(settings, 'allowMultiple') ? 'multiple' : 'single'" 
          :collapsible="true"
          class="accordion-list flex flex-col"
          :style="{ gap: (getVal(settings, 'gap', blockDevice) || 16) + 'px' }"
        >
          <AccordionItem 
            v-for="(item, index) in items" 
            :key="index"
            :value="`item-${index}`"
            class="accordion-item transition-all duration-300 border-none"
            :class="[
                getItemClasses(settings, index),
            ]"
            :style="getItemStyles(settings, index)"
          >
            <!-- Header -->
            <AccordionTrigger 
              class="accordion-header w-full flex items-center justify-between text-left focus:outline-none group hover:no-underline"
              :class="headerPadding(settings)"
              @click="toggle(index)"
            >
              <span 
                class="accordion-title font-bold text-lg"
                :class="{ 'text-primary': openIndices.includes(index) }"
              >
                {{ item.title || 'Question' }}
              </span>
              
              <template #icon>
                <div 
                  class="accordion-icon ml-4 transition-all duration-300 shrink-0 [&[data-state=open]]:rotate-180"
                  :style="iconStyles(settings)"
                >
                  <LucideIcon :name="getIconName(settings)" class="w-full h-full opacity-60 group-hover:opacity-100" />
                </div>
              </template>
            </AccordionTrigger>

            <!-- Content -->
            <AccordionContent class="accordion-content" :style="contentContainerStyles(settings)">
                <div class="p-6 pt-0">
                    <div class="prose max-w-none text-slate-600 leading-relaxed" :style="contentTypographyStyles(settings)" v-html="item.content || 'Answer goes here...'"></div>
                </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])
const openIndices = ref([])

const toggle = (index) => {
    if (getVal(settings.value, 'allowMultiple')) {
        if (openIndices.value.includes(index)) {
             openIndices.value = openIndices.value.filter(i => i !== index)
        } else {
             openIndices.value.push(index)
        }
    } else {
        openIndices.value = openIndices.value.includes(index) ? [] : [index]
    }
}

const getIconName = (settings) => {
    const icon = getVal(settings, 'toggleIcon')
    if (typeof icon === 'string') return icon.replace('lucide:', '')
    return 'ChevronDown'
}

const iconStyles = (settings) => {
    const size = parseInt(getVal(settings, 'iconSize')) || 20
    const color = getVal(settings, 'iconColor') || 'currentColor'
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: color
    }
}

const getItemClasses = (settings, index) => {
    const variant = getVal(settings, 'variant') || 'simple'
    const isOpen = openIndices.value.includes(index)
    
    if (variant === 'boxed') {
        return 'border rounded-xl bg-white overflow-hidden ' + (isOpen ? 'shadow-sm border-gray-200' : 'border-transparent shadow-sm')
    }
    if (variant === 'minimal') {
        return 'border-b border-gray-100 last:border-0'
    }
    // simple
    return 'border-b border-gray-200 last:border-0'
}

const getItemStyles = (settings, index) => {
    const isOpen = openIndices.value.includes(index)
    const bgColor = isOpen 
        ? (getVal(settings, 'openHeaderBackgroundColor') || getVal(settings, 'headerBackgroundColor') || '#f1f5f9')
        : (getVal(settings, 'headerBackgroundColor') || '#f8fafc')
        
    const styles = {
        backgroundColor: bgColor
    }
    
    // Apply header typography to the outer item if needed, but usually we apply to specific span
    return styles
}

const contentContainerStyles = (settings) => ({
    backgroundColor: getVal(settings, 'contentBackgroundColor') || 'transparent'
})

const contentTypographyStyles = (settings) => getTypographyStyles(settings, 'content_', props.device)

const headerTypographyStyles = (settings) => getTypographyStyles(settings, 'header_', props.device)

const headerPadding = (settings) => {
    const variant = getVal(settings, 'variant') || 'simple'
    return variant === 'boxed' ? 'p-6' : 'py-5'
}

</script>

<style scoped>
.accordion-block { width: 100%; }
</style>
