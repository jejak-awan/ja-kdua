<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="hero-inner relative z-10 w-full flex flex-col justify-center" 
        :style="heroInnerStyles(settings, blockDevice)"
      >
        <div class="mx-auto w-full px-6" :style="{ maxWidth: (getVal(settings, 'contentMaxWidth') || 1200) + 'px' }">
          <div 
            class="hero-text-wrapper flex flex-col"
            :class="alignmentClasses(settings, blockDevice)"
          >
            <component 
                :is="getVal(settings, 'titleTag') || 'h1'"
                v-if="getVal(settings, 'title')" 
                class="hero-title font-black mb-8 leading-[1.1] text-pretty whitespace-pre-line" 
                :class="{ 'drop-shadow-lg': getVal(settings, 'titleShadow') }"
                :style="titleStyles(settings, blockDevice)"
                :contenteditable="mode === 'edit'"
                @blur="e => updateField('title', e.target.innerText)"
            >
                {{ getVal(settings, 'title') }}
            </component>

            <div 
                v-if="getVal(settings, 'subtitle')"
                class="hero-subtitle mb-12 opacity-90 leading-relaxed font-medium"
                :style="subtitleStyles(settings, blockDevice)"
                :contenteditable="mode === 'edit'"
                @blur="e => updateField('subtitle', e.target.innerText)"
            >
                {{ getVal(settings, 'subtitle') }}
            </div>

            <div v-if="mode === 'edit' && !nestedBlocks.length" class="hero-placeholder p-12 border-2 border-dashed border-white/20 rounded-3xl text-white/40 text-center">
                <p>Add buttons or other modules here</p>
            </div>

            <!-- Nested blocks support -->
            <div class="hero-nested-blocks w-full">
                <template v-if="mode === 'edit'">
                    <slot />
                </template>
                <template v-else>
                    <div v-for="child in nestedBlocks" :key="child.id" class="hero-child">
                         <!-- Recursion handled by renderer -->
                    </div>
                </template>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder')

const heroInnerStyles = (settings, device) => {
    const minHeight = getVal(settings, 'minHeight', device) || 700
    const vAlign = getVal(settings, 'verticalAlign') || 'center'
    
    return {
        minHeight: `${minHeight}px`,
        justifyContent: vAlign === 'center' ? 'center' : (vAlign === 'end' ? 'flex-end' : 'flex-start')
    }
}

const alignmentClasses = (settings, device) => {
    const align = getVal(settings, 'titleAlign', device) || 'center'
    if (align === 'center') return 'text-center items-center mx-auto'
    if (align === 'right') return 'text-right items-end ml-auto'
    return 'text-left items-start mr-auto'
}

const titleStyles = (settings, device) => {
    const fontSize = getVal(settings, 'titleSize', device) || 72
    const color = getVal(settings, 'titleColor') || '#ffffff'
    const weight = getVal(settings, 'titleWeight') || '900'
    
    return {
        fontSize: `${fontSize}px`,
        color: color,
        fontWeight: weight
    }
}

const subtitleStyles = (settings, device) => {
    const fontSize = getVal(settings, 'subtitleSize', device) || 22
    const color = getVal(settings, 'subtitleColor') || 'rgba(255, 255, 255, 0.9)'
    const maxWidth = getVal(settings, 'subtitleMaxWidth') || 800
    
    return {
        fontSize: `${fontSize}px`,
        color: color,
        maxWidth: `${maxWidth}px`
    }
}

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.hero-title { letter-spacing: -0.04em; }
.hero-inner { width: 100%; transition: all 0.5s ease-out; }
</style>
