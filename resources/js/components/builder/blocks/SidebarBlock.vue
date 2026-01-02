<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Search, Calendar, Tag, Archive, User, Loader2 } from 'lucide-vue-next';
import api from '@/services/api';
import { parseResponse, ensureArray } from '@/utils/responseParser';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    location: { type: String, default: 'sidebar-1' },
    title: { type: String, default: '' },
    show_search: { type: Boolean, default: true },
    show_categories: { type: Boolean, default: true },
    show_recent_posts: { type: Boolean, default: true },
    show_tags: { type: Boolean, default: true },
    show_archive: { type: Boolean, default: false },
    style: { type: String, default: 'card' },
    padding: { type: String, default: 'py-8' }
});

const widgets = ref([]);
const loading = ref(false);

const fetchWidgets = async () => {
    if (!props.location) return;

    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/widgets/location/${props.location}`);
        const { data } = parseResponse(response);
        widgets.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch widgets:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchWidgets);
watch(() => props.location, fetchWidgets);

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const widgetClasses = computed(() => {
    const styles = {
        card: 'bg-card border rounded-2xl p-6 shadow-sm',
        minimal: 'border-b pb-6',
        filled: 'bg-muted rounded-2xl p-6'
    };
    return styles[props.style] || styles.card;
});

// Helper to determine if we should show fallback (if no widgets in location)
const showFallback = computed(() => widgets.value.length === 0 && !loading.value);
</script>

<template>
    <aside :class="containerClasses" class="relative">
        <div v-if="loading" class="absolute inset-0 bg-background/50 flex items-center justify-center z-10 rounded-lg">
            <Loader2 class="w-5 h-5 animate-spin text-primary" />
        </div>

        <div class="space-y-6">
            <!-- Title Override -->
            <h3 v-if="title" class="text-xl font-bold">{{ title }}</h3>
            
            <!-- Dynamic Widgets -->
            <template v-for="widget in widgets" :key="widget.id">
                <div :class="widgetClasses">
                    <h4 class="text-sm font-bold text-muted-foreground mb-4 flex items-center gap-2">
                        <component 
                            :is="widget.type === 'recent_posts' ? Calendar : widget.type === 'categories' ? Archive : Search" 
                            class="w-4 h-4" 
                        />
                        {{ widget.title }}
                    </h4>
                    
                    <!-- Content based on type -->
                    <div v-if="widget.type === 'text' || widget.type === 'html'" v-html="widget.content" class="text-sm prose prose-sm dark:prose-invert"></div>
                    
                    <ul v-else-if="widget.type === 'categories'" class="space-y-2">
                         <li v-for="(cat, cIdx) in (widget.data || [])" :key="cIdx">
                            <a :href="`/category/${cat.slug || '#'}`" class="text-sm hover:text-primary transition-colors flex justify-between">
                                {{ cat.name || cat }}
                                <span v-if="cat.contents_count" class="text-xs text-muted-foreground">({{ cat.contents_count }})</span>
                            </a>
                        </li>
                    </ul>

                    <ul v-else-if="widget.type === 'recent_posts'" class="space-y-3">
                         <li v-for="(post, pIdx) in (widget.data || [])" :key="pIdx">
                            <a :href="`/blog/${post.slug || '#'}`" class="block group">
                                <span class="text-sm font-medium group-hover:text-primary transition-colors line-clamp-1">{{ post.title }}</span>
                                <span class="text-xs text-muted-foreground">{{ post.published_at || post.date }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- Fallback Mock Widgets (if no real widgets assigned to this location) -->
            <template v-if="showFallback">
                <!-- Search Widget -->
                <div v-if="show_search" :class="widgetClasses">
                    <h4 class="text-sm font-bold text-muted-foreground mb-4 flex items-center gap-2">
                        <Search class="w-4 h-4" />
                        Search (Demo)
                    </h4>
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Search..."
                            class="w-full h-10 pl-4 pr-10 rounded-xl border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                        <Search class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    </div>
                </div>
                
                <!-- Categories Widget -->
                <div v-if="show_categories" :class="widgetClasses">
                    <h4 class="text-sm font-bold text-muted-foreground mb-4 flex items-center gap-2">
                        <Archive class="w-4 h-4" />
                        Categories (Demo)
                    </h4>
                    <ul class="space-y-2">
                        <li v-for="cat in ['Travel', 'Lifestyle', 'Fashion', 'Tech']" :key="cat">
                            <a href="#" class="text-sm hover:text-primary transition-colors">{{ cat }}</a>
                        </li>
                    </ul>
                </div>
            </template>
        </div>
    </aside>
</template>
