<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { Loader2, ExternalLink } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    filterCategory: { type: String, default: '' },
    itemsPerPage: { type: [Number, String], default: 9 },
    columns: { type: [Number, String], default: 3 },
    gap: { type: [Number, String], default: 20 },
    showFilter: { type: Boolean, default: true },
    showTitle: { type: Boolean, default: true },
    showCategory: { type: Boolean, default: true },
    hoverEffect: { type: String, default: 'overlay' },
    imageAspectRatio: { type: String, default: '1:1' },
    overlayColor: { type: String, default: 'rgba(0,0,0,0.6)' }
});

const projects = ref([]);
const loading = ref(true);
const activeFilter = ref('all');
const categories = ref(['all']);

const gridStyles = computed(() => {
    // Handling responsive columns and gap if passed as strings (though renderer usually gets single values per device)
    const cols = parseInt(props.columns) || 3;
    const gapVal = parseInt(props.gap) || 20;
    
    return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: `${gapVal}px`
    };
});

const imageStyles = computed(() => {
    if (props.imageAspectRatio === 'custom') return {};
    const ratio = { '1:1': '100%', '4:3': '75%', '16:9': '56.25%' }[props.imageAspectRatio] || '100%';
    return { paddingBottom: ratio, position: 'relative' };
});

const overlayStyles = computed(() => {
    return { backgroundColor: props.overlayColor };
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
            limit: parseInt(props.itemsPerPage) || 9,
            status: 'published'
        };
        if (props.filterCategory) params.category = props.filterCategory;
        
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
        projects.value = Array.from({ length: parseInt(props.itemsPerPage) || 9 }, (_, i) => ({
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
watch(() => [props.filterCategory, props.itemsPerPage], fetchProjects);
</script>

<template>
    <div class="portfolio-block">
        <h2 v-if="title" class="portfolio-title">{{ title }}</h2>
        
        <!-- Filter -->
        <div v-if="showFilter && categories.length > 1" class="portfolio-filter">
            <button 
                v-for="cat in categories" 
                :key="cat"
                @click="activeFilter = cat"
                :class="['filter-btn', { 'filter-btn--active': activeFilter === cat }]"
            >
                {{ cat === 'all' ? 'All' : cat }}
            </button>
        </div>
        
        <!-- Loading -->
        <div v-if="loading" class="portfolio-loading">
            <Loader2 class="loading-icon" />
        </div>
        
        <!-- Grid -->
        <div v-else class="portfolio-grid" :style="gridStyles">
            <article 
                v-for="project in filteredProjects" 
                :key="project.id"
                :class="['portfolio-item', `portfolio-item--${hoverEffect}`]"
            >
                <div class="item-media" :style="imageStyles">
                    <img 
                        v-if="project.featured_image" 
                        :src="project.featured_image" 
                        :alt="project.title"
                        class="item-img"
                    />
                    <div v-else class="item-placeholder">
                        <span>No Image</span>
                    </div>
                    
                    <!-- Overlay -->
                    <div v-if="hoverEffect !== 'none'" class="item-overlay" :style="overlayStyles">
                        <div class="overlay-content">
                            <span v-if="showCategory" class="item-category">
                                {{ project.category?.name || project.category || 'Project' }}
                            </span>
                            <h3 v-if="showTitle" class="item-title">{{ project.title }}</h3>
                            <a 
                                :href="`/project/${project.slug || project.id}`"
                                class="item-link"
                            >
                                <ExternalLink :size="20" />
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>

<style scoped>
.portfolio-block { width: 100%; }
.portfolio-title { font-size: 2rem; font-weight: 800; text-align: center; margin-bottom: 2rem; }

.portfolio-filter { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-bottom: 2.5rem; }
.filter-btn { padding: 8px 24px; font-size: 0.875rem; font-weight: 600; border-radius: 9999px; background: #f3f4f6; color: #374151; transition: all 0.2s; border: none; cursor: pointer; }
.filter-btn:hover { background: #e5e7eb; }
.filter-btn--active { background: #3b82f6; color: #ffffff; }

.portfolio-loading { display: flex; align-items: center; justify-content: center; padding: 4rem 0; }
.loading-icon { width: 2rem; height: 2rem; animation: spin 1s linear infinite; color: #3b82f6; }

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.portfolio-grid { width: 100%; }

.portfolio-item { position: relative; overflow: hidden; border-radius: 12px; transition: all 0.3s; }

.item-media { width: 100%; overflow: hidden; background: #f3f4f6; }
.item-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.item-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #9ca3af; }

.item-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; color: #ffffff; }
.overlay-content { text-align: center; padding: 1.5rem; }

.portfolio-item:hover .item-overlay { opacity: 1; }

.item-category { display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
.item-title { font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; }

.item-link { display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 50%; background: #ffffff; color: #111827; transition: transform 0.2s; }
.item-link:hover { transform: scale(1.1); }

/* Hover Effects */
.portfolio-item--zoom:hover .item-img { transform: scale(1.1); }
.portfolio-item--grayscale .item-img { filter: grayscale(100%); }
.portfolio-item--grayscale:hover .item-img { filter: grayscale(0); }
</style>
