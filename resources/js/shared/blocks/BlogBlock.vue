<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings }">
      <div class="blog-block w-full">
        <h2 
          v-if="settings.title || mode === 'edit'" 
          class="blog-title mb-12" 
          :style="titleDisplayStyles"
          :contenteditable="mode === 'edit'"
          @blur="e => updateResponsiveField('title', e.target.innerText)"
          v-text="settings.title"
        >
        </h2>

        <div class="blog-grid" :style="gridStyles">
          <Card 
            v-for="post in mockPosts" 
            :key="post.id"
            class="blog-post group flex flex-col border-none shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 overflow-hidden rounded-[24px]"
            :style="postStyles"
          >
            <!-- Featured Image -->
            <div v-if="settings.showImage !== false" class="post-image relative overflow-hidden" :style="imageStyles">
              <div class="absolute inset-0 bg-slate-100 flex items-center justify-center transition-transform duration-700 group-hover:scale-110">
                <LucideIcon name="Image" class="w-12 h-12 text-slate-300 opacity-50" />
              </div>
              <Badge 
                v-if="settings.showCategory !== false" 
                class="absolute top-4 left-4 z-10 rounded-full font-bold px-4"
              >
                {{ post.category }}
              </Badge>
            </div>
            
            <!-- Content -->
            <CardContent class="p-8 flex flex-col flex-grow">
              <CardTitle 
                class="post-title text-xl font-bold mb-4 line-clamp-2 leading-tight group-hover:text-primary transition-colors border-none" 
                :style="itemTitleStyles"
              >
                {{ post.title }}
              </CardTitle>
              
              <CardDescription 
                v-if="settings.showExcerpt !== false" 
                class="post-excerpt mb-8 line-clamp-3 text-slate-500 font-medium leading-relaxed" 
                :style="excerptStyles"
              >
                {{ post.excerpt }}
              </CardDescription>
              
              <!-- Meta / Author -->
              <div class="mt-auto pt-6 border-t border-slate-100 flex items-center gap-3">
                 <Avatar v-if="settings.showAuthor !== false" class="w-8 h-8">
                    <AvatarFallback class="bg-primary/10 text-primary text-[10px] font-bold">
                        {{ post.author.split(' ').map(n => n[0]).join('') }}
                    </AvatarFallback>
                 </Avatar>
                 <div class="flex flex-col">
                    <span v-if="settings.showAuthor !== false" class="text-xs font-bold text-slate-900">{{ post.author }}</span>
                    <span v-if="settings.showDate !== false" class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">{{ post.date }}</span>
                 </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardTitle, CardDescription, CardContent, Badge, Avatar, AvatarFallback } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module?.settings || {})

// Mock posts for preview
const mockPosts = computed(() => {
  const count = getVal(settings.value, 'itemsPerPage', props.device) || 6
  return Array.from({ length: Math.min(count, 12) }, (_, i) => ({
    id: i + 1,
    title: `How to Build Premium CMS Blocks with Shadcn and Vue`,
    excerpt: 'Explore the latest techniques in modern web design, focus on accessibility, performance, and unmatched aesthetics for your next project.',
    category: 'Design',
    author: 'Antigravity AI',
    date: 'JAN 24, 2026'
  }))
})

const gridStyles = computed(() => {
  const layout = getVal(settings.value, 'layout', props.device) || 'grid'
  const columns = getVal(settings.value, 'columns', props.device) || 3
  const gap = getVal(settings.value, 'gap', props.device) || 32
  
  if (layout === 'list') {
    return { display: 'flex', flexDirection: 'column', gap: `${gap}px` }
  }
  return { 
    display: 'grid', 
    gridTemplateColumns: `repeat(${columns}, 1fr)`, 
    gap: `${gap}px` 
  }
})

const postStyles = computed(() => ({
    backgroundColor: settings.value.cardBackgroundColor || '#ffffff',
}))

const imageStyles = computed(() => {
  const ratio = settings.value.imageAspectRatio || '16:9'
  const ratioMap = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }
  return { paddingTop: ratioMap[ratio] || '56.25%' }
})

const titleDisplayStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'item_title_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit' || !builder) return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.blog-block { width: 100%; }
</style>
