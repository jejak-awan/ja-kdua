<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="quote-block-wrapper"
  >
    <blockquote class="quote-container" :class="[`quote--${quoteStyle}`]">
      <div v-if="quoteStyle === 'classic'" class="quote-icon-wrapper">
        <QuoteIcon class="quote-icon" :style="iconStyles" />
      </div>
      
      <div 
        class="quote-content" 
        :style="contentStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateField('content', $event.target.innerText)"
        v-text="getVal(settings, 'content') || 'Your quote here...'"
      ></div>
      
      <footer v-if="hasAuthor" class="quote-footer">
        <cite class="quote-author-wrapper">
          <span 
            class="quote-author" 
            :style="authorStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('author', $event.target.innerText)"
            v-text="getVal(settings, 'author') || 'Author Name'"
          ></span>
          <span v-if="hasTitle" class="author-title-separator">, </span>
          <span 
            v-if="hasTitle" 
            class="author-title" 
            :style="authorTitleStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateField('authorTitle', $event.target.innerText)"
            v-text="getVal(settings, 'authorTitle')"
          ></span>
        </cite>
      </footer>
    </blockquote>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Quote as QuoteIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const quoteStyle = computed(() => getVal(props.settings, 'quoteStyle') || 'modern')
const hasAuthor = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'author'))
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'authorTitle'))

const contentStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'quote_'),
  textAlign: getVal(props.settings, 'alignment') || 'left',
  outline: 'none'
}))

const authorStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'author_'),
  outline: 'none'
}))

const authorTitleStyles = computed(() => ({
  opacity: 0.7,
  fontStyle: 'normal',
  outline: 'none'
}))

const iconStyles = computed(() => {
  const s = getTypographyStyles(props.settings, 'quote_')
  return {
    color: s.color || 'var(--theme-primary-color, #2059ea)',
    opacity: 0.2
  }
})

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.quote-block-wrapper {
  width: 100%;
}

.quote-container {
  margin: 0;
  position: relative;
  width: 100%;
}

.quote-icon-wrapper {
  margin-bottom: 16px;
}

.quote-icon {
  width: 48px;
  height: 48px;
}

.quote-content {
  font-style: italic;
  line-height: 1.6;
}

.quote--modern {
  border-left: 4px solid var(--theme-primary-color, #2059ea);
  padding-left: 24px;
}

.quote--minimal::before {
  content: '"';
  font-size: 5rem;
  position: absolute;
  left: -20px;
  top: -30px;
  opacity: 0.1;
  font-family: serif;
}

.quote-footer {
  margin-top: 20px;
}

.quote-author-wrapper {
  font-style: normal;
}

.quote-author {
  font-weight: 700;
}

.author-title {
  font-size: 0.9em;
}

.quote-content[contenteditable="true"]:empty:before {
  content: 'Enter quote content...';
  opacity: 0.3;
}
.quote-author[contenteditable="true"]:empty:before {
  content: 'Author Name';
  opacity: 0.3;
}
.author-title[contenteditable="true"]:empty:before {
  content: 'Job Title';
  opacity: 0.3;
}
</style>
