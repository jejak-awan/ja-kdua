<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { Loader2, ExternalLink } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    category: { type: String, default: '' },
    limit: { type: Number, default: 9 },
    columns: { type: String, default: '3' },
    show_filter: { type: Boolean, default: true },
    show_title: { type: Boolean, default: true },
    show_category: { type: Boolean, default: true },
    style: { type: String, default: 'cards' },
    padding: { type: String, default: 'py-16' },
    bgColor: { type: String, default: '' }
});

const projects = ref([]);
const loading = ref(true);
const activeFilter = ref('all');
const categories = ref(['all']);

const containerClasses = computed(() => {
    return ['transition-all duration-500', props.padding].filter(Boolean);
});

const gridClass = computed(() => {
    const cols = {
        '2': 'grid-cols-1 md:grid-cols-2',
        '3': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        '4': 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
    };
    return cols[props.columns] || cols['3'];
});

const filteredProjects = computed(() => {
    if (activeFilter.value === 'all') return projects.value;
    return projects.value.filter(p => 
        (p.category?.name || p.category) === activeFilter.value
    );
});

const fetchProjects = async () => {
    loading.value = true;
    try {
        const params = {
            type: 'project',
            limit: props.limit,
            status: 'published'
        };
        if (props.category) params.category = props.category;
        
        const response = await api.get('/cms/contents', { params });
        projects.value = response.data?.data || response.data || [];
        
        // Extract unique categories
        const cats = new Set(['all']);
        projects.value.forEach(p => {
            if (p.category?.name) cats.add(p.category.name);
            else if (typeof p.category === 'string') cats.add(p.category);
        });
        categories.value = Array.from(cats);
    } catch (err) {
        console.warn('Portfolio: Failed to fetch projects', err);
        // Fallback demo data
        projects.value = Array.from({ length: props.limit }, (_, i) => ({
            id: i + 1,
            title: `Project ${i + 1}`,
            featured_image: null,
            category: { name: ['Design', 'Development', 'Marketing'][i % 3] },
            slug: '#'
        }));
        categories.value = ['all', 'Design', 'Development', 'Marketing'];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProjects);
watch(() => [props.category, props.limit], fetchProjects);
</script>

<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6">
            <h2 v-if="title" class="text-3xl md:text-4xl font-extrabold mb-8 tracking-tight text-center">{{ title }}</h2>
            
            <!-- Filter -->
            <div v-if="show_filter && categories.length > 1" class="flex flex-wrap justify-center gap-2 mb-10">
                <button 
                    v-for="cat in categories" 
                    :key="cat"
                    @click="activeFilter = cat"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-full transition-all',
                        activeFilter === cat 
                            ? 'bg-primary text-primary-foreground' 
                            : 'bg-muted hover:bg-muted/80 text-foreground'
                    ]"
                >
                    {{ cat === 'all' ? 'All' : cat }}
                </button>
            </div>
            
            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-20">
                <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>
            
            <!-- Grid -->
            <div v-else :class="['grid gap-6', gridClass]">
                <article 
                    v-for="project in filteredProjects" 
                    :key="project.id"
                    :class="[
                        'group relative overflow-hidden transition-all duration-300',
                        style === 'cards' ? 'bg-card border rounded-2xl shadow-sm hover:shadow-xl' : 'rounded-xl'
                    ]"
                >
                    <!-- Image -->
                    <div class="aspect-[4/3] overflow-hidden bg-muted">
                        <img 
                            v-if="project.featured_image" 
                            :src="project.featured_image" 
                            :alt="project.title"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                            <span class="text-xs">No Image</span>
                        </div>
                        
                        <!-- Overlay -->
                        <div 
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center"
                        >
                            <a 
                                :href="`/project/${project.slug || project.id}`"
                                class="w-12 h-12 rounded-full bg-white text-black flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform"
                            >
                                <ExternalLink class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                    
                    <!-- Info -->
                    <div v-if="style === 'cards' && (show_title || show_category)" class="p-4">
                        <span v-if="show_category" class="text-[10px] font-bold text-primary">
                            {{ project.category?.name || project.category || 'Project' }}
                        </span>
                        <h3 v-if="show_title" class="font-bold mt-1 line-clamp-1">{{ project.title }}</h3>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
