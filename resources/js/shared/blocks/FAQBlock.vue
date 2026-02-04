<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="faq-block transition-colors duration-300 group"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'FAQ'"
    :style="cardStyles"
  >
    <div 
        class="faq-container relative w-full" 
        :style="containerStyles"
    >
        <div class="space-y-4">
                <Accordion type="single" collapsible class="w-full">
                <AccordionItem 
                    v-for="(item, index) in faqItems" 
                    :key="index" 
                    :value="`item-${index}`"
                    class="border border-slate-200 dark:border-slate-800 rounded-2xl px-6 mb-4 overflow-hidden bg-white dark:bg-slate-900 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-300"
                >
                    <AccordionTrigger class="py-6 hover:no-underline group/trigger">
                        <div class="flex items-center gap-4 text-left">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-black text-xs transition-colors group-hover/trigger:bg-primary group-hover/trigger:text-white">
                                {{ index + 1 }}
                            </div>
                            <span class="text-lg font-black tracking-tight leading-tight group-hover/trigger:text-primary transition-colors">
                                {{ (item as Record<string, any>).question }}
                            </span>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent class="pb-6">
                        <div 
                            class="prose prose-slate dark:prose-invert max-w-none text-slate-500 dark:text-slate-400 font-medium leading-relaxed pl-12"
                            v-html="(item as Record<string, any>).answer"
                        ></div>
                    </AccordionContent>
                </AccordionItem>
                </Accordion>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '../ui'
import { 
    getVal, 
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const faqItems = computed(() => (settings.value.items as unknown[]) || [
  { question: 'Example Question 1', answer: 'This is the answer for the first question.' },
  { question: 'Example Question 2', answer: 'This is the answer for the second question.' }
])

const cardStyles = computed((): CSSProperties => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles as CSSProperties
})

const containerStyles = computed((): CSSProperties => {
    const layoutStyles = getLayoutStyles(settings.value, props.device)
    return { 
        ...layoutStyles,
        width: '100%'
    } as CSSProperties
})
</script>

<style scoped>
.faq-block { width: 100%; position: relative; }
</style>
