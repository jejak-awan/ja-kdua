<template>
  <BaseBlock :module="module" :mode="mode" :device="device" v-slot="{ styles: wrapperBaseStyles, settings, getAttributes }">
      <div class="cta-block" :style="ctaBlockStyles">
        <div class="cta-content" :style="contentStyles">
          <h2 
            v-if="title || mode === 'edit'" 
            class="cta-title" 
            :style="titleStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('title', e.target.innerText)"
            v-bind="getAttributes('title')"
          >
            {{ title }}
          </h2>
          <p 
            v-if="content || mode === 'edit'" 
            class="cta-text" 
            :style="textStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('content', e.target.innerText)"
            v-bind="getAttributes('content')"
          >
            {{ content }}
          </p>
        </div>
        
        <div class="cta-button-wrapper">
          <a 
            :href="settings.buttonUrl || '#'" 
            :target="settings.buttonTarget || '_self'"
            class="cta-button"
            :style="buttonStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('buttonText', e.target.innerText)"
            @click="mode === 'edit' ? e => e.preventDefault() : undefined"
            v-bind="getAttributes('button')"
          >
            {{ buttonText || 'Get Started' }}
          </a>
        </div>
      </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

const title = computed(() => getVal(settings.value, 'title', props.device) || '')
const content = computed(() => getVal(settings.value, 'content', props.device) || '')
const buttonText = computed(() => getVal(settings.value, 'buttonText', props.device) || '')

const layout = computed(() => getVal(settings.value, 'layout', props.device) || 'stacked')
const alignment = computed(() => getVal(settings.value, 'alignment', props.device) || 'center')

const ctaBlockStyles = computed(() => {
  return { 
    width: '100%',
    display: 'flex',
    gap: layout.value === 'stacked' ? '24px' : '32px',
    flexDirection: layout.value === 'stacked' ? 'column' : 'row',
    alignItems: layout.value === 'inline' ? 'center' : (alignment.value === 'center' ? 'center' : (alignment.value === 'right' ? 'flex-end' : 'flex-start')),
    textAlign: alignment.value
  }
})

const contentStyles = computed(() => ({
  flex: layout.value === 'inline' ? 1 : 'none',
  textAlign: alignment.value
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const textStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device))

const buttonStyles = computed(() => {
  const styles = {
    display: 'inline-block',
    padding: '12px 28px',
    backgroundColor: settings.value.buttonBackgroundColor || 'var(--theme-primary-color, #2059ea)',
    color: settings.value.buttonTextColor || '#ffffff',
    textDecoration: 'none',
    borderRadius: '6px',
    transition: 'all 0.2s ease',
    cursor: 'pointer'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'button_', props.device))
  return styles
})

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.cta-block { width: 100%; }
.cta-button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); opacity: 0.9; }
</style>
