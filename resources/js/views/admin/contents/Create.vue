<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.content.list.createNew') }}</h1>
                <AutoSaveIndicator
                    :status="autoSaveStatus"
                    :last-saved="lastSaved"
                    class="mt-2"
                />
            </div>
            <router-link
                :to="{ name: 'contents' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← {{ $t('features.content.form.back') }}
            </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content Section -->
            <ContentDetails
                v-model="form"
                v-model:selected-tags="selectedTags"
                :categories="categories"
                :tags="tags"
            />

            <!-- Featured Image Section -->
            <FeaturedImage v-model="form.featured_image" />

            <!-- SEO Section -->
            <SeoSettings v-model="form" />

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <router-link
                    :to="{ name: 'contents' }"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                >
                    {{ $t('features.content.form.cancel') }}
                </router-link>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                >
                    {{ loading ? $t('features.content.form.creating') : $t('features.content.form.create') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();
const router = useRouter();
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import MediaPicker from '../../../components/MediaPicker.vue';
import AutoSaveIndicator from '../../../components/AutoSaveIndicator.vue';
import SeoSettings from '../../../components/content/SeoSettings.vue';
import FeaturedImage from '../../../components/content/FeaturedImage.vue';
import ContentDetails from '../../../components/content/ContentDetails.vue';
import { useAutoSave } from '../../../composables/useAutoSave';



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
        const payload = {
            ...form.value,
            tags: selectedTags.value.map(t => t.id),
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
