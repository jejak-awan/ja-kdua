<template>
  <BaseBlock :module="module" :settings="settings" class="tabbed-posts-block">
    <div class="container mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-6">
            <h2 v-if="settings.title" class="text-2xl md:text-3xl font-bold tracking-tight" :style="titleStyles">{{ settings.title }}</h2>
            
            <!-- Custom Tabs -->
             <div class="flex flex-wrap gap-2 md:gap-6 border-b border-gray-200 dark:border-gray-700">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id" 
                    class="pb-2 text-sm font-medium transition-colors relative"
                    :class="activeTab === tab.id ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="min-h-[300px]">
             <div v-if="loading && !posts[activeTab]" class="flex items-center justify-center py-20">
                <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
            </div>
            
            <div v-else :class="['grid gap-6', gridClass]">
                 <article 
                    v-for="post in currentPosts" 
                    :key="post.id"
                    class="group bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-all"
                >
                     <div class="aspect-[3/2] overflow-hidden bg-gray-100 dark:bg-gray-900 relative">
                        <img 
                            v-if="post.featured_image" 
                            :src="post.featured_image" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                         <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <ImageIcon class="w-8 h-8" />
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold mb-2 leading-tight group-hover:text-blue-600 transition-colors">
                            {{ post.title }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ post.excerpt }}</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { ref, computed, onMounted, watch, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Loader2, Image as ImageIcon } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const activeTab = ref('')
const tabs = ref([])
const posts = ref({})
const loading = ref(false)

const parseCategories = () => {
    let cats = settings.value.categories
    if (typeof cats === 'string') {
        cats = cats.split(',').map(c => c.trim()).filter(Boolean)
    }
    
    if (!cats || cats.length === 0) {
         tabs.value = [
             { id: 'tech', label: 'Technology' },
             { id: 'life', label: 'Lifestyle' },
             { id: 'news', label: 'News' }
         ]
    } else {
        tabs.value = cats.map(cat => ({
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
    // In a real app, we'd fetch these from an API or inject them
    // For the shared component, we'll provide some mock data
    const catId = activeTab.value
    return Array.from({ length: getResponsiveValue(settings.value, 'limit', device.value) || 4 }, (_, i) => ({
        id: `${catId}-${i}`,
        title: `${tabs.value.find(t => t.id === catId)?.label || 'Latest'} Article ${i + 1}`,
        excerpt: 'Dynamic content placeholder showing how the tabbed posts will look in the live site.',
        featured_image: null
    }))
})

const gridClass = computed(() => {
    const cols = getResponsiveValue(settings.value, 'columns', device.value) || 3
    if (cols == 2) return 'grid-cols-1 md:grid-cols-2'
    if (cols == 4) return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
})

const titleStyles = computed(() => getTypographyStyles(settings.value, '', device.value))
</script>

<style scoped>
.tabbed-posts-block { width: 100%; }
</style>
