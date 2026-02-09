<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="alert-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Alert Notification'"
  >
    <div 
        class="alert-container mx-auto px-6 py-4 rounded-2xl border-2 flex items-center gap-4 animate-in fade-in slide-in-from-top-2 duration-500" 
        :class="[variantClasses]"
        :style="containerStyles"
    >
        <div class="alert-icon-wrapper shrink-0">
             <LucideIcon :name="iconName" class="w-6 h-6" />
        </div>
        <div class="alert-content flex-1 min-w-0">
             <h4 v-if="settings.title" class="font-black text-sm uppercase tracking-widest mb-1">{{ settings.title }}</h4>
             <p class="text-sm font-medium opacity-80 leading-relaxed">{{ settings.content || 'Important notification message.' }}</p>
        </div>
        <Button 
            v-if="settings.showClose" 
            variant="ghost" 
            size="icon" 
            class="rounded-full hover:bg-black/5 dark:hover:bg-white/5"
        >
             <X class="w-4 h-4" />
        </Button>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import { LucideIcon } from '@/components/ui'
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { 
    getLayoutStyles
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

const variantClasses = computed(() => {
    const variant = settings.value.variant || 'info'
    const variants: Record<string, string> = {
        info: 'bg-primary/5 border-primary/20 text-primary',
        success: 'bg-emerald-500/5 border-emerald-500/20 text-emerald-600',
        warning: 'bg-amber-500/5 border-amber-500/20 text-amber-600',
        destructive: 'bg-destructive/5 border-destructive/20 text-destructive'
    }
    return variants[variant as string] || variants.info
})

const iconName = computed(() => {
    const variant = settings.value.variant || 'info'
    const icons: Record<string, string> = {
        info: 'Info',
        success: 'CheckCircle2',
        warning: 'AlertTriangle',
        destructive: 'AlertOctagon'
    }
    return icons[variant as string] || icons.info
})

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.alert-block { width: 100%; position: relative; }
</style>
