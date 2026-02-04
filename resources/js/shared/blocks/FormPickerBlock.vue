<template>
  <div class="form-picker-block py-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xs font-black uppercase tracking-widest opacity-60">Selection</h3>
      <Badge variant="outline" class="bg-primary/10 text-primary border-primary/20 px-3 py-0.5 rounded-full uppercase tracking-tighter text-[9px] font-black">
        Options Configured
      </Badge>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="(option, index) in options" 
        :key="index"
        class="picker-option group/opt relative p-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 hover:border-primary/40 hover:bg-primary/5 transition-all duration-300 cursor-pointer"
        :class="{ 'opacity-50 grayscale': (option as Record<string, any>).disabled }"
      >
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center group-hover/opt:bg-primary transition-colors duration-300">
                <Box class="w-5 h-5 opacity-40 group-hover/opt:opacity-100 group-hover/opt:text-white" />
            </div>
            <div>
                <h4 class="font-black text-sm tracking-tight">{{ (option as Record<string, any>).label }}</h4>
                <p v-if="(option as Record<string, any>).description" class="text-xs font-medium opacity-50">{{ (option as Record<string, any>).description }}</p>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '../ui'
import Box from 'lucide-vue-next/dist/esm/icons/box.js';
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

const options = computed(() => (settings.value.options as unknown[]) || [
  { label: 'Standard Option', description: 'Regular selection' },
  { label: 'Premium Tier', description: 'Advanced features included' }
])
</script>

<style scoped>
.form-picker-block { width: 100%; }
</style>
