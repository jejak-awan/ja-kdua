<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="quote-block-wrapper transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Quote'"
  >
    <Card 
        class="quote-container relative overflow-hidden bg-white dark:bg-slate-900 border-none shadow-2xl rounded-[3rem] p-10 md:p-20 group transition-colors duration-700 hover:-translate-y-3"
        :style="containerStyles"
    >
      <!-- Background Decorative Quote -->
      <div class="absolute -top-12 -left-12 text-slate-100 dark:text-slate-800 transition-colors duration-1000 group-hover:scale-125 group-hover:rotate-12 pointer-events-none">
          <QuoteIcon :size="240" />
      </div>

      <div class="relative z-10 flex flex-col h-full">
          <div class="quote-header mb-12">
              <div class="w-20 h-1.5 bg-primary rounded-full mb-10 transform origin-left transition-transform duration-700 group-hover:scale-x-125"></div>
          </div>
          
          <div 
            class="quote-content font-black italic tracking-tight leading-tight text-slate-900 dark:text-white mb-12 transition-colors duration-500 group-hover:text-primary decoration-primary/20 decoration-8 underline-offset-8" 
            :style="contentStyles"
            :contenteditable="mode === 'edit'"
            @blur="(e: any) => updateField('content', (e.target as HTMLElement).innerText)"
            v-text="getVal(settings, 'content', device) || 'Your voice, amplified with style and precision through our premium design system.'"
          ></div>
          
          <footer v-if="hasAuthor" class="quote-footer flex items-center gap-8 mt-auto">
             <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-primary font-black text-2xl uppercase shadow-sm border border-slate-100 dark:border-slate-700 transition-transform group-hover:rotate-6">
                {{ (getVal(settings, 'author', device) || 'A')[0] }}
             </div>
             <cite class="quote-author-wrapper not-italic flex flex-col gap-1">
               <span 
                 class="quote-author font-black text-slate-900 dark:text-white text-xl tracking-tight leading-none" 
                 :style="authorStyles"
                 :contenteditable="mode === 'edit'"
                 @blur="(e: any) => updateField('author', (e.target as HTMLElement).innerText)"
                 v-text="getVal(settings, 'author', device) || 'Creative Director'"
               ></span>
               <span 
                 v-if="hasTitle" 
                 class="author-title text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mt-1" 
                 :style="authorTitleStyles"
                 :contenteditable="mode === 'edit'"
                 @blur="(e: any) => updateField('authorTitle', (e.target as HTMLElement).innerText)"
                 v-text="getVal(settings, 'authorTitle', device) || 'Industrial Design Studio'"
               ></span>
             </cite>
          </footer>
      </div>
    </Card>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import QuoteIcon from 'lucide-vue-next/dist/esm/icons/quote.js';import BaseBlock from '../components/BaseBlock.vue'
import { Card } from '../ui'
import { 
  getVal, 
  getLayoutStyles, 
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const hasAuthor = computed(() => props.mode === 'edit' || !!getVal(settings.value, 'author', device.value))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(settings.value, 'authorTitle', device.value))

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const contentStyles = computed(() => {
    const defaultSize = device.value === 'mobile' ? '32px' : '48px'
    const styles = getTypographyStyles(settings.value, 'quote_', device.value)
    return {
        fontSize: styles.fontSize || defaultSize,
        textAlign: (getVal(settings.value, 'alignment', device.value) || 'left') as any,
        ...styles
    }
})

const authorStyles = computed(() => getTypographyStyles(settings.value, 'author_', device.value))
const authorTitleStyles = computed(() => getTypographyStyles(settings.value, 'author_title_', device.value))

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.quote-block-wrapper { width: 100%; }
.quote-content:focus { outline: none; }
.quote-author:focus { outline: none; }
.author-title:focus { outline: none; }
</style>

