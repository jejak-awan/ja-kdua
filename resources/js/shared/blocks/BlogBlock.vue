<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="blog-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Blog Posts'"
    :style="(cardStyles as any)"
  >
    <div class="blog-wrapper w-full" :style="(containerStyles as any)">
      <h2 
        v-if="settings.title || mode === 'edit'" 
        class="blog-main-title mb-16 outline-none tracking-tighter" 
        :style="(mainTitleStyles as any)"
        :contenteditable="mode === 'edit'"
        @blur="(e: FocusEvent) => updateField('title', (e.target as HTMLElement).innerText)"
        v-text="(settings.title as string) || 'Insights & Stories'"
      ></h2>

      <div class="blog-grid transition-[width] duration-500" :style="(gridStyles as any)">
        <Card 
          v-for="post in posts" 
          :key="post.id"
          class="blog-post group flex flex-col border-none shadow-2xl overflow-hidden rounded-[3rem] transition-colors duration-700 bg-white dark:bg-slate-900 border border-slate-50 dark:border-slate-800 hover:-translate-y-4"
          :style="(postStyles as any)"
        >
          <!-- Featured Image -->
          <div 
            v-if="settings.showImage !== false" 
            class="post-image relative overflow-hidden transition-[width] duration-500" 
            :style="(imageWrapperStyles as any)"
          >
            <div class="absolute inset-0 bg-slate-50 dark:bg-slate-950 flex items-center justify-center overflow-hidden">
                <template v-if="post.featured_image">
                    <img :src="post.featured_image" :alt="post.title" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                </template>
                <template v-else>
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 z-10" />
                    <LucideIcon name="Image" class="w-16 h-16 text-slate-100 dark:text-slate-800 transition-transform duration-1000 group-hover:scale-110" />
                </template>
            </div>
            
            <!-- Category Badge -->
            <Badge 
              v-if="settings.showCategory !== false && post.category" 
              class="absolute top-6 left-6 z-20 rounded-2xl font-black px-5 py-2 text-[10px] uppercase tracking-widest bg-white/90 dark:bg-slate-900/90 text-primary border-none shadow-xl hover:bg-primary hover:text-white transition-colors transform -translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100"
            >
              {{ post.category.name }}
            </Badge>
          </div>
          
          <!-- Content -->
          <CardContent class="p-10 flex flex-col flex-grow relative z-20">
            <CardTitle 
              class="post-title text-2xl font-black mb-4 line-clamp-2 leading-tight tracking-tight text-slate-900 dark:text-white group-hover:text-primary transition-colors border-none" 
              :style="(itemTitleStyles as any)"
            >
              <a :href="mode === 'view' ? `/blog/${post.slug}` : '#'" @click="mode === 'edit' ? $event.preventDefault() : null">
                {{ post.title }}
              </a>
            </CardTitle>
            
            <CardDescription 
              v-if="settings.showExcerpt !== false" 
              class="post-excerpt mb-10 line-clamp-3 text-slate-500 dark:text-slate-400 font-medium leading-relaxed" 
              :style="(excerptStyles as any)"
            >
              {{ post.excerpt }}
            </CardDescription>
            
            <!-- Meta Footer -->
            <div class="mt-auto pt-8 border-t border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
               <div v-if="settings.showAuthor !== false && post.author" class="flex items-center gap-3">
                  <Avatar class="w-10 h-10 shadow-lg border-2 border-slate-50 dark:border-slate-800 rounded-2xl overflow-hidden">
                     <AvatarFallback class="bg-primary/5 text-primary text-xs font-black">
                         {{ post.author.name ? post.author.name.split(' ').map((n: string) => n[0]).join('') : 'U' }}
                     </AvatarFallback>
                  </Avatar>
                  <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ post.author.name || 'User' }}</span>
               </div>
               
               <div v-if="settings.showDate !== false" class="flex flex-col items-end">
                  <span class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em]">{{ formatDate(post.published_at || post.created_at) }}</span>
               </div>
            </div>
          </CardContent>
        </Card>
      </div>
      
      <!-- Empty State -->
      <div v-if="posts.length === 0 && !loading" class="text-center py-20 opacity-50 font-bold">
        No posts found.
      </div>
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <div class="inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject, ref, onMounted, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardTitle, CardDescription, CardContent, Badge, Avatar, AvatarFallback } from '../ui'
import { LucideIcon } from '@/components/ui';
import api from '@/services/api'
import { 
  getVal, 
  getLayoutStyles, 
  getTypographyStyles 
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const postData = ref<any[]>([])
const loading = ref(false)

const posts = computed(() => postData.value)

const fetchPosts = async () => {
    loading.value = true
    try {
        const count = getVal<number>(settings.value, 'itemsPerPage', props.device) || 6
        const category = getVal<string>(settings.value, 'category', props.device)
        const orderBy = getVal<string>(settings.value, 'orderBy', props.device) || 'date'
        const order = getVal<string>(settings.value, 'order', props.device) || 'desc'
        
        let sortField = 'published_at'
        if (orderBy === 'title') sortField = 'title'
        if (orderBy === 'views') sortField = 'views'
        
        const sort = order === 'desc' ? `-${sortField}` : sortField
        
        const response = await api.get('/cms/contents', {
            params: {
                type: 'post',
                category: category || undefined,
                limit: count,
                sort: sort
            }
        })
        
        if (response.data?.success) {
            postData.value = response.data.data
        }
    } catch (error) {
        console.error('Failed to fetch posts:', error)
    } finally {
        loading.value = false
    }
}

onMounted(fetchPosts)
watch(() => [
    settings.value.itemsPerPage,
    settings.value.category,
    settings.value.orderBy,
    settings.value.order
], fetchPosts)

const formatDate = (dateStr: string) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).toUpperCase()
}

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => getLayoutStyles(settings.value, props.device))

const gridStyles = computed(() => {
  const layout = getVal<string>(settings.value, 'layout', props.device) || 'grid'
  const columns = getVal<number>(settings.value, 'columns', props.device) || 3
  const gap = getVal<number>(settings.value, 'gap', props.device) || 32
  
  if (layout === 'list') {
    return { 
        display: 'flex', 
        flexDirection: 'column' as const, 
        gap: `${gap}px` 
    }
  }
  return { 
    display: 'grid', 
    gridTemplateColumns: props.device === 'mobile' ? '1fr' : (props.device === 'tablet' ? 'repeat(2, 1fr)' : `repeat(${columns}, 1fr)`), 
    gap: `${gap}px` 
  }
})

const postStyles = computed(() => ({
    backgroundColor: getVal<string>(settings.value, 'cardBackgroundColor', props.device) || 'transparent',
}))

const imageWrapperStyles = computed(() => {
  const ratio = getVal<string>(settings.value, 'imageAspectRatio', props.device) || '16:9'
  const ratioMap: Record<string, string> = { '16:9': '56.25%', '4:3': '75%', '1:1': '100%' }
  return { paddingTop: ratioMap[ratio] || '56.25%' }
})

const mainTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const itemTitleStyles = computed(() => getTypographyStyles(settings.value, 'item_title_', props.device))
const excerptStyles = computed(() => getTypographyStyles(settings.value, 'excerpt_', props.device))

const updateField = (key: string, value: string) => {
  if (props.mode !== 'edit' || !builder) return
  builder.updateModuleSettings(props.module.id, { [key]: value })
}
</script>

<style scoped>
.blog-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.blog-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.blog-transition-enter-active, .blog-transition-leave-active { transition: all 0.5s ease-out; }
.blog-transition-enter-from, .blog-transition-leave-to { opacity: 0; transform: translateY(20px); }
</style>
