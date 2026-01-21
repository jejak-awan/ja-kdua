<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div class="heading-container">
        <component 
          :is="tag(settings)"
          class="heading-block"
          :style="headingStyles(settings, blockDevice)"
          :class="[sizeClass(settings), decorationClass(settings)]"
        >
          <template v-if="blockMode === 'edit' && !isDynamic(settings)">
            <div 
              ref="editableRef"
              contenteditable="true"
              @blur="onTextBlur($event, settings)"
              v-html="displayText(settings)"
              style="display: inline-block; width: 100%; outline: none;"
            ></div>
          </template>
          <template v-else>
            {{ displayText(settings) }}
          </template>
        </component>

        <div v-if="subtitle(settings) || blockMode === 'edit'" class="heading-subtitle" :class="subtitleSizeClass(settings)" :style="subtitleStyles(settings, blockDevice)">
          <div 
            :contenteditable="blockMode === 'edit'"
            @blur="onSubtitleBlur($event, settings)"
            v-html="subtitle(settings) || (blockMode === 'edit' ? 'Add a subtitle...' : '')"
            style="display: inline-block; width: 100%; outline: none;"
          ></div>
        </div>
        
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getTypographyStyles, getVal, getResponsiveValue } from '../utils/styleUtils'
// We might need to handle dynamic resolution differently in a shared component
// For now, let's keep the core logic simple and adapt the dynamic resolver if needed.

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)

const tag = (settings) => getVal(settings, 'tag') || 'h2'
const subtitle = (settings) => getVal(settings, 'subtitle') || ''
const isDynamic = (settings) => {
    const text = getVal(settings, 'text')
    return typeof text === 'string' && text.startsWith('{{')
}

const displayText = (settings) => {
    return getVal(settings, 'text') || 'Heading Text'
}

const headingStyles = (settings, device) => {
  const styles = { width: '100%' }
  const typography = getTypographyStyles(settings, '', device)
  
  const alignment = getVal(settings, 'alignment', device)
  if (alignment) typography.textAlign = alignment
  
  Object.assign(styles, typography)
  return styles
}

const sizeClass = (settings) => {
    const size = getVal(settings, 'size') || 'large'
    
    const sizes = {
        small: { mobile: 'text-lg', desktop: 'text-2xl' },
        medium: { mobile: 'text-xl', desktop: 'text-3xl' },
        large: { mobile: 'text-2xl', desktop: 'text-4xl' },
        xlarge: { mobile: 'text-3xl', desktop: 'text-5xl' },
        display: { mobile: 'text-4xl', desktop: 'text-7xl' }
    }
    
    // Fallback
    const s = sizes[size] || sizes.large

    // Builder Mode: Force specific class based on simulated device
    if (props.mode === 'edit') {
        if (props.device === 'mobile') return s.mobile
        // Tablet usually inherits mobile or has intermediate, but for now map to mobile or desktop
        // Let's use mobile size for tablet to be safe/compact, or define tablet specific if needed.
        if (props.device === 'tablet') return s.mobile 
        return s.desktop // default to desktop
    }

    // Frontend: Use responsive classes
    return `${s.mobile} md:${s.desktop}`
}

const subtitleSizeClass = (settings) => {
    const size = getVal(settings, 'size') || 'large'
    
    // Mappings for subtitle relative to heading size
    const sizes = {
        small: { mobile: 'text-sm', desktop: 'text-sm' },
        medium: { mobile: 'text-sm', desktop: 'text-base' },
        large: { mobile: 'text-base', desktop: 'text-lg' },
        xlarge: { mobile: 'text-lg', desktop: 'text-xl' },
        display: { mobile: 'text-xl', desktop: 'text-2xl' }
    }

    const s = sizes[size] || sizes.large

    // Builder Mode
    if (props.mode === 'edit') {
        if (props.device === 'mobile') return s.mobile
        if (props.device === 'tablet') return s.mobile
        return s.desktop
    }

    // Frontend (simplified responsive behavior for subtitle)
    if (s.mobile === s.desktop) return s.desktop
    return `${s.mobile} md:${s.desktop}`
}

const decorationClass = (settings) => {
    const dec = getVal(settings, 'decoration') || 'none'
    return {
        'underline underline-offset-8 decoration-primary decoration-4': dec === 'underline',
        'bg-gradient-to-r from-primary/20 to-primary/10 px-4 py-2 rounded-lg inline-block': dec === 'highlight',
        'bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent': dec === 'gradient'
    }
}

const subtitleStyles = (settings, device) => {
    const s = getTypographyStyles(settings, '', device)
    return {
        textAlign: s.textAlign || 'left',
        opacity: 0.8,
        marginTop: '0.5em',
        fontWeight: 'normal'
    }
}

const onTextBlur = (e, settings) => {
  if (props.mode !== 'edit' || !builder) return
  const newText = e.target.innerText
  if (newText !== getVal(settings, 'text')) {
    builder.updateModule(props.module.id, {
      settings: { ...settings, text: newText }
    })
  }
}

const onSubtitleBlur = (e, settings) => {
  if (props.mode !== 'edit' || !builder) return
  const newSubtitle = e.target.innerText
  if (newSubtitle !== getVal(settings, 'subtitle') && newSubtitle !== 'Add a subtitle...') {
    builder.updateModule(props.module.id, {
      settings: { ...settings, subtitle: newSubtitle }
    })
  }
}
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
