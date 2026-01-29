<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="accordion-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Accordion'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings, device: blockDevice }">
      <div 
        class="accordion-container mx-auto" 
        :style="containerStyles"
      >
        <Accordion 
          :type="getVal(blockSettings, 'allowMultiple') ? 'multiple' : 'single'" 
          :collapsible="true"
          class="accordion-list flex flex-col"
          :style="{ gap: (getVal(blockSettings, 'gap', blockDevice) || 16) + 'px' }"
        >
          <AccordionItem 
            v-for="(item, index) in items" 
            :key="index"
            :value="`item-${index}`"
            class="accordion-item transition-colors duration-300 border-none"
            :class="[
                getItemClasses(blockSettings, index),
            ]"
            :style="getItemStyles(blockSettings, index)"
          >
            <!-- Header -->
            <AccordionTrigger 
              class="accordion-header w-full flex items-center justify-between text-left focus:outline-none group hover:no-underline"
              :class="headerPadding(blockSettings)"
              @click="toggle(index)"
            >
              <span 
                class="accordion-title font-bold text-lg"
                :class="{ 'text-primary': openIndices.includes(index) }"
                :style="headerTypographyStyles(blockSettings)"
              >
                {{ item.title || 'Question' }}
              </span>
              
              <template #icon>
                <div 
                  class="accordion-icon ml-4 transition-colors duration-300 shrink-0 [&[data-state=open]]:rotate-180"
                  :style="iconStyles(blockSettings)"
                >
                  <LucideIcon :name="getIconName(blockSettings)" class="w-full h-full opacity-60 group-hover:opacity-100" />
                </div>
              </template>
            </AccordionTrigger>

            <!-- Content -->
            <AccordionContent class="accordion-content" :style="contentContainerStyles(blockSettings)">
                <div class="p-6 pt-0">
                    <div class="prose max-w-none text-slate-600 leading-relaxed" :style="contentTypographyStyles(blockSettings)" v-html="item.content || 'Answer goes here...'"></div>
                </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getLayoutStyles, getTypographyStyles } from '../utils/styleUtils'
import type { BlockInstance, BlockProps } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => props.settings || props.module?.settings || {})
const items = computed<any[]>(() => settings.value.items || [])
const openIndices = ref<number[]>([])

const toggle = (index: number) => {
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

const getIconName = (settings: any) => {
    const icon = getVal(settings, 'toggleIcon')
    if (typeof icon === 'string') return icon.replace('lucide:', '')
    return 'ChevronDown'
}

const iconStyles = (settings: any) => {
    const size = parseInt(getVal(settings, 'iconSize')) || 20
    const color = getVal(settings, 'iconColor') || 'currentColor'
    return {
        width: `${size}px`,
        height: `${size}px`,
        color: color
    }
}

const getItemClasses = (settings: any, index: any) => {
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

const getItemStyles = (settings: any, index: any) => {
    const isOpen = openIndices.value.includes(index)
    const bgColor = isOpen 
        ? (getVal(settings, 'openHeaderBackgroundColor') || getVal(settings, 'headerBackgroundColor') || '#f1f5f9')
        : (getVal(settings, 'headerBackgroundColor') || '#f8fafc')
        
    const styles = {
        backgroundColor: bgColor
    }
    
    return styles
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const contentContainerStyles = (settings: any) => ({
    backgroundColor: getVal(settings, 'contentBackgroundColor') || 'transparent'
})

const contentTypographyStyles = (settings: any) => getTypographyStyles(settings, 'content_', props.device)

const headerTypographyStyles = (settings: any) => getTypographyStyles(settings, 'header_', props.device)

const headerPadding = (settings: any) => {
    const variant = getVal(settings, 'variant') || 'simple'
    return variant === 'boxed' ? 'p-6' : 'py-5'
}

</script>

<style scoped>
.accordion-block { width: 100%; }
.accordion-item {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.accordion-item:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
