<template>
  <div class="faq-block" :style="wrapperStyles">
    <draggable
      :list="module.children || []"
      item-key="id"
      group="faq_item"
      class="faq-container"
      ghost-class="ja-builder-ghost"
    >
      <template #item="{ element: child, index }">
        <ModuleWrapper
          :module="child"
          :index="index"
        />
      </template>
    </draggable>
    
    <!-- Empty State -->
    <div v-if="!module.children || module.children.length === 0" class="empty-faq-placeholder">
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
const device = computed(() => builder?.device || 'desktop')

const openItems = ref([])

// Ensure at least one item is open by default if accordion
if (props.module.children && props.module.children.length > 0) {
    openItems.value = [props.module.children[0].id]
}

const toggleItem = (id) => {
  const allowMultiple = getResponsiveValue(settings.value, 'allowMultiple', device.value)
  if (allowMultiple) {
    if (openItems.value.includes(id)) {
      openItems.value = openItems.value.filter(i => i !== id)
    } else {
      openItems.value = [...openItems.value, id]
    }
  } else {
    openItems.value = openItems.value.includes(id) ? [] : [id]
  }
}

provide('faqState', {
  openItems: computed(() => openItems.value),
  toggleItem,
  parentSettings: settings
})

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
</script>

<style scoped>
.faq-block { width: 100%; }
.faq-item:last-child { border-bottom: none !important; }
.faq-icon--expanded { transform: rotate(180deg); }
</style>
