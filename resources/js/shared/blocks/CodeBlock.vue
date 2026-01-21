<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="code-block-wrapper"
  >
    <div class="code-container" :class="[themeClass]">
      <div class="code-header">
        <div v-if="windowChrome" class="code-chrome">
          <span class="chrome-dot red"></span>
          <span class="chrome-dot yellow"></span>
          <span class="chrome-dot green"></span>
        </div>
        <span class="code-language">{{ language.toUpperCase() }}</span>
        <button v-if="showCopyButton" class="code-copy" @click="copyCode" title="Copy code">
          <component :is="copied ? Check : Copy" class="copy-icon" :class="{ 'text-green-500': copied }" />
        </button>
      </div>
      
      <pre 
        class="code-pre" 
        :style="codeStyles"
      ><code
        class="code-content"
        :contenteditable="mode === 'edit'"
        @blur="updateCode"
        v-text="codeValue"
      ></code></pre>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Copy, Check } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)
const copied = ref(false)

const language = computed(() => getVal(props.settings, 'language') || 'html')
const showCopyButton = computed(() => getVal(props.settings, 'showCopyButton') !== false)
const windowChrome = computed(() => getVal(props.settings, 'windowChrome') === true)
const themeClass = computed(() => `code--${getVal(props.settings, 'theme') || 'dark'}`)
const codeValue = computed(() => getVal(props.settings, 'code') || '')

const codeStyles = computed(() => {
  const s = getTypographyStyles(props.settings, 'code_') || {}
  const maxHeight = getVal(props.settings, 'maxHeight')
  if (maxHeight) {
    s.maxHeight = maxHeight
  }
  return s
})

const copyCode = () => {
  if (copied.value) return
  navigator.clipboard?.writeText(codeValue.value).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  })
}

const updateCode = (e) => {
  if (props.mode !== 'edit' || !builder) return
  
  const value = e.target.innerText
  const current = props.settings.code
  let newValue
  
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { code: newValue })
}
</script>

<style scoped>
.code-container {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.code-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  gap: 12px;
}

.code-chrome {
  display: flex;
  gap: 6px;
}

.chrome-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.chrome-dot.red { background-color: #ef4444; }
.chrome-dot.yellow { background-color: #f59e0b; }
.chrome-dot.green { background-color: #22c55e; }

/* Dark Theme */
.code--dark .code-header { background: #1e1e1e; color: #888; border-bottom: 1px solid #333; }
.code--dark .code-pre { background: #1e1e1e; color: #d4d4d4; }

/* Light Theme */
.code--light .code-header { background: #f5f5f5; color: #666; border-bottom: 1px solid #e5e5e5; }
.code--light .code-pre { background: #ffffff; color: #333; }

.code-language {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-left: auto;
}

.code-copy {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  opacity: 0.6;
  color: inherit;
  transition: opacity 0.2s;
}

.code-copy:hover { opacity: 1; }

.copy-icon {
  width: 14px;
  height: 14px;
}

.code-pre {
  margin: 0;
  padding: 20px;
  overflow-x: auto;
  font-family: 'Fira Code', 'Courier New', Courier, monospace;
  font-size: 14px;
  line-height: 1.6;
}

.code-content {
  display: block;
  white-space: pre;
  min-height: 1em;
  outline: none;
}

.code-content[contenteditable="true"]:empty:before {
  content: '// Enter your code here...';
  opacity: 0.3;
}
</style>
