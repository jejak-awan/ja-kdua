<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="accordion-block py-12"
  >
    <div class="accordion-container container mx-auto px-6" :style="containerStyles">
        <Accordion type="single" collapsible class="w-full">
            <AccordionItem 
                v-for="(item, index) in items" 
                :key="index" 
                :value="`item-${index}`"
                class="border-b border-slate-100 dark:border-slate-800"
            >
                <AccordionTrigger class="py-6 hover:no-underline group">
                    <span class="text-xl font-black tracking-tighter group-hover:text-primary transition-colors">{{ (item as Record<string, any>).title }}</span>
                </AccordionTrigger>
                <AccordionContent class="pb-6">
                    <div class="prose prose-slate dark:prose-invert max-w-none text-slate-500 font-medium leading-relaxed">
                        {{ (item as Record<string, any>).content }}
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
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

const items = computed(() => (settings.value.items as unknown[]) || [
  { title: 'Feature Alpha', content: 'Detailed breakdown of the first core capability.' },
  { title: 'Module Beta', content: 'How our secondary systems interface with your workflow.' }
])

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.accordion-block { width: 100%; position: relative; }
</style>
