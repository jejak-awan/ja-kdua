<template>
    <div class="max-w-[1600px] mx-auto pb-20 px-4 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" asChild>
                    <router-link :to="{ name: 'contents' }">
                        <ArrowLeft class="w-4 h-4" />
                    </router-link>
                </Button>
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.content.list.createNew') }}</h1>
                    <AutoSaveIndicator
                        :status="autoSaveStatus"
                        :last-saved="lastSaved"
                    />
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                 <Button 
                    variant="ghost" 
                    size="icon" 
                    @click="isSidebarOpen = !isSidebarOpen"
                    :title="isSidebarOpen ? 'Close Settings' : 'Open Settings'"
                >
                    <PanelRightClose v-if="isSidebarOpen" class="w-4 h-4" />
                    <PanelRightOpen v-else class="w-4 h-4" />
                </Button>
                 <Button variant="ghost" asChild>
                    <router-link :to="{ name: 'contents' }">
                        {{ $t('features.content.form.cancel') }}
                    </router-link>
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="loading"
                    class="min-w-[120px] shadow-sm"
                >
                    <template v-if="loading">
                        <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('features.content.form.creating') }}
                    </template>
                    <template v-else>
                        <Save class="w-4 h-4 mr-2" />
                        {{ $t('features.content.form.create') }}
                    </template>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Main Content Area (Center) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-8' : 'lg:col-span-12', 'space-y-8 transition-all duration-300 ease-in-out']">
                <ContentMain
                    v-model="form"
                />
            </div>

            <!-- Sidebar (Right) -->
            <div :class="[isSidebarOpen ? 'lg:col-span-4' : 'hidden', 'space-y-6 transition-all duration-300 ease-in-out']">
                <ContentSidebar
                    v-model="form"
                    v-model:selected-tags="selectedTags"
                    :categories="categories"
                    :tags="tags"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Button from '@/components/ui/button.vue';
import {
    ArrowLeft, 
    Save, 
    Loader2, 
    PanelRightClose, 
    PanelRightOpen 
} from 'lucide-vue-next';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import AutoSaveIndicator from '../../../components/AutoSaveIndicator.vue';
import ContentMain from '../../../components/content/ContentMain.vue';
import ContentSidebar from '../../../components/content/ContentSidebar.vue';
import { useAutoSave } from '../../../composables/useAutoSave';

const { t } = useI18n();
const router = useRouter();

const isSidebarOpen = ref(true);

const loading = ref(false);
const categories = ref([]);
const tags = ref([]);
const selectedTags = ref([]);
const contentId = ref(null);

const form = ref({
    title: '',
    slug: '',
    excerpt: '',
    body: '',
    featured_image: null,
    status: 'draft',
    type: 'post',
    category_id: null,
    published_at: null,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    og_image: null,
});

// Auto-generation logic
watch(() => form.value.title, (newTitle) => {
    // Slug
    if (!form.value.slug || form.value.slug === slugify(localStorage.getItem('last_title') || '')) {
         form.value.slug = slugify(newTitle);
    }
    // Meta Title
    if (!form.value.meta_title || form.value.meta_title === localStorage.getItem('last_title')) {
        form.value.meta_title = newTitle;
    }
    localStorage.setItem('last_title', newTitle);
});

watch(() => form.value.excerpt, (newExcerpt) => {
    // Meta Description
    if (!form.value.meta_description || form.value.meta_description === localStorage.getItem('last_excerpt')) {
        form.value.meta_description = newExcerpt;
    }
    localStorage.setItem('last_excerpt', newExcerpt);
});

// Helper for slugify
const slugify = (text) => {
    if (!text) return '';
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

// Create a computed form that includes tags for auto-save
const formWithTags = computed(() => ({
    ...form.value,
    tags: selectedTags.value.map(t => t.id),
}));

// Auto-save setup
const {
    isSaving: autoSaving,
    lastSaved,
    saveStatus: autoSaveStatus,
    hasChanges,
} = useAutoSave(formWithTags, contentId, {
    interval: 30000, // 30 seconds
    enabled: true,
    onSave: (response) => {
        // Update contentId if new content was created
        if (response?.data?.id && !contentId.value) {
            contentId.value = response.data.id;
        }
    },
});

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

const fetchTags = async () => {
    try {
        const response = await api.get('/admin/cms/tags');
        const { data } = parseResponse(response);
        tags.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
};

const handleSubmit = async () => {
    loading.value = true;
    try {
        // Auto-fill SEO fields if empty
        if (!form.value.meta_title && form.value.title) {
            form.value.meta_title = form.value.title;
        }
        if (!form.value.meta_description && form.value.excerpt) {
            form.value.meta_description = form.value.excerpt;
        }
        if (!form.value.meta_keywords && selectedTags.value.length > 0) {
            form.value.meta_keywords = selectedTags.value.map(t => t.name).join(', ');
        }
        
        // Prepare tags: send ids for existing, names for new
        const tagIds = selectedTags.value.filter(t => t.id).map(t => t.id);
        const newTags = selectedTags.value.filter(t => !t.id).map(t => t.name);
        
        const payload = {
            ...form.value,
            tags: tagIds,
            new_tags: newTags,
        };

        // If content was auto-saved, use update endpoint
        const endpoint = contentId.value
            ? `/admin/cms/contents/${contentId.value}`
            : '/admin/cms/contents';
        const method = contentId.value ? 'put' : 'post';

        const response = await method === 'put'
            ? await api.put(endpoint, payload)
            : await api.post(endpoint, payload);
        
        router.push({ name: 'contents' });
    } catch (error) {
        console.error('Failed to create content:', error);
        alert(error.response?.data?.message || t('features.content.messages.createFailed'));
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchTags();
});
</script>
