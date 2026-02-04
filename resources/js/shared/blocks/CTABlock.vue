<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="cta-block py-20"
  >
    <div class="cta-container container mx-auto px-6" :style="containerStyles">
        <div class="cta-card relative bg-slate-900 dark:bg-primary rounded-[3rem] p-12 md:p-24 overflow-hidden shadow-2xl flex flex-col items-center text-center">
            <!-- Background Accent -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl animate-pulse" />
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-primary/20 rounded-full blur-3xl" />

            <!-- Content Area -->
            <div class="relative z-10 max-w-3xl">
                <h2 
                    class="cta-title text-white text-4xl md:text-6xl font-black tracking-tighter mb-8 leading-tight animate-in fade-in slide-in-from-bottom-4 duration-700"
                    :style="titleStyles"
                >
                    {{ settings.title || 'Start Your Journey With Us Today' }}
                </h2>
                
                <p 
                    class="cta-description text-white/60 text-lg md:text-xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed"
                    :style="contentStyles"
                >
                    {{ settings.description || 'Join thousands of satisfied customers who have already transformed their digital presence. Your success starts here.' }}
                </p>

                <div 
                    v-if="settings.buttonText"
                    class="flex gap-4 justify-center"
                >
                     <Button 
                        size="lg" 
                        class="rounded-full px-10 h-14 font-black transition-all hover:scale-105 active:scale-95 shadow-xl shadow-primary/20"
                     >
                        {{ settings.buttonText }}
                     </Button>
                </div>
            </div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import { 
    getLayoutStyles,
    getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device) as CSSProperties)
const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device) as CSSProperties)

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.cta-block { width: 100%; position: relative; }
</style>
