<template>
  <BaseBlock :module="module" :mode="mode" :settings="settings" class="tabbed-posts-block">
    <div class="container mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-6">
            <h2 v-if="settings.title" class="text-2xl md:text-3xl font-bold tracking-tight" :style="titleStyles">{{ settings.title }}</h2>
            
            <Tabs v-model="activeTab" class="w-full md:w-auto">
                <TabsList class="bg-transparent border-b border-slate-200 dark:border-slate-800 p-0 h-auto gap-8 rounded-none">
                    <TabsTrigger 
                        v-for="tab in tabs" 
                        :key="tab.id" 
                        :value="tab.id"
                        class="pb-3 rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent shadow-none font-bold text-slate-500 hover:text-slate-900 transition-colors"
                    >
                        {{ tab.label }}
                    </TabsTrigger>
                </TabsList>
            </Tabs>
        </div>
        
        <!-- Content Area -->
        <div class="min-h-[300px]">
             <div v-if="loading && !posts[activeTab]" class="flex items-center justify-center py-20">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>
            
            <div v-else :class="['grid gap-8', gridClass]">
                 <Card 
                    v-for="post in currentPosts" 
                    :key="post.id"
                    class="group overflow-hidden hover:shadow-2xl transition-[width] duration-500 border-none rounded-[24px] bg-white dark:bg-slate-900 shadow-lg"
                >
                    <div class="aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800 relative">
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        />
                         <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                            <ImageIcon class="w-12 h-12 opacity-30" />
                        </div>
                    </div>
                    
                    <CardContent class="p-6">
                        <CardTitle class="text-lg font-black mb-3 leading-tight group-hover:text-primary transition-colors border-none">
                            {{ post.title }}
                        </CardTitle>
                        <CardDescription class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed font-medium">
                            {{ post.excerpt }}
                        </CardDescription>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Tabs, TabsList, TabsTrigger, Card, CardContent, CardTitle, CardDescription } from '../ui'
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockProps, BuilderInstance } from '@/types/builder'

const props = withDefaults(defineProps<BlockProps>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance>('builder', null as any)
const settings = computed(() => props.settings || props.module?.settings || {})
const currentDevice = computed(() => props.device || builder?.device?.value || 'desktop')

const activeTab = ref<string>('')
const tabs = ref<{ id: string; label: string }[]>([])
const posts = ref<Record<string, any[]>>({})
const loading = ref(false)

const parseCategories = () => {
    let cats = settings.value.categories
    if (typeof cats === 'string') {
        cats = cats.split(',').map((c: string) => c.trim()).filter(Boolean)
    }
    
    if (!cats || cats.length === 0) {
         tabs.value = [
             { id: 'tech', label: 'Technology' },
             { id: 'life', label: 'Lifestyle' },
             { id: 'news', label: 'News' }
         ]
    } else {
        tabs.value = (cats as any[]).map(cat => ({
            id: typeof cat === 'object' ? cat.id : cat,
            label: typeof cat === 'object' ? cat.name : cat
        }))
    }
    
    if (tabs.value.length > 0 && !activeTab.value) {
        activeTab.value = tabs.value[0].id
    }
}

onMounted(parseCategories)
watch(() => settings.value.categories, parseCategories)

const currentPosts = computed(() => {
    const catId = activeTab.value
    const limit = getResponsiveValue(settings.value, 'limit', currentDevice.value) || 4
    return Array.from({ length: Number(limit) }, (_, i) => ({
        id: `${catId}-${i}`,
        title: `${tabs.value.find(t => t.id === catId)?.label || 'Latest'} Article: Breaking the Boundaries of Design`,
        excerpt: 'Explore the future of web design and content management with our latest deep-dive articles.',
        featured_image: null
    }))
})

const gridClass = computed(() => {
    const cols = getResponsiveValue(settings.value, 'columns', currentDevice.value) || 3
    if (cols == 2) return 'grid-cols-1 md:grid-cols-2'
    if (cols == 4) return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
})

const titleStyles = computed(() => getTypographyStyles(settings.value, '', currentDevice.value))
</script>

<style scoped>
.tabbed-posts-block { width: 100%; }
</style>
