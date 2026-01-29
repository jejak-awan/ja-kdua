<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :settings="settings"
    class="testimonial-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Testimonial'"
  >
    <Card 
        class="testimonial-card relative p-12 overflow-hidden transition-colors duration-700 border-none shadow-2xl rounded-[3rem] group" 
        :class="[
          (getVal(settings, 'alignment', device) || 'text-center')
        ]"
        :style="containerStyles"
    >
      <!-- Quote Icon Overlay -->
      <QuoteIcon 
        v-if="getVal(settings, 'showQuoteIcon', device) !== false" 
        class="absolute -top-10 -right-10 w-48 h-48 opacity-[0.03] transform rotate-180 z-0 pointer-events-none transition-colors duration-1000 group-hover:scale-125 group-hover:rotate-[195deg]"
        :style="{ color: getVal(settings, 'quoteIconColor', device) || 'var(--primary)' }"
      />
      
      <!-- Content Wrapper -->
      <CardContent class="p-0 relative z-10 flex flex-col h-full" :class="{ 'items-center': (getVal(settings, 'alignment', device) || 'text-center') === 'text-center' }">
<!-- Quote Icon Small -->
        <div 
          v-if="getVal(settings, 'showQuoteIcon', device) !== false" 
          class="mb-10 w-14 h-14 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-sm border border-slate-100 dark:border-slate-700 transition-[width] duration-500 group-hover:rotate-12 group-hover:bg-primary group-hover:text-white"
        >
          <QuoteIcon 
            class="w-7 h-7 transform rotate-180"
            :style="quoteIconStyles"
          />
        </div>

        <!-- Rating -->
        <div v-if="getVal(settings, 'rating', device) > 0" class="flex gap-1.5 mb-10 rating-stars transition-[width] duration-500 group-hover:scale-110">
           <StarIcon 
              v-for="i in 5" 
              :key="i"
              class="w-5 h-5"
              :class="i <= getVal(settings, 'rating', device) ? 'fill-amber-400 text-amber-400' : 'text-slate-200 dark:text-slate-700'"
           />
        </div>
        
        <!-- Content -->
        <div 
          class="testimonial-text mb-12 max-w-4xl font-serif italic text-pretty leading-relaxed text-slate-900 dark:text-white drop-shadow-sm transition-colors duration-500 group-hover:text-primary" 
          :style="contentStyles"
          :contenteditable="mode === 'edit'"
          @blur="(e: any) => updateResponsiveField('content', (e.target as HTMLElement).innerText)"
        >
          "{{ getVal(settings, 'content', device) || 'This exceeds every expectation we had for a design system. It is art in digital form.' }}"
        </div>
        
        <!-- Author Section -->
        <div class="testimonial-author mt-auto flex items-center gap-6" :class="{ 'flex-col text-center': (getVal(settings, 'alignment', device)) === 'text-center' }">
          <Avatar class="h-20 w-20 shadow-xl border-4 border-white dark:border-slate-800 transition-colors duration-700 group-hover:scale-110 group-hover:rotate-6 rounded-[1.5rem] overflow-hidden">
            <AvatarImage 
              v-if="getVal(settings, 'authorImage', device)"
              :src="getVal(settings, 'authorImage', device)"
              alt="Author"
              class="object-cover"
            />
            <AvatarFallback class="bg-slate-50 dark:bg-slate-950 text-primary">
                <UserIcon class="w-1/2 h-1/2 opacity-20" />
            </AvatarFallback>
          </Avatar>
          
          <div class="author-info flex flex-col gap-1" :class="{ 'items-center': (getVal(settings, 'alignment', device)) === 'text-center' }">
            <CardTitle 
              class="author-name font-black text-xl text-slate-900 dark:text-white tracking-tight border-none"
              :style="authorNameStyles"
              :contenteditable="mode === 'edit'"
              @blur="(e: any) => updateResponsiveField('authorName', (e.target as HTMLElement).innerText)"
            >
              {{ getVal(settings, 'authorName', device) || 'Sarah Johnson' }}
            </CardTitle>
            <CardDescription class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">
              <span 
                  :style="authorTitleStyles"
                  :contenteditable="mode === 'edit'"
                   @blur="(e: any) => updateResponsiveField('authorTitle', (e.target as HTMLElement).innerText)"
              >{{ getVal(settings, 'authorTitle', device) || 'Creative Director' }}</span>
            </CardDescription>
            
             <img 
              v-if="getVal(settings, 'companyLogo', device)"
              :src="getVal(settings, 'companyLogo', device)"
              alt="Company Logo"
              class="h-6 w-auto mt-4 opacity-40 grayscale hover:grayscale-0 transition-colors duration-300"
            />
          </div>
        </div>
      </CardContent>
    </Card>
  </BaseBlock>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue'
import QuoteIcon from 'lucide-vue-next/dist/esm/icons/quote.js';
import UserIcon from 'lucide-vue-next/dist/esm/icons/user.js';
import StarIcon from 'lucide-vue-next/dist/esm/icons/star.js';import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardContent, CardTitle, CardDescription, Avatar, AvatarImage, AvatarFallback } from '../ui'
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

const containerStyles = computed(() => {
  const layout = getLayoutStyles(settings.value, device.value)
  
  const hoverScale = getVal(settings.value, 'hover_scale', device.value) || 1
  const hoverBrightness = getVal(settings.value, 'hover_brightness', device.value) || 100

  return {
      ...layout,
      '--hover-scale': hoverScale,
      '--hover-brightness': `${hoverBrightness}%`
  }
})

const quoteIconStyles = computed(() => {
  return {
    color: getVal(settings.value, 'quoteIconColor', device.value) || 'currentColor'
  }
})

const contentStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'content_', device.value)
    return {
        fontSize: (getVal(settings.value, 'content_font_size', device.value) || 20) + 'px',
        ...styles
    }
})

const authorNameStyles = computed(() => getTypographyStyles(settings.value, 'author_name_', device.value))
const authorTitleStyles = computed(() => getTypographyStyles(settings.value, 'author_title_', device.value))

const updateResponsiveField = (fieldName: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder?.updateModuleSettings(props.module.id, { [fieldName]: value })
}
</script>

<style scoped>
.testimonial-block { 
  width: 100%; 
}
.testimonial-card {
    background-color: #fff;
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.dark .testimonial-card {
    background-color: #0f172a;
}
.testimonial-card:hover {
  transform: scale(var(--hover-scale, 1));
  filter: brightness(var(--hover-brightness, 100%));
}
</style>

