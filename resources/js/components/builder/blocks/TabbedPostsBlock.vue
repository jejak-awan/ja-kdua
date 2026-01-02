<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import Tabs from '@/components/ui/tabs.vue';
import TabsList from '@/components/ui/tabs-list.vue';
import TabsTrigger from '@/components/ui/tabs-trigger.vue';
import TabsContent from '@/components/ui/tabs-content.vue';
import { Loader2 } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: String,
    categories: { 
        type: [Array, String], // Can be ID array or comma-separated string
        default: () => [] 
    },
    limit: { type: Number, default: 6 },
    columns: { type: String, default: '3' },
    padding: { type: String, default: 'py-12' },
    bgColor: String
});

const activeTab = ref('');
const tabs = ref([]);
const posts = ref({}); // Cache by category ID
const loading = ref(false);

const parseCategories = () => {
    // If we have real DB categories, we would fetch them. 
    // For now, we simulate "Category Objects" from the input IDs/Names
    // In a real app, we'd probably have a prop 'availableCategories' or fetch them.
    // Here we'll treat the input as a list of Category Names for simplicity in the builder.
    
    let cats = props.categories;
    if (typeof cats === 'string') {
        cats = cats.split(',').map(c => c.trim()).filter(Boolean);
    }
    
    if (!cats || cats.length === 0) {
         // Fallback defaults
         tabs.value = [
             { id: 'tech', label: 'Technology' },
             { id: 'life', label: 'Lifestyle' },
             { id: 'news', label: 'News' }
         ];
    } else {
        tabs.value = cats.map((cat, idx) => ({
            id: typeof cat === 'object' ? cat.id : cat,
            label: typeof cat === 'object' ? cat.name : cat
        }));
    }
    
    // Set initial active tab
    if (tabs.value.length > 0 && !activeTab.value) {
        activeTab.value = tabs.value[0].id;
        fetchPosts(activeTab.value);
    }
};

const fetchPosts = async (catId) => {
    if (posts.value[catId]) return; // Use cache
    
    loading.value = true;
    try {
        // Simulate API call filtering by category
        const params = {
            type: 'post',
            limit: props.limit,
            category: catId,
            status: 'published'
        };
        
        const response = await api.get('/cms/contents', { params });
        posts.value[catId] = response.data?.data || response.data || [];
    } catch (err) {
        console.warn('TabbedPosts: Failed to fetch', err);
        // Demo Fallback
        posts.value[catId] = Array.from({ length: 4 }, (_, i) => ({
            id: `${catId}-${i}`,
            title: `${catId} Article ${i + 1}`,
            excerpt: 'Dynamic content loaded for this specific category tab.',
            featured_image: null,
            category: { name: catId } // Just ensuring badge shows something
        }));
    } finally {
        loading.value = false;
    }
};

const onTabChange = (val) => {
    activeTab.value = val;
    fetchPosts(val);
};

onMounted(parseCategories);
watch(() => props.categories, parseCategories);

const gridClass = computed(() => {
    const cols = {
        '2': 'grid-cols-1 md:grid-cols-2',
        '3': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        '4': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    };
    return cols[props.columns] || cols['3'];
});

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding].filter(Boolean);
});
</script>

<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6">
             <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-6">
                <h2 v-if="title" class="text-2xl md:text-3xl font-bold tracking-tight">{{ title }}</h2>
                
                <!-- Tab Triggers (Desktop) -->
                 <Tabs :modelValue="activeTab" @update:modelValue="onTabChange" class="w-full md:w-auto">
                    <TabsList class="bg-transparent border-b border-border rounded-none h-auto p-0 flex-wrap gap-2 md:gap-6">
                        <TabsTrigger 
                            v-for="tab in tabs" 
                            :key="tab.id" 
                            :value="tab.id"
                            class="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:text-primary px-0 pb-2 bg-transparent text-muted-foreground hover:text-foreground shadow-none"
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
                
                <div v-else-if="posts[activeTab]" :class="['grid gap-6', gridClass]">
                     <article 
                        v-for="post in posts[activeTab]" 
                        :key="post.id"
                        class="group bg-card border rounded-lg overflow-hidden hover:shadow-md transition-all"
                    >
                         <!-- Small/Compact Card -->
                         <div class="aspect-[3/2] overflow-hidden bg-muted relative">
                            <img 
                                v-if="post.featured_image" 
                                :src="post.featured_image" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                             <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground bg-muted/50">
                                <span class="text-xs">No Image</span>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-bold mb-2 leading-tight group-hover:text-primary transition-colors">
                                {{ post.title }}
                            </h3>
                            <p class="text-xs text-muted-foreground line-clamp-2">{{ post.excerpt }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>
