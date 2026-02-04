<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div 
        class="hero-block-container"
        :id="(getVal<string>(settings, 'html_id', blockDevice) as string)"
        :style="(containerStyles(settings, blockDevice) as CSSProperties)"
      >
        <div 
            class="hero-inner relative overflow-hidden transition-colors duration-700 group" 
            :style="(innerStyles(settings, blockDevice) as CSSProperties)"
        >
            <!-- Decorative Orbs -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary/20 rounded-full blur-3xl transition-transform duration-1000 group-hover:scale-125"></div>

            <!-- Background Image & Overlay -->
            <div 
                v-if="getVal(settings, 'bgImage', blockDevice)"
                class="absolute inset-0 z-0"
            >
                <img 
                    :src="(getVal<string>(settings, 'bgImage', blockDevice) as string)"
                    class="w-full h-full object-cover transition-transform duration-[2000ms] group-hover:scale-110"
                    alt=""
                />
                <div 
                    class="absolute inset-0 bg-black transition-opacity duration-500"
                    :style="{ opacity: (getVal<number>(settings, 'overlayOpacity', blockDevice) || 0) / 100 }"
                ></div>
            </div>

            <!-- Content Wrapper -->
            <div 
                class="hero-content relative z-10 w-full flex"
                :class="layoutClasses(settings, blockDevice)"
                :style="(contentWrapperStyles(settings, blockDevice) as CSSProperties)"
            >
                <!-- Text Area -->
                <div 
                    class="hero-text-area"
                    :style="{ maxWidth: toCSS(getVal<string | number>(settings, 'contentMaxWidth', blockDevice) || 1200) }"
                    :class="[
                       isCenter(settings, blockDevice) ? 'text-center mx-auto' : 'text-left'
                    ]"
                >
                    <Badge 
                        v-if="getVal(settings, 'eyebrow', blockDevice)"
                        class="mb-8 rounded-full bg-white/10 text-white border-white/20 backdrop-blur-md px-6 py-2 text-sm font-semibold tracking-wide"
                    >
                        {{ getVal(settings, 'eyebrow', blockDevice) }}
                    </Badge>

                    <h1 
                        class="hero-title font-black leading-tight mb-8 tracking-tighter transition-[width] duration-500" 
                        :style="(getTypographyStyles(settings, 'title_', blockDevice) as CSSProperties)"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateField('title', (e.target as HTMLElement).innerText)"
                    >
                        {{ getVal<string>(settings, 'title', blockDevice) }}
                    </h1>
                    
                    <div 
                        v-if="mode === 'edit' || getVal(settings, 'subtitle', blockDevice)"
                        class="hero-subtitle text-xl opacity-90 leading-relaxed font-medium mb-12 transition-[width] duration-500" 
                        :style="(getTypographyStyles(settings, 'subtitle_', blockDevice) as CSSProperties)"
                        :contenteditable="mode === 'edit'"
                        @blur="e => updateField('subtitle', (e.target as HTMLElement).innerText)"
                    >
                        {{ getVal(settings, 'subtitle', blockDevice) }}
                    </div>

                    <!-- Split Image -->
                    <div 
                        v-if="getVal(settings, 'layout', blockDevice) === 'split' && getVal(settings, 'image', blockDevice)"
                        class="hero-split-image mt-12 rounded-3xl overflow-hidden shadow-2xl transition-transform duration-700 group-hover:scale-[1.02]"
                    >
                         <img 
                            :src="(getVal<string>(settings, 'image', blockDevice) as string)"
                            class="w-full h-auto object-cover"
                            alt="Hero"
                        />
                    </div>
                </div>
            </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Badge } from '../ui'
import { 
    getVal, 
    getTypographyStyles,
    getLayoutStyles,
    toCSS
} from '../utils/styleUtils'
import type { BuilderInstance, BlockProps, ModuleSettings } from '../../types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)

const containerStyles = (settings: ModuleSettings, device: string) => {
    return {
        width: '100%',
        ...getLayoutStyles(settings, device)
    }
}

const innerStyles = (settings: ModuleSettings, device: string): CSSProperties => {
    const style: Record<string, string | number | undefined> = {
        minHeight: toCSS(getVal<string | number>(settings, 'minHeight', device) || 700),
        willChange: 'transform, filter',
        transition: 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
        display: 'flex',
        alignItems: getVal<string>(settings, 'verticalAlign', device) || 'center'
    }

    // Background logic
    const baseColor = getVal<string>(settings, 'bgColor') || 'transparent'
    const gradStart = getVal<string>(settings, 'gradientStart') || '#4f46e5'
    const gradEnd = getVal<string>(settings, 'gradientEnd') || '#7c3aed'
    
    if (baseColor === 'transparent') {
        style.background = `linear-gradient(135deg, ${gradStart}, ${gradEnd})`
    }

    // Interactive Variables
    style['--hover-scale'] = `scale(${getVal<number>(settings, 'hover_scale', device) || 1})`;
    style['--hover-brightness'] = `brightness(${getVal<number>(settings, 'hover_brightness', device) || 100}%)`;

    return style as CSSProperties
}

const contentWrapperStyles = (_settings: ModuleSettings, _device: string) => {
    return {
        padding: '0 2rem'
    }
}

const isCenter = (settings: ModuleSettings, device: string) => {
    const align = getVal<string>(settings, 'alignment', device) || 'center'
    return align === 'center'
}

const layoutClasses = (settings: ModuleSettings, device: string) => {
    const layout = getVal<string>(settings, 'layout', device) || 'centered'
    if (layout === 'split') {
        return 'flex-col lg:flex-row items-center justify-between gap-16'
    }
    return 'flex-col items-center justify-center'
}

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.hero-block-container { 
    width: 100%; 
}

.hero-inner:hover {
    transform: var(--hover-scale);
    filter: var(--hover-brightness);
}

.hero-title { 
    letter-spacing: -0.04em; 
    white-space: pre-line;
}
</style>
