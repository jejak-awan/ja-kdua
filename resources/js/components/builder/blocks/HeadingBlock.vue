<template>
  <div class="heading-wrapper" :style="wrapperStyles">
    <component 
        :is="tag"
        class="heading-block"
        :style="headingStyles"
        :class="[cssClass, sizeClass, decorationClass]"
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

    <div v-if="subtitle || builder?.isEditing" class="heading-subtitle" :class="subtitleSizeClass" :style="subtitleStyles">
        <div 
            contenteditable="true"
            @blur="onSubtitleBlur"
            v-html="subtitle || (builder?.isEditing ? 'Add a subtitle...' : '')"
            style="display: inline-block; width: 100%; outline: none;"
        ></div>
    </div>
  </div>
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
const subtitle = computed(() => settings.value.subtitle || '')
const tag = computed(() => settings.value.tag || 'h2')
const size = computed(() => settings.value.size || 'large')
const decoration = computed(() => settings.value.decoration || 'none')

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

const onSubtitleBlur = (e) => {
  const newSubtitle = e.target.innerText
  if (newSubtitle !== subtitle.value && newSubtitle !== 'Add a subtitle...') {
    builder.updateModule(props.module.id, {
      settings: { ...settings.value, subtitle: newSubtitle }
    })
  }
}

const wrapperStyles = computed(() => {
    const styles = { width: '100%' }
    // Move background/spacing/border/shadow handling to wrapper if needed, 
    // but for now keeping it on the heading element as per previous design might be safer?
    // Actually, if we have a subtitle, we might want the wrapper to handle the background?
    // Let's assume background applies to the wrapper if subtitle exists.
    // For safety, let's keep the original logic for now but applying to headingStyles if wrapper doesn't take it?
    // No, let's apply container styles to wrapper if we want to support subtitle correctly.
    
    // Applying container styles to wrapper
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

const headingStyles = computed(() => {
  const styles = { width: '100%' }
  // Removed container styles from here as they are now on wrapper
  
  // Custom Typography mapping for Heading
  const typography = getTypographyStyles(settings.value, '', device.value)
  if (settings.value.alignment) {
    typography.textAlign = getResponsiveValue(settings.value, 'alignment', device.value)
  }
  Object.assign(styles, typography)
  
  return styles
})

const cssClass = computed(() => settings.value.cssClass || '')

const sizeClass = computed(() => ({
    'text-xl md:text-2xl': size.value === 'small',
    'text-2xl md:text-3xl': size.value === 'medium',
    'text-3xl md:text-4xl': size.value === 'large',
    'text-4xl md:text-5xl': size.value === 'xlarge',
    'text-5xl md:text-7xl': size.value === 'display'
}))

const subtitleSizeClass = computed(() => ({
    'text-sm': size.value === 'small',
    'text-base': size.value === 'medium',
    'text-lg': size.value === 'large',
    'text-xl': size.value === 'xlarge',
    'text-xl': size.value === 'display'
}))

const decorationClass = computed(() => ({
    'underline underline-offset-8 decoration-primary decoration-4': decoration.value === 'underline',
    'bg-gradient-to-r from-primary/20 to-primary/10 px-4 py-2 rounded-lg inline-block': decoration.value === 'highlight',
    'bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent': decoration.value === 'gradient'
}))

const subtitleStyles = computed(() => {
    const s = getTypographyStyles(settings.value, '', device.value)
    // We want to inherit alignment but maybe reduce opacity/weight?
    return {
        textAlign: s.textAlign || 'left',
        opacity: 0.8,
        marginTop: '0.5em',
        fontWeight: 'normal'
    }
})
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
