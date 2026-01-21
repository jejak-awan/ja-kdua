<template>
  <div class="faq-block" :style="wrapperStyles">
    <div class="faq-list" :style="listStyles">
        <div 
            v-for="(item, index) in settings.items || []" 
            :key="index"
            class="faq-item"
            :style="itemStyles"
        >
            <div 
                class="faq-question" 
                :style="questionStyles"
                @click="toggleItem(index)"
            >
                <div class="question-text">{{ item.question }}</div>
                <div class="faq-icon" :style="{ transform: openItems.includes(index) ? 'rotate(180deg)' : 'rotate(0deg)' }">
                    <ChevronDown v-if="settings.layout === 'accordion'" />
                </div>
            </div>
            <div 
                v-if="settings.layout === 'list' || openItems.includes(index)"
                class="faq-answer" 
                :style="answerStyles"
            >
                {{ item.answer }}
            </div>
        </div>
    </div>
    
    <!-- Empty State -->
    <div v-if="!settings.items || settings.items.length === 0" class="empty-faq-placeholder">
        <HelpCircle :size="24" />
        <span>Add FAQ items in settings</span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject, provide } from 'vue'
import draggable from 'vuedraggable'
import { HelpCircle } from 'lucide-vue-next'
import ModuleWrapper from '../canvas/ModuleWrapper.vue'
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
const device = computed(() => builder?.device?.value || 'desktop')

const openItems = ref([])

// Ensure at least one item is open by default if accordion
const toggleItem = (index) => {
    if (settings.value.layout === 'list') return
    
    if (settings.value.allowMultiple) {
        if (openItems.value.includes(index)) {
            openItems.value = openItems.value.filter(i => i !== index)
        } else {
            openItems.value.push(index)
        }
    } else {
        openItems.value = openItems.value.includes(index) ? [] : [index]
    }
}

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

const listStyles = computed(() => ({
    display: 'flex',
    flexDirection: 'column',
    gap: '16px'
}))

const itemStyles = computed(() => ({
    border: `1px solid ${getResponsiveValue(settings.value, 'itemBorderColor', device.value) || '#e0e0e0'}`,
    borderRadius: '8px',
    overflow: 'hidden',
    backgroundColor: '#fff'
}))

const questionStyles = computed(() => {
    const s = getTypographyStyles(settings.value, 'question_', device.value)
    return {
        ...s,
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: '16px',
        cursor: settings.value.layout === 'accordion' ? 'pointer' : 'default',
        userSelect: 'none'
    }
})

const answerStyles = computed(() => {
    const s = getTypographyStyles(settings.value, 'answer_', device.value)
    return {
        ...s,
        padding: '0 16px 16px 16px',
        borderTop: settings.value.layout === 'list' ? 'none' : '1px solid transparent'
    }
})
</script>

<style scoped>
.faq-block { width: 100%; }
.faq-item:last-child { border-bottom: none !important; }
.faq-icon--expanded { transform: rotate(180deg); }
</style>
