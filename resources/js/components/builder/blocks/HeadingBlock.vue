<template>
  <component 
    :is="tag"
    class="heading-block"
    :style="headingStyles"
    :class="cssClass"
  >
    <template v-if="!isDynamic">
       <div 
         ref="editableRef"
         contenteditable="true"
         @blur="onTextBlur"
         @input="onTextInput"
         v-html="displayText"
         style="display: inline-block; width: 100%; outline: none;"
       ></div>
    </template>
    <template v-else>
      {{ displayText }}
    </template>
  </component>
</template>

<script setup>
import { computed, inject, ref, watch } from 'vue'
import { 
  getResponsiveValue, 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles 
} from '../core/styleUtils'
import { isDynamicValue, extractTag, getTagLabel, resolveDynamicValue } from '../core/dynamicResolver'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const rawText = computed(() => settings.value.text || 'Your Heading Here')
const tag = computed(() => settings.value.tag || 'h2')

const editableRef = ref(null)

// Dynamic resolution state
const resolvedText = ref(null)
const isResolving = ref(false)

// Check if text is dynamic
const isDynamic = computed(() => isDynamicValue(rawText.value))

// Resolve dynamic text when it changes
watch(rawText, async (newValue) => {
  if (isDynamicValue(newValue)) {
    isResolving.value = true
    resolvedText.value = null
    try {
      const resolved = await resolveDynamicValue(newValue, {
        contentId: builder?.contentId
      })
      resolvedText.value = resolved
    } catch (e) {
      resolvedText.value = getTagLabel(extractTag(newValue))
    } finally {
      isResolving.value = false
    }
  }
}, { immediate: true })

// Display text: resolved value, loading indicator, or raw text
const displayText = computed(() => {
  if (!isDynamic.value) {
    return rawText.value
  }
  
  if (isResolving.value) {
    return '...'
  }
  
  // Show resolved value or fallback to label
  return resolvedText.value || getTagLabel(extractTag(rawText.value))
})

const onTextInput = (e) => {
  // We can handle live updates if needed, but usually on blur is safer for performance
}

const onTextBlur = (e) => {
  const newText = e.target.innerText
  if (newText !== rawText.value) {
    builder.updateModule(props.module.id, {
      settings: { ...settings.value, text: newText }
    })
  }
}

const headingStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  // Custom Typography mapping for Heading
  const typography = getTypographyStyles(settings.value, '', device.value)
  if (settings.value.alignment) {
    typography.textAlign = getResponsiveValue(settings.value, 'alignment', device.value)
  }
  Object.assign(styles, typography)
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')
</script>

<style scoped>
.heading-block {
  margin: 0;
  padding: 0;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
</style>
