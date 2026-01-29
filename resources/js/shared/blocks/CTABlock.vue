<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
        <div 
            class="cta-block-container"
            :id="getVal(settings, 'html_id', blockDevice)"
            :style="containerStyles(settings, blockDevice)"
        >
            <div 
                class="cta-inner relative overflow-hidden transition-[width] duration-500 group rounded-[48px]" 
                :style="innerStyles(settings, blockDevice)"
            >
                <!-- Decorative Orbs -->
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary/20 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>

                <!-- Content Wrapper -->
                <div 
                    class="cta-content relative z-10 w-full flex"
                    :class="layoutClasses(settings, blockDevice)"
                    :style="contentWrapperStyles(settings, blockDevice)"
                >
                    <!-- Text Area -->
                    <div 
                        class="cta-text-area max-w-4xl"
                        :class="{ 
                            'text-center mx-auto': isCenter(settings, blockDevice),
                            'text-left': !isCenter(settings, blockDevice)
                        }"
                    >
                        <h2 
                            class="cta-title font-black leading-none mb-6 tracking-tighter transition-colors duration-300" 
                            :style="getTypographyStyles(settings, 'title_', blockDevice)"
                            :contenteditable="mode === 'edit'"
                            @blur="e => updateField('title', (e.target as HTMLElement).innerText)"
                        >
                            {{ getVal(settings, 'title', blockDevice) }}
                        </h2>
                        <div 
                            class="cta-body text-xl opacity-90 leading-relaxed font-medium transition-colors duration-300" 
                            :style="getTypographyStyles(settings, 'content_', blockDevice)"
                            :contenteditable="mode === 'edit'"
                            @blur="e => updateField('content', (e.target as HTMLElement).innerText)"
                            v-html="getVal(settings, 'content', blockDevice)"
                        >
                        </div>
                    </div>

                    <!-- Button Area -->
                    <div 
                        class="cta-button-area shrink-0"
                        :class="{ 'mt-12': !isInline(settings, blockDevice) }"
                    >
                        <Button 
                            as="a"
                            :href="mode === 'view' ? (getVal(settings, 'buttonUrl') || '#') : undefined"
                            :target="getVal(settings, 'buttonTarget') || '_self'"
                            :aria-label="getVal(settings, 'aria_label', blockDevice) || undefined"
                            class="h-14 px-12 rounded-full font-bold shadow-2xl transition-colors duration-300 hover:scale-105 active:scale-95"
                            :style="buttonDisplayStyles(settings, blockDevice)"
                            @click="onButtonClick"
                        >
                            {{ getVal(settings, 'buttonText', blockDevice) || 'Get Started' }}
                            <LucideIcon name="ArrowRight" class="ml-2 w-5 h-5 transition-transform group-hover:translate-x-1" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, BlockProps } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)

const containerStyles = (settings: any, device: string) => {
    return {
        width: '100%',
        ...getLayoutStyles(settings, device)
    }
}

const innerStyles = (settings: any, device: string) => {
    const style: Record<string, any> = {
        willChange: 'transform, filter',
        transition: 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
    }

    // Gradient Background from settings
    const baseColor = getVal(settings, 'bgColor') || '#4f46e5'
    style.background = `linear-gradient(135deg, ${baseColor}, #7c3aed)`

    // Interactive Variables
    style['--hover-scale'] = `scale(${getVal(settings, 'hover_scale', device) || 1})`;
    style['--hover-brightness'] = `brightness(${getVal(settings, 'hover_brightness', device) || 100}%)`;

    return style
}

const contentWrapperStyles = (settings: any, device: string) => {
    // Basic Flex/Spacing based on layout type
    return {}
}

const isCenter = (settings: any, device: string) => {
    const align = getVal(settings, 'alignment', device) || 'center'
    return align === 'center'
}

const isInline = (settings: any, device: string) => {
    const layout = getVal(settings, 'layout', device) || 'inline'
    return layout === 'inline' || layout === 'split'
}

const layoutClasses = (settings: any, device: string) => {
    const layout = getVal(settings, 'layout', device) || 'inline'
    
    if (layout === 'split') {
        return 'flex-col lg:flex-row items-center justify-between gap-12 text-left'
    }
    if (layout === 'inline') {
        return 'flex-col lg:flex-row items-center justify-center gap-8 text-center lg:text-left'
    }
    return 'flex-col items-center text-center'
}

const buttonDisplayStyles = (settings: any, device: string) => {
    const styles = getTypographyStyles(settings, 'button_', device)
    
    return {
        ...styles,
        backgroundColor: getVal(settings, 'buttonBackgroundColor') || '#ffffff',
        color: getVal(settings, 'buttonTextColor') || '#4f46e5'
    }
}

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}

const onButtonClick = (e: MouseEvent) => {
  if (props.mode === 'edit') {
    e.preventDefault()
  }
}
</script>

<style scoped>
.cta-block-container { 
    width: 100%; 
}

.cta-inner:hover {
    transform: var(--hover-scale);
    filter: var(--hover-brightness);
}

.cta-title { letter-spacing: -0.02em; }
</style>
