<template>
    <div class="max-w-5xl mx-auto pb-20">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.content.list.createNew') }}</h1>
                <AutoSaveIndicator
                    :status="autoSaveStatus"
                    :last-saved="lastSaved"
                />
            </div>
            <Button variant="ghost" asChild class="w-fit">
                <router-link :to="{ name: 'contents' }">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    {{ $t('features.content.form.back') }}
                </router-link>
            </Button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Main Content Section -->
            <ContentDetails
                v-model="form"
                v-model:selected-tags="selectedTags"
                :categories="categories"
                :tags="tags"
            />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Featured Image Section -->
                <FeaturedImage v-model="form.featured_image" />

                <!-- SEO Section -->
                <SeoSettings v-model="form" />
            </div>

            <!-- Actions -->
            <Card class="border-none shadow bg-card/80 backdrop-blur-sm sticky bottom-6 z-10">
                <CardContent class="p-4 flex justify-end items-center gap-4">
                    <Button variant="ghost" asChild>
                        <router-link :to="{ name: 'contents' }">
                            {{ $t('features.content.form.cancel') }}
                        </router-link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="loading"
                        class="min-w-[140px] shadow-sm"
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
                </CardContent>
            </Card>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import Button from '@/components/ui/button.vue';
import Card from '@/components/ui/card.vue';
import CardContent from '@/components/ui/card-content.vue';
import { 
    ArrowLeft, 
    Save, 
    Loader2 
} from 'lucide-vue-next';

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
