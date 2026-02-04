<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="form-step-block"
  >
    <template #default="{ settings }">
      <div 
        class="form-step space-y-8" 
        :style="stepStyles(settings)"
      >
        <div class="step-header mb-10">
          <Badge v-if="settings.showBadge !== false" variant="outline" class="mb-4 bg-primary/10 text-primary border-primary/20 px-4 py-1 rounded-full uppercase tracking-widest text-[10px] font-black">
            Step {{ index + 1 }}
          </Badge>
          <h2 v-if="settings.title" class="text-3xl md:text-5xl font-black tracking-tighter mb-4">{{ settings.title }}</h2>
          <p v-if="settings.description" class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed max-w-2xl">{{ settings.description }}</p>
        </div>
        
        <div class="step-content">
          <slot />
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Badge } from '../ui'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
  index?: number;
}>(), {
  mode: 'view',
  device: 'desktop',
  index: 0
})

const stepStyles = (settings: ModuleSettings): CSSProperties => {
  return {
    opacity: settings.visible === false ? 0.3 : 1,
    pointerEvents: settings.visible === false ? 'none' : 'auto'
  } as CSSProperties
}
</script>

<style scoped>
.form-step-block { width: 100%; }
</style>
