<template>
  <div class="faq-item-block" :style="itemStyles">
    <button class="faq-question" :style="questionStyles" @click="toggle">
      <span 
        class="faq-question-text"
        :contenteditable="builder?.isEditing"
        @blur="onQuestionBlur"
        @click.stop
      >{{ question }}</span>
      <ChevronDown 
        v-if="isAccordion" 
        class="faq-icon" 
        :class="{ 'faq-icon--expanded': isOpen }" 
        :style="iconStyles" 
      />
    </button>
    <div v-show="isOpen" class="faq-answer" :style="answerStyles">
      <InlineRichtext 
        :model-value="answer"
        @update:modelValue="onAnswerUpdate"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import { 
  getTypographyStyles, 
  getResponsiveValue 
} from '../core/styleUtils'
import InlineRichtext from '../canvas/InlineRichtext.vue'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')
const settings = computed(() => props.module.settings || {})

const faqState = inject('faqState', {
  openItems: computed(() => []),
  toggleItem: () => {},
  parentSettings: computed(() => ({}))
})

const parentSettings = computed(() => faqState.parentSettings.value || {})
const isOpen = computed(() => (faqState.openItems.value || []).includes(props.module.id) || !isAccordion.value)
const isAccordion = computed(() => getResponsiveValue(parentSettings.value, 'layout', device.value) !== 'list')

const question = computed(() => getResponsiveValue(settings.value, 'question', device.value) || 'New Question')
const answer = computed({
  get: () => getResponsiveValue(settings.value, 'answer', device.value) || '',
  set: (val) => updateResponsiveField('answer', val)
})

const toggle = () => {
    if (isAccordion.value) {
        faqState.toggleItem(props.module.id)
    }
}

const onQuestionBlur = (e) => {
    updateResponsiveField('question', e.target.innerText)
}

const onAnswerUpdate = (val) => {
    updateResponsiveField('answer', val)
}

const updateResponsiveField = (fieldName, value) => {
    const current = settings.value[fieldName]
    let newValue
    if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
        newValue = { ...current, [device.value]: value }
    } else {
        newValue = { [device.value]: value }
    }
    builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}

const itemStyles = computed(() => {
  const borderColor = getResponsiveValue(parentSettings.value, 'itemBorderColor', device.value) || '#e0e0e0'
  return { 
    borderBottom: `1px solid ${borderColor}` 
  }
})

const questionStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'question_', device.value)
  return {
    ...styles,
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    padding: '16px 0',
    background: 'none',
    border: 'none',
    cursor: isAccordion.value ? 'pointer' : 'default',
    textAlign: styles.textAlign || 'left'
  }
})

const answerStyles = computed(() => {
  const styles = getTypographyStyles(parentSettings.value, 'answer_', device.value)
  return { 
    ...styles, 
    padding: '0 0 16px' 
  }
})

const iconStyles = computed(() => {
  const color = getResponsiveValue(parentSettings.value, 'accentColor', device.value) || '#2059ea'
  return { 
    color: color,
    width: '20px',
    height: '20px',
    transition: 'transform 0.2s',
    flexShrink: 0
  }
})
</script>

<style scoped>
.faq-item-block { width: 100%; }
.faq-item-block:last-child { border-bottom: none !important; }
.faq-icon--expanded { transform: rotate(180deg); }
.faq-question-text { flex: 1; outline: none; }
</style>
