<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
        <div 
            class="cta-block relative overflow-hidden transition-all duration-500 group rounded-[48px]" 
            :class="[
                getVal(settings, 'padding') || 'py-32 px-12'
            ]"
            :style="blockStyles(settings)"
        >
            <!-- Decorative Orbs / Gradients -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary/20 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>

            <!-- Background Image & Overlay -->
            <div 
                v-if="getVal(settings, 'bgImage')"
                class="absolute inset-0 z-0"
            >
                <img 
                    :src="getVal(settings, 'bgImage')"
                    class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
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
                    class="cta-text-area max-w-4xl"
                     :class="{ 
                        'text-center mx-auto': isCenter(settings, blockDevice),
                        'text-left': !isCenter(settings, blockDevice)
                    }"
                >
                    <Badge 
                        v-if="getVal(settings, 'eyebrow')"
                        class="mb-6 rounded-full bg-white/10 text-white border-white/20 backdrop-blur-md px-4 py-1"
                    >
                        {{ getVal(settings, 'eyebrow') }}
                    </Badge>

                    <h2 
                        class="cta-title font-black leading-none mb-6 tracking-tighter" 
                        :style="titleDisplayStyles(settings, blockDevice)"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateResponsiveField('title', e.target.innerText)"
                    >
                        {{ getVal(settings, 'title', blockDevice) }}
                    </h2>
                     <div 
                        class="cta-subtitle text-xl opacity-90 leading-relaxed font-medium" 
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
                    :class="{ 'mt-12': !isInline(settings, blockDevice) }"
                >
                    <Button 
                        as="a"
                        :href="mode === 'view' ? (getVal(settings, 'buttonUrl') || '#') : null"
                        class="h-14 px-12 rounded-full font-bold shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95"
                        :style="buttonDisplayStyles"
                        :variant="getVal(settings, 'buttonStyle') === 'outline' ? 'outline' : 'default'"
                        @click="mode === 'edit' ? $event.preventDefault() : null"
                    >
                        {{ getVal(settings, 'buttonText', blockDevice) || 'Get Started' }}
                        <LucideIcon name="ArrowRight" class="ml-2 w-5 h-5 transition-transform group-hover:translate-x-1" />
                    </Button>
                </div>
            </div>
        </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button, Badge } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module?.settings || {})

const blockStyles = (settings) => {
    const baseColor = getVal(settings, 'bgColor') || '#4f46e5'
    return {
        background: `linear-gradient(135deg, ${baseColor}, #7c3aed)`,
    }
}

const titleDisplayStyles = (settings, device) => {
    const defaultSize = device === 'mobile' ? '42px' : '72px'
    return {
        color: getVal(settings, 'textColor') || '#ffffff',
        fontSize: getVal(settings, 'titleSize', device) || defaultSize,
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
        return 'flex flex-col lg:flex-row items-center justify-between gap-12 text-left'
    }
    if (layout === 'inline') {
        return 'flex flex-col lg:flex-row items-center justify-center gap-8 text-center lg:text-left'
    }
    if (layout === 'stacked-left') {
        return 'flex flex-col items-start text-left'
    }
    return 'flex flex-col items-center text-center'
}

const buttonDisplayStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    const style = getVal(settings.value, 'buttonStyle') || 'secondary'
    
    const res = { ...styles }
    if (style === 'secondary') {
        res.backgroundColor = '#ffffff'
        res.color = '#1e293b'
    }
    return res
})

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit' || !builder) return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.cta-block { width: 100%; }
</style>
