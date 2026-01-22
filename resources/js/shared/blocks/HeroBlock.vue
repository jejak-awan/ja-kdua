<template>
  <BaseBlock :module="module" :mode="mode" :device="device" v-slot="{ settings, getAttributes }">
    <div class="hero-content relative z-10 w-full" :style="contentStyles">
        <h1 v-if="settings.title" class="hero-title leading-tight font-bold mb-6" :style="titleStyles" v-bind="getAttributes('title')">
            {{ settings.title }}
        </h1>
        <p v-if="settings.subtitle" class="hero-subtitle text-lg md:text-xl opacity-90 mb-10 max-w-2xl" :style="subtitleStyles" v-bind="getAttributes('subtitle')">
            {{ settings.subtitle }}
        </p>
        <div v-if="settings.show_cta" class="hero-actions flex gap-4" :style="actionsStyles">
            <button class="px-8 py-4 bg-primary text-white rounded-full font-bold shadow-xl hover:scale-105 transition-transform" v-bind="getAttributes('button')">
                {{ settings.buttonText || 'Get Started' }}
            </button>
        </div>
        
        <!-- Nested blocks support for complex Heros -->
        <div class="hero-nested mt-12 w-full">
            <template v-if="mode === 'edit'">
                <slot />
            </template>
            <template v-else>
                <div v-for="child in nestedBlocks" :key="child.id" class="hero-child">
                    <BlockRenderer :block="child" :mode="mode" />
                </div>
            </template>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const BlockRenderer = inject('BlockRenderer', null)

const contentStyles = computed(() => {
    const align = getResponsiveValue(settings.value, 'align', device.value) || 'center'
    return {
        textAlign: align,
        display: 'flex',
        flexDirection: 'column',
        alignItems: align === 'center' ? 'center' : (align === 'right' ? 'flex-end' : 'flex-start')
    }
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const actionsStyles = computed(() => ({}))
</script>

<style scoped>
.hero-block { width: 100%; display: flex; align-items: center; min-height: 400px; }
.hero-title { font-size: clamp(2rem, 5vw, 4rem); }
</style>
