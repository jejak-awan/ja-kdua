<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="author-block py-16"
  >
    <div class="author-container container mx-auto px-6 max-w-4xl" :style="containerStyles">
        <div class="author-card relative group p-12 rounded-[3.5rem] bg-slate-50 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-800 flex flex-col items-center text-center transition-all duration-700 hover:border-primary/40 hover:bg-primary/5">
            <!-- Background Decoration -->
            <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-700 pointer-events-none">
                 <svg width="100%" height="100%" class="text-primary"><pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="currentColor" /></pattern><rect width="100%" height="100%" fill="url(#dots)" /></svg>
            </div>

            <!-- Avatar -->
            <div class="relative mb-10 w-40 h-40">
                <div class="absolute inset-0 rounded-[2.5rem] bg-primary rotate-6 transition-transform group-hover:rotate-12 duration-700" />
                <div class="absolute inset-0 rounded-[2.5rem] bg-white dark:bg-slate-800 p-1">
                    <img 
                        :src="(settings.avatar as string) || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=800'" 
                        class="w-full h-full object-cover rounded-[2.2rem] grayscale group-hover:grayscale-0 transition-all duration-700"
                        alt="Author"
                    />
                </div>
            </div>

            <!-- Bio -->
            <div class="relative z-10">
                <h3 class="text-3xl font-black tracking-tighter mb-4 group-hover:text-primary transition-colors">{{ settings.name || 'Alexander Wright' }}</h3>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary mb-6">{{ settings.role || 'Principal Designer & Visionary' }}</p>
                <div class="w-12 h-1 bg-slate-200 dark:bg-slate-800 mx-auto mb-8 rounded-full group-hover:w-24 group-hover:bg-primary transition-all duration-700" />
                <p class="text-lg font-medium text-slate-500 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">{{ settings.bio || 'Leading the next generation of digital experiences through intentional design and forward-thinking architecture.' }}</p>
            </div>

            <!-- Social -->
            <div class="flex gap-4 mt-10">
                 <Button variant="outline" size="icon" class="rounded-2xl w-12 h-12 hover:bg-primary hover:text-white transition-all">
                    <Twitter class="w-5 h-5" />
                 </Button>
                 <Button variant="outline" size="icon" class="rounded-2xl w-12 h-12 hover:bg-primary hover:text-white transition-all">
                    <Github class="w-5 h-5" />
                 </Button>
            </div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Button } from '../ui'
import Twitter from 'lucide-vue-next/dist/esm/icons/twitter.js';
import Github from 'lucide-vue-next/dist/esm/icons/github.js';
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

const containerStyles = computed((): CSSProperties => {
    return getLayoutStyles(settings.value, props.device) as CSSProperties
})
</script>

<style scoped>
.author-block { width: 100%; position: relative; }
</style>
