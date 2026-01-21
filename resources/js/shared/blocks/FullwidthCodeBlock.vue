<template>
  <BaseBlock :module="module" :settings="settings" class="fullwidth-code-block" :style="wrapperStyles">
    <div 
      v-if="settings.rawContent || mode === 'edit'" 
      class="code-content" 
      :contenteditable="mode === 'edit'"
      @blur="updateCode"
      v-html="settings.rawContent || (mode === 'edit' ? '<!-- Add your custom HTML/CSS/JS here -->' : '')" 
    />
    <div v-else class="code-placeholder">
      <Code class="placeholder-icon" />
      <span>Add custom HTML/CSS/JS</span>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Code } from 'lucide-vue-next'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})

const updateCode = (event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { rawContent: value })
}

const wrapperStyles = computed(() => {
  return { 
    width: '100%', 
    minHeight: '100px'
  }
})
</script>

<style scoped>
.fullwidth-code-block { width: 100%; }
.code-content { width: 100%; min-height: 50px; }
.code-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 48px; color: #666; width: 100%; border: 1px dashed #ccc; }
.placeholder-icon { width: 32px; height: 32px; }
[contenteditable]:focus {
  outline: 1px solid #3b82f6;
  background: rgba(59, 130, 246, 0.05);
}
</style>
