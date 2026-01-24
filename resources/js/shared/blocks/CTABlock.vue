<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
        <div 
            class="cta-block relative overflow-hidden transition-all duration-300 group" 
            :class="[
                getVal(settings, 'radius') || 'rounded-2xl',
                getVal(settings, 'padding') || 'py-32 px-12'
            ]"
            :style="blockStyles(settings)"
        >
            <!-- Background Image & Overlay -->
            <div 
                v-if="getVal(settings, 'bgImage')"
                class="absolute inset-0 z-0"
            >
                <img 
                    :src="getVal(settings, 'bgImage')"
                    class="w-full h-full object-cover"
                    alt=""
                />
                <div 
                    class="absolute inset-0 bg-black transition-opacity duration-300"
                    :style="{ opacity: (getVal(settings, 'overlayOpacity') || 0) / 100 }"
                ></div>
            </div>

            <!-- Content Wrapper -->
            <div 
                class="cta-content relative z-10 w-full"
                :class="layoutClasses(settings, blockDevice)"
            >
                <!-- Text Area -->
                <div 
                    class="cta-text-area max-w-3xl"
                     :class="{ 
                        'text-center mx-auto': isCenter(settings, blockDevice),
                        'text-left': !isCenter(settings, blockDevice)
                    }"
                >
                    <h2 
                        class="cta-title font-bold leading-tight mb-4" 
                        :style="titleStyles(settings, blockDevice)"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateResponsiveField('title', e.target.innerText)"
                    >
                        {{ getVal(settings, 'title', blockDevice) }}
                    </h2>
                     <div 
                        class="cta-subtitle text-lg opacity-90 leading-relaxed" 
                        :style="{ color: getVal(settings, 'textColor') || '#ffffff' }"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateResponsiveField('subtitle', e.target.innerText)"
                    >
                        {{ getVal(settings, 'subtitle', blockDevice) }}
                    </div>
                </div>

                <!-- Button Area -->
                <div 
                    class="cta-button-area shrink-0"
                    :class="{ 'mt-8': !isInline(settings, blockDevice) }"
                >
                     <a 
                        :href="getVal(settings, 'buttonUrl') || '#'" 
                        class="cta-button inline-flex items-center justify-center font-semibold transition-all duration-200"
                        :class="[
                            buttonClasses(settings),
                            'px-8 py-3 rounded-lg hover:transform hover:-translate-y-0.5'
                        ]"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateResponsiveField('buttonText', e.target.innerText)"
                        @click="mode === 'edit' ? e.preventDefault() : null"
                    >
                        {{ getVal(settings, 'buttonText', blockDevice) || 'Get Started' }}
                    </a>
                </div>
            </div>
        </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')

const blockStyles = (settings) => {
    const baseColor = getVal(settings, 'bgColor') || '#4f46e5'
    return {
        background: `linear-gradient(135deg, ${baseColor}, #7c3aed)`, // Modern Indigo to Violet pulse
    }
}

const titleStyles = (settings, device) => {
    return {
        color: getVal(settings, 'textColor') || '#ffffff',
        fontSize: device === 'mobile' ? '36px' : '56px',
        fontWeight: '900', // Heavy impact
        letterSpacing: '-0.025em'
    }
}

const isCenter = (settings, device) => {
    const layout = getVal(settings, 'layout', device) || 'stacked-center'
    return layout === 'stacked-center'
}

const isInline = (settings, device) => {
    const layout = getVal(settings, 'layout', device) || 'stacked-center'
    return layout === 'inline' || layout === 'split'
}

const layoutClasses = (settings, device) => {
    const layout = getVal(settings, 'layout', device) || 'stacked-center'
    
    if (layout === 'split') {
        return 'flex flex-col md:flex-row items-center justify-between gap-8 text-left'
    }
    if (layout === 'inline') {
        return 'flex flex-col md:flex-row items-center justify-center gap-6 text-center md:text-left'
    }
    if (layout === 'stacked-left') {
        return 'flex flex-col items-start text-left'
    }
    // Default: stacked-center
    return 'flex flex-col items-center text-center'
}

const buttonClasses = (settings) => {
    const style = getVal(settings, 'buttonStyle') || 'secondary'
    
    if (style === 'primary') {
        return 'bg-primary text-primary-foreground hover:brightness-110 shadow-lg'
    }
    if (style === 'outline') {
        return 'bg-transparent border-2 border-current hover:bg-white/10'
    }
    // Default: secondary (White usually on colored bg)
    return 'bg-white text-gray-900 hover:bg-gray-50 shadow-lg'
}

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = props.module.settings[fieldName]
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
</style>
