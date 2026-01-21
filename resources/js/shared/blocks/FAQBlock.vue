<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="faq-block-wrapper"
  >
    <div class="faq-container" :style="containerStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="faq-item"
        :class="{ 'faq-item--open': openItems.includes(index) }"
        :style="itemStyles"
      >
        <div 
          class="faq-question" 
          :style="questionStyles"
          @click="toggleItem(index)"
        >
          <div 
            class="question-text"
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index, 'question', $event.target.innerText)"
            @click.stop
            v-text="item.question || 'Question text...'"
          ></div>
          <div v-if="isAccordion" class="faq-icon-wrapper" :style="iconStyles">
            <ChevronDown class="faq-chevron" />
          </div>
        </div>
        
        <div 
          v-if="!isAccordion || openItems.includes(index)"
          class="faq-answer-wrapper"
          :class="{ 'faq-answer--animating': isAccordion }"
        >
          <div 
            class="faq-answer" 
            :style="answerStyles"
            :contenteditable="mode === 'edit'"
            @blur="updateItemField(index, 'answer', $event.target.innerText)"
            v-text="item.answer || 'Answer text goes here...'"
          ></div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="items.length === 0 && mode === 'edit'" class="empty-faq-placeholder">
        <HelpCircle :size="24" class="placeholder-icon" />
        <span>Add FAQ items in settings</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { ChevronDown, HelpCircle } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)
const openItems = ref([])

const items = computed(() => props.settings.items || [])
const isAccordion = computed(() => getVal(props.settings, 'layout') !== 'list')
const allowMultiple = computed(() => getVal(props.settings, 'allowMultiple') === true)

const containerStyles = computed(() => ({
  display: 'flex',
  flexDirection: 'column',
  gap: `${getVal(props.settings, 'gap') || 16}px`,
  width: '100%'
}))

const itemStyles = computed(() => ({
  border: `1px solid ${getVal(props.settings, 'itemBorderColor') || '#e2e8f0'}`,
  borderRadius: `${getVal(props.settings, 'itemBorderRadius') || 8}px`,
  overflow: 'hidden',
  backgroundColor: getVal(props.settings, 'itemBgColor') || '#ffffff',
  transition: 'all 0.3s ease'
}))

const questionStyles = computed(() => {
  const s = getTypographyStyles(props.settings, 'question_')
  return {
    ...s,
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: '16px 20px',
    cursor: isAccordion.value ? 'pointer' : 'default',
    userSelect: 'none'
  }
})

const answerStyles = computed(() => {
  const s = getTypographyStyles(props.settings, 'answer_')
  return {
    ...s,
    padding: '0 20px 20px 20px',
    lineHeight: '1.6',
    opacity: 0.9
  }
})

const iconStyles = computed(() => ({
  color: getVal(props.settings, 'iconColor') || 'inherit',
  transition: 'transform 0.3s ease'
}))

const toggleItem = (index) => {
  if (!isAccordion.value) return
  
  if (allowMultiple.value) {
    if (openItems.value.includes(index)) {
      openItems.value = openItems.value.filter(i => i !== index)
    } else {
      openItems.value.push(index)
    }
  } else {
    openItems.value = openItems.value.includes(index) ? [] : [index]
  }
}

const updateItemField = (index, key, value) => {
  if (props.mode !== 'edit' || !builder) return
  const newItems = [...items.value]
  newItems[index] = { ...newItems[index], [key]: value }
  builder.updateModuleSettings(props.id, { items: newItems })
}

// Initial state
if (isAccordion.value && items.value.length > 0 && getVal(props.settings, 'openFirst') !== false) {
    openItems.value = [0]
}
</script>

<style scoped>
.faq-block-wrapper {
  width: 100%;
}

.faq-item--open {
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.question-text {
  flex: 1;
  font-weight: 600;
  outline: none;
}

.faq-icon-wrapper {
  margin-left: 12px;
}

.faq-chevron {
  width: 20px;
  height: 20px;
  transition: transform 0.3s ease;
}

.faq-item--open .faq-chevron {
  transform: rotate(180deg);
}

.faq-answer-wrapper {
  overflow: hidden;
}

.faq-answer {
  outline: none;
}

.faq-answer--animating {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.empty-faq-placeholder {
  padding: 32px;
  text-align: center;
  color: #94a3b8;
  border: 2px dashed #e2e8f0;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.placeholder-icon {
  opacity: 0.5;
}
</style>
