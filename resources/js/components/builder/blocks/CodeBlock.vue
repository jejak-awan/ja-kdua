<template>
  <div class="code-block" :style="wrapperStyles">
    <div class="code-header" :class="themeClass">
      <span class="code-language">{{ language.toUpperCase() }}</span>
      <button class="code-copy" @click="copyCode">
        <Copy class="copy-icon" />
      </button>
    </div>
    <pre class="code-content" :class="themeClass" :style="codeStyles"><code><template v-for="(line, index) in codeLines" :key="index"><span v-if="showLineNumbers" class="line-number">{{ index + 1 }}</span>{{ line }}
</template></code></pre>
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
const device = computed(() => builder?.device || 'desktop')

const language = computed(() => settings.value.language || 'html')
const showLineNumbers = computed(() => getResponsiveValue(settings.value, 'showLineNumbers', device.value) !== false)
const themeClass = computed(() => `code--${getResponsiveValue(settings.value, 'theme', device.value) || 'dark'}`)

const codeLines = computed(() => {
  const code = settings.value.code || ''
  return code.split('\n')
})

const copyCode = () => {
  navigator.clipboard?.writeText(settings.value.code || '')
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

const codeStyles = computed(() => getTypographyStyles(settings.value, 'code_', device.value))
</script>

<style scoped>
.code-block { width: 100%; }
.code-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; }
.code-header.code--dark { background: #1e1e1e; color: #888; }
.code-header.code--light { background: #f5f5f5; color: #666; }
.code-language { font-size: 12px; font-weight: 600; }
.code-copy { background: none; border: none; cursor: pointer; padding: 4px; opacity: 0.6; color: inherit; }
.code-copy:hover { opacity: 1; }
.copy-icon { width: 16px; height: 16px; }
.code-content { margin: 0; padding: 16px; overflow-x: auto; }
.code-content.code--dark { background: #1e1e1e; color: #d4d4d4; }
.code-content.code--light { background: #f8f8f8; color: #333; }
.line-number { display: inline-block; width: 32px; color: #666; text-align: right; padding-right: 16px; user-select: none; }
</style>
