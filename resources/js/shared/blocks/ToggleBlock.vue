<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="toggle-block" :style="toggleBlockStyles">
        <!-- Header -->
        <button 
          class="toggle-header"
          :class="{ 'toggle-header--open': isOpen }"
          :style="headerStyles"
          @click="toggleOpen"
          type="button"
        >
          <LucideIcon 
            name="ChevronDown"
            v-if="toggleIconValue !== 'none' && iconPositionValue === 'left'"
            class="toggle-icon toggle-icon--left"
            :class="{ 'toggle-icon--rotated': isOpen }"
            :style="iconStyles"
          />
          <span class="toggle-title" :style="titleStyles">{{ titleValue }}</span>
          <LucideIcon 
            name="ChevronDown"
            v-if="toggleIconValue !== 'none' && iconPositionValue === 'right'"
            class="toggle-icon"
            :class="{ 'toggle-icon--rotated': isOpen }"
            :style="iconStyles"
          />
        </button>
        
        <!-- Content -->
        <div v-show="isOpen" class="toggle-content" :style="contentStyles">
          <div v-html="settings.content" />
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})

const titleValue = computed(() => getVal(settings.value, 'title', props.device) || 'Toggle Title')
const toggleIconValue = computed(() => getVal(settings.value, 'toggleIcon', props.device) || 'chevron')
const iconPositionValue = computed(() => getVal(settings.value, 'iconPosition', props.device) || 'right')
const iconColorValue = computed(() => getVal(settings.value, 'iconColor', props.device) || '#333333')
const defaultOpenValue = computed(() => getVal(settings.value, 'defaultOpen', props.device))

const isOpen = ref(!!defaultOpenValue.value)

watch(() => defaultOpenValue.value, (newVal) => {
  isOpen.value = !!newVal
})

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const toggleBlockStyles = computed(() => {
  return { width: '100%', overflow: 'hidden' }
})

const headerStyles = computed(() => {
  const styles = {
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    border: 'none',
    cursor: 'pointer',
    backgroundColor: getVal(settings.value, 'headerBackgroundColor', props.device) || '#f5f5f5',
    padding: '16px 20px',
    textAlign: 'left',
    transition: 'background-color 0.2s ease'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'header_', props.device))
  return styles
})

const contentStyles = computed(() => {
  const styles = {
    backgroundColor: getVal(settings.value, 'contentBackgroundColor', props.device) || '#ffffff',
    borderTop: '1px solid #e0e0e0',
    padding: '20px'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', props.device))
  return styles
})

const iconStyles = computed(() => ({
  color: iconColorValue.value,
  width: '20px',
  height: '20px',
  transition: 'transform 0.2s ease'
}))

const titleStyles = computed(() => ({}))
</script>

<style scoped>
.toggle-block { width: 100%; }
.toggle-header:hover { opacity: 0.9; }
.toggle-icon--left { margin-right: 12px; }
.toggle-icon--rotated { transform: rotate(180deg); }
.toggle-content { animation: slideDown 0.2s ease; }

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
