<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="hero-inner relative z-10 w-full flex" 
        :class="[
            getVal(settings, 'layout') === 'split' ? 'lg:flex-row flex-col' : 'flex-col',
            alignmentClasses(settings, blockDevice).container
        ]"
        :style="heroInnerStyles(settings, blockDevice)"
      >
        <!-- Text Content Area -->
        <div 
            class="hero-content-area flex flex-col"
            :class="[
                getVal(settings, 'layout') === 'split' ? 'lg:w-1/2 w-full' : 'w-full',
                getVal(settings, 'useGlass') ? 'bg-white/10 backdrop-blur-md border border-white/20 p-10 md:p-16 rounded-[40px]' : ''
            ]"
            :style="{ maxWidth: (getVal(settings, 'contentMaxWidth') || 1200) + 'px' }"
        >
          <div 
            class="hero-text-wrapper flex flex-col"
            :class="alignmentClasses(settings, blockDevice).text"
          >
            <!-- Eyebrow / Badge -->
            <Badge 
                v-if="getVal(settings, 'eyebrow')"
                variant="outline"
                class="mb-6 w-fit border-primary/30 bg-primary/5 text-primary-foreground font-bold tracking-widest uppercase text-xs px-4 py-1.5 rounded-full"
            >
                {{ getVal(settings, 'eyebrow') }}
            </Badge>

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

            <!-- Nested blocks for buttons -->
            <div class="hero-nested-blocks w-full flex flex-wrap gap-4" :class="alignmentClasses(settings, blockDevice).buttons">
                <template v-if="mode === 'edit'">
                    <slot />
                    <div v-if="!nestedBlocks.length" class="p-4 border border-dashed border-white/20 rounded-xl text-xs text-white/40">
                        Drop buttons here
                    </div>
                </template>
                <template v-else>
                    <slot />
                </template>
            </div>
          </div>
        </div>

        <!-- Media Area (Visible in split layout) -->
        <div 
            v-if="getVal(settings, 'layout') === 'split'"
            class="hero-media-area lg:w-1/2 w-full flex items-center justify-center p-8 lg:p-0"
        >
            <div class="relative w-full aspect-video lg:aspect-square group overflow-hidden rounded-[30px] shadow-2xl">
                <img 
                    v-if="getVal(settings, 'image')"
                    :src="getVal(settings, 'image')" 
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />
                <div v-else class="w-full h-full bg-slate-800/50 flex flex-col items-center justify-center text-white/20 gap-4">
                    <LucideIcon name="Image" :size="64" />
                    <span class="font-bold">Add split image in settings</span>
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
import { Badge, Button } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)

const heroInnerStyles = (settings, device) => {
    const minHeight = getVal(settings, 'minHeight', device) || 700
    const vAlign = getVal(settings, 'verticalAlign') || 'center'
    
    return {
        minHeight: `${minHeight}px`,
        justifyContent: vAlign === 'center' ? 'center' : (vAlign === 'end' ? 'flex-end' : 'flex-start'),
        alignItems: 'center'
    }
}

const alignmentClasses = (settings, device) => {
    const align = getVal(settings, 'titleAlign', device) || 'center'
    const res = {
        container: 'mx-auto',
        text: 'text-center items-center',
        buttons: 'justify-center'
    }

    if (align === 'left') {
        res.container = 'mr-auto'
        res.text = 'text-left items-start'
        res.buttons = 'justify-start'
    } else if (align === 'right') {
        res.container = 'ml-auto'
        res.text = 'text-right items-end'
        res.buttons = 'justify-end'
    }

    return res
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
.hero-content-area { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
