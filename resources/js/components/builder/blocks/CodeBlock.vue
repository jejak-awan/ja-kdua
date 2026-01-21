<template>
  <div class="code-block" :style="wrapperStyles">
    <div class="code-header" :class="themeClass">
      <div v-if="windowChrome" class="code-chrome">
        <span class="chrome-dot red"></span>
        <span class="chrome-dot yellow"></span>
        <span class="chrome-dot green"></span>
      </div>
      <span class="code-language">{{ language.toUpperCase() }}</span>
      <button v-if="showCopyButton" class="code-copy" @click="copyCode">
        <Copy class="copy-icon" />
      </button>
    </div>
    <pre 
      class="code-content" 
      :class="themeClass" 
      :style="codeStyles"
    ><code
      :contenteditable="builder?.isEditing"
      @blur="onCodeBlur"
    ><template v-for="(line, index) in codeLines" :key="index"><span v-if="showLineNumbers" class="line-number">{{ index + 1 }}</span>{{ line }}{{ index < codeLines.length - 1 ? '\n' : '' }}</template></code></pre>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Copy } from 'lucide-vue-next'
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

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const language = computed(() => settings.value.language || 'html')
const showLineNumbers = computed(() => getResponsiveValue(settings.value, 'showLineNumbers', device.value) !== false)
const showCopyButton = computed(() => getResponsiveValue(settings.value, 'showCopyButton', device.value) !== false)
const windowChrome = computed(() => getResponsiveValue(settings.value, 'windowChrome', device.value) === true)
const maxHeight = computed(() => getResponsiveValue(settings.value, 'maxHeight', device.value))
const themeClass = computed(() => `code--${getResponsiveValue(settings.value, 'theme', device.value) || 'dark'}`)

const codeLines = computed(() => {
  const code = settings.value.code || ''
  return code.split('\n')
})

const copyCode = () => {
  navigator.clipboard?.writeText(settings.value.code || '')
}

const onCodeBlur = (e) => {
    const val = e.target.innerText
    updateResponsiveField('code', val)
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

const wrapperStyles = computed(() => {
  const styles = { width: '100%', overflow: 'hidden' }
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

const codeStyles = computed(() => {
    const s = getTypographyStyles(settings.value, 'code_', device.value) || {}
    if (maxHeight.value) {
        s.maxHeight = maxHeight.value
    }
    return s
})
</script>

<style scoped>
.code-block { width: 100%; }
.code-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; gap: 12px; }

.code-chrome { display: flex; gap: 6px; margin-right: auto; }
.chrome-dot { width: 10px; height: 10px; border-radius: 50%; }
.chrome-dot.red { background-color: #ef4444; }
.chrome-dot.yellow { background-color: #f59e0b; }
.chrome-dot.green { background-color: #22c55e; }

.code-header.code--dark { background: #1e1e1e; color: #888; }
.code-header.code--light { background: #f5f5f5; color: #666; }
.code-language { font-size: 12px; font-weight: 600; margin-left: auto; margin-right: 8px; }
.code-copy { background: none; border: none; cursor: pointer; padding: 4px; opacity: 0.6; color: inherit; }
.code-copy:hover { opacity: 1; }
.copy-icon { width: 16px; height: 16px; }
.code-content { margin: 0; padding: 16px; overflow-x: auto; }
.code-content.code--dark { background: #1e1e1e; color: #d4d4d4; }
.code-content.code--light { background: #f8f8f8; color: #333; }
.line-number { display: inline-block; width: 32px; color: #666; text-align: right; padding-right: 16px; user-select: none; }
</style>
