<template>
  <BaseBlock
    :module="module"
    :mode="mode"
    :settings="settings"
    class="team-member-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Team Member'"
  >
    <div 
      class="flex transition-colors duration-300 w-full p-8 rounded-[3rem] bg-white dark:bg-slate-900 border border-slate-50 dark:border-slate-800 shadow-xl hover:-translate-y-3 group" 
      :class="[
        layout === 'stacked' ? 'flex-col items-center gap-8 text-center' : 'flex-row items-center gap-10 text-left',
        getVal(settings, 'alignment', device) === 'right' ? 'flex-row-reverse text-right' : ''
      ]"
      :style="containerStyles"
    >
      <!-- Avatar -->
      <Avatar :style="avatarStyles" class="flex-shrink-0 shadow-2xl transition-transform duration-700 group-hover:scale-110 group-hover:rotate-3 border-4 border-slate-50 dark:border-slate-800 overflow-hidden rounded-[2.5rem]">
        <AvatarImage 
          v-if="getVal(settings, 'image', device)" 
          :src="getVal(settings, 'image', device)" 
          :alt="getVal(settings, 'name', device)" 
          class="object-cover"
        />
        <AvatarFallback class="bg-slate-50 dark:bg-slate-950 text-primary">
          <User class="w-1/2 h-1/2 scale-150 opacity-20" />
        </AvatarFallback>
      </Avatar>
      
      <!-- Info -->
      <div class="member-info flex-1">
        <h3 
          class="font-black text-2xl mb-2 outline-none tracking-tighter text-slate-900 dark:text-white" 
          :style="nameStyles"
          :contenteditable="mode === 'edit'"
          @blur="(e: any) => updateField('name', (e.target as HTMLElement).innerText)"
          v-text="getVal(settings, 'name', device) || 'Design Luminary'"
        ></h3>
        <p 
          class="text-xs font-black uppercase tracking-[0.2em] text-primary mb-5 outline-none" 
          :style="positionStyles"
          :contenteditable="mode === 'edit'"
          @blur="(e: any) => updateField('position', (e.target as HTMLElement).innerText)"
          v-text="getVal(settings, 'position', device) || 'Principal Architect'"
        ></p>
        <div 
          v-if="bioValue || mode === 'edit'" 
          class="mb-8 leading-relaxed outline-none text-slate-500 dark:text-slate-400 font-medium" 
          :style="bioStyles"
          :contenteditable="mode === 'edit'"
          @blur="(e: any) => updateField('bio', (e.target as HTMLElement).innerText)"
          v-text="bioValue || 'Crafting experiences that bridge the gap between human intuition and digital precision.'"
        ></div>
        
        <!-- Social Links -->
        <div class="flex flex-wrap gap-4" :class="socialAlignmentClass">
          <template v-if="socialLinks.length">
              <a 
                v-for="(link, index) in socialLinks" 
                :key="index"
                :href="link.url || '#'"
                class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-950 text-slate-400 flex items-center justify-center hover:bg-primary hover:text-white hover:-translate-y-1 hover:rotate-6 transition-colors duration-300 shadow-sm border border-slate-100 dark:border-slate-800"
                @click.prevent
              >
                <LucideIcon :name="link.network || 'globe'" class="w-4 h-4" />
              </a>
          </template>
          <template v-else-if="mode === 'edit'">
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 italic">Connect later</span>
          </template>
        </div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import User from 'lucide-vue-next/dist/esm/icons/user.js';import BaseBlock from '../components/BaseBlock.vue'
import { Avatar, AvatarImage, AvatarFallback } from '../ui'
import { LucideIcon } from '@/components/ui';
import { 
  getVal, 
  getLayoutStyles, 
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const bioValue = computed(() => getVal(settings.value, 'bio', device.value))
const layout = computed(() => getVal(settings.value, 'layout', device.value) || 'stacked')
const alignment = computed(() => getVal(settings.value, 'alignment', device.value) || 'center')

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const avatarStyles = computed(() => {
    const size = parseInt(getVal(settings.value, 'imageSize', device.value)) || 150
    return {
        width: `${size}px`,
        height: `${size}px`
    }
})

const socialAlignmentClass = computed(() => {
    if (layout.value === 'stacked') return 'justify-center'
    if (alignment.value === 'right') return 'justify-end'
    return 'justify-start' 
})

const socialLinks = computed(() => settings.value.socialLinks || [])

const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const positionStyles = computed(() => getTypographyStyles(settings.value, 'position_', device.value))
const bioStyles = computed(() => getTypographyStyles(settings.value, 'bio_', device.value))

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.team-member-block { width: 100%; }

/* Edit mode overrides */
div[contenteditable="true"]:empty:before {
  content: 'Join the story...';
  opacity: 0.2;
}
</style>

