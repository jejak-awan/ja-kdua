<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="quote-block-wrapper"
  >
    <Card class="quote-container relative overflow-hidden bg-white dark:bg-slate-900 border-none shadow-2xl rounded-[40px] p-10 md:p-16 group transition-all duration-700 hover:-translate-y-2">
      <!-- Background Decorative Quote -->
      <div class="absolute -top-10 -left-10 text-slate-100 dark:text-slate-800 transition-transform duration-1000 group-hover:scale-110 group-hover:rotate-12 pointer-events-none">
          <QuoteIcon :size="200" />
      </div>

      <div class="relative z-10 flex flex-col h-full">
          <div class="quote-header mb-10">
              <div class="w-16 h-1 bg-primary rounded-full mb-8 transform origin-left transition-transform duration-700 group-hover:scale-x-150"></div>
          </div>
          
          <div 
            class="quote-content font-black italic tracking-tight leading-[1.2] text-slate-800 dark:text-slate-100 mb-10 transition-colors duration-500 group-hover:text-primary" 
            :style="contentStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('content', $event.target.innerText)"
            v-text="getVal(settings, 'content', currentDevice) || 'Your voice, amplified with style and precision through our premium design system.'"
          ></div>
          
          <footer v-if="hasAuthor" class="quote-footer flex items-center gap-6 mt-auto">
             <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-primary font-bold text-xl uppercase">
                {{ (getVal(settings, 'author', currentDevice) || 'A')[0] }}
             </div>
             <cite class="quote-author-wrapper not-italic flex flex-col">
               <span 
                 class="quote-author font-black text-slate-900 dark:text-white text-lg tracking-tight" 
                 :style="authorStyles"
                 :contenteditable="mode === 'edit'"
                 @blur="updateField('author', $event.target.innerText)"
                 v-text="getVal(settings, 'author', currentDevice) || 'Antigravity AI'"
               ></span>
               <span 
                 v-if="hasTitle" 
                 class="author-title text-sm font-bold text-slate-400 uppercase tracking-widest mt-1" 
                 :style="authorTitleStyles"
                 :contenteditable="mode === 'edit'"
                 @blur="updateField('authorTitle', $event.target.innerText)"
                 v-text="getVal(settings, 'authorTitle', currentDevice) || 'Lead Software Engineer'"
               ></span>
             </cite>
          </footer>
      </div>
    </Card>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Quote as QuoteIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { Card } from '../ui'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean,
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const currentDevice = computed(() => builder?.device?.value || props.device || 'desktop')

const hasAuthor = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'author', currentDevice.value))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'authorTitle', currentDevice.value))

const contentStyles = computed(() => {
    const defaultSize = currentDevice.value === 'mobile' ? '32px' : '48px'
    const styles = getTypographyStyles(props.settings, 'quote_', currentDevice.value)
    return {
        fontSize: styles.fontSize || defaultSize,
        textAlign: getVal(props.settings, 'alignment', currentDevice.value) || 'left',
        ...styles
    }
})

const authorStyles = computed(() => getTypographyStyles(props.settings, 'author_', currentDevice.value))
const authorTitleStyles = computed(() => getTypographyStyles(props.settings, 'author_title_', currentDevice.value))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.id, { [key]: value })
}
</script>

<style scoped>
.quote-block-wrapper { width: 100%; }
.quote-content:focus { outline: none; }
.quote-author:focus { outline: none; }
.author-title:focus { outline: none; }
</style>
