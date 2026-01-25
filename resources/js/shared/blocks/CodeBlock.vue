<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="code-block-wrapper transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Code Block'"
  >
    <div 
        class="code-container transition-all duration-300" 
        :class="[themeClass]"
        :style="containerStyles"
    >
      <div class="code-header">
        <div v-if="windowChrome" class="code-chrome">
          <span class="chrome-dot red"></span>
          <span class="chrome-dot yellow"></span>
          <span class="chrome-dot green"></span>
        </div>
        <span class="code-language font-black tracking-widest">{{ language.toUpperCase() }}</span>
        <button 
            v-if="showCopyButton" 
            class="code-copy p-1.5 rounded-md hover:bg-white/10 transition-colors" 
            @click="copyCode" 
            title="Copy code"
        >
          <component :is="copied ? Check : Copy" class="copy-icon" :class="{ 'text-emerald-400': copied }" />
        </button>
      </div>
      
      <pre 
        class="code-pre custom-scrollbar" 
        :style="codeStyles"
      ><code
        class="code-content"
        :contenteditable="mode === 'edit'"
        @input="onCodeInput"
        v-text="codeValue"
      ></code></pre>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import { Copy, Check } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal, 
  getLayoutStyles,
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const copied = ref(false)

const language = computed(() => getVal(settings.value, 'language', device.value) || 'html')
const showCopyButton = computed(() => getVal(settings.value, 'showCopyButton', device.value) !== false)
const windowChrome = computed(() => getVal(settings.value, 'windowChrome', device.value) === true)
const themeClass = computed(() => `code--${getVal(settings.value, 'theme', device.value) || 'dark'}`)
const codeValue = computed(() => getVal(settings.value, 'code', device.value) || '')

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const codeStyles = computed(() => {
  const s = getTypographyStyles(settings.value, 'code_', device.value) || {}
  const maxHeight = getVal(settings.value, 'maxHeight', device.value)
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

const onCodeInput = (e: any) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { code: e.target.innerText })
}
</script>

<style scoped>
.code-block-wrapper { width: 100%; }
.code-container {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
}

.code-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  gap: 12px;
}

.code-chrome {
  display: flex;
  gap: 8px;
}

.chrome-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.chrome-dot.red { background-color: #ff5f56; }
.chrome-dot.yellow { background-color: #ffbd2e; }
.chrome-dot.green { background-color: #27c93f; }

/* Dark Theme */
.code--dark .code-header { background: #0f1115; color: #4b5563; border-bottom: 1px solid rgba(255,255,255,0.05); }
.code--dark .code-pre { background: #0f1115; color: #e2e8f0; }

/* Light Theme */
.code--light .code-header { background: #f8fafc; color: #94a3b8; border-bottom: 1px solid rgba(0,0,0,0.05); }
.code--light .code-pre { background: #ffffff; color: #1e293b; }

.code-language {
  font-size: 10px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-left: auto;
  opacity: 0.6;
}

.code-copy {
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.6;
  color: inherit;
  transition: all 0.2s;
}

.code-copy:hover { opacity: 1; transform: scale(1.1); }

.copy-icon {
  width: 16px;
  height: 16px;
}

.code-pre {
  margin: 0;
  padding: 24px;
  overflow-x: auto;
  font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', Courier, monospace;
  font-size: 14px;
  line-height: 1.7;
}

.code-content {
  display: block;
  white-space: pre;
  min-height: 1.5em;
  outline: none;
}

.code-content[contenteditable="true"]:empty:before {
  content: '// Enter your code here...';
  opacity: 0.2;
}

.custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.code--light .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); }
</style>

