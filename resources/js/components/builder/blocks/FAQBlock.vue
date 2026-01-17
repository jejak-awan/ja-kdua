<template>
  <div class="faq-block" :style="wrapperStyles">
    <div v-for="(item, index) in faqItems" :key="index" class="faq-item" :style="itemStyles">
      <button class="faq-question" :style="questionStyles" @click="toggle(index)">
        <span>{{ item.question }}</span>
        <ChevronDown v-if="isAccordion" class="faq-icon" :class="{ 'faq-icon--expanded': openItems.includes(index) }" :style="iconStyles" />
      </button>
      <div v-show="isOpen(index)" class="faq-answer" :style="answerStyles">
        {{ item.answer }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device || 'desktop')

const openItems = ref([0])
const isAccordion = computed(() => getResponsiveValue(settings.value, 'layout', device.value) !== 'list')

const faqItems = computed(() => {
  return (props.module.children || []).map(child => ({
    question: child.settings.question || 'New Question',
    answer: child.settings.answer || ''
  }))
})

const toggle = (index) => {
  if (!isAccordion.value) return
  if (getResponsiveValue(settings.value, 'allowMultiple', device.value)) {
    if (openItems.value.includes(index)) {
      openItems.value = openItems.value.filter(i => i !== index)
    } else {
      openItems.value = [...openItems.value, index]
    }
  } else {
    openItems.value = openItems.value.includes(index) ? [] : [index]
  }
}

const isOpen = (index) => !isAccordion.value || openItems.value.includes(index)

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const itemStyles = computed(() => {
  const borderColor = getResponsiveValue(settings.value, 'itemBorderColor', device.value) || '#e0e0e0'
  return { 
    borderBottom: `1px solid ${borderColor}` 
  }
})

const questionStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'question_', device.value)
  return {
    ...styles,
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    padding: '16px 0',
    background: 'none',
    border: 'none',
    cursor: 'pointer',
    textAlign: styles.textAlign || 'left'
  }
})

const answerStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'answer_', device.value)
  return { 
    ...styles, 
    padding: '0 0 16px' 
  }
})

const iconStyles = computed(() => {
  const color = getResponsiveValue(settings.value, 'accentColor', device.value) || '#2059ea'
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
.faq-block { width: 100%; }
.faq-item:last-child { border-bottom: none !important; }
.faq-icon--expanded { transform: rotate(180deg); }
</style>
