<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.content.form.editTitle') }}</h1>
                <div v-if="lockStatus" class="mt-2 flex items-center space-x-2">
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="lockStatus.is_locked ? 'bg-yellow-500/20 text-yellow-400' : 'bg-green-500/20 text-green-400'"
                    >
                        {{ lockStatus.is_locked ? $t('features.content.form.locked') : $t('features.content.form.unlocked') }}
                    </span>
                    <span v-if="lockStatus.is_locked && lockStatus.locked_by" class="text-xs text-muted-foreground">
                        {{ $t('features.content.form.lockedBy', { name: lockStatus.locked_by.name }) }}
                    </span>
                    <button
                        v-if="lockStatus.is_locked && lockStatus.can_unlock"
                        @click="handleUnlock"
                        class="text-xs text-indigo-600 hover:text-indigo-900"
                    >
                        {{ $t('features.content.form.unlock') }}
                    </button>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <AutoSaveIndicator
                    :status="autoSaveStatus"
                    :last-saved="lastSaved"
                />
                <button
                    @click="handlePreview"
                    class="inline-flex items-center px-4 py-2 border border-input text-sm font-medium rounded-md text-foreground bg-card hover:bg-muted"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ $t('features.content.form.preview') }}
                </button>
                <router-link
                    :to="{ name: 'contents' }"
                    class="text-muted-foreground hover:text-foreground"
                >
                    ← {{ $t('features.content.form.back') }}
                </router-link>
            </div>
        </div>

        <div v-if="loading && !form.title" class="text-center py-8">
            <p class="text-muted-foreground">{{ $t('features.content.form.loading') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content Section -->
            <div class="bg-card shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.details') }}</h2>
                
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.titlePlaceholder')"
                        >
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.slugPlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.content.form.urlFriendlyHelp') }}</p>
                    </div>

                    <!-- Type & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.content.form.type') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.content.form.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                required
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="draft">{{ $t('features.content.status.draft') }}</option>
                                <option value="published">{{ $t('features.content.status.published') }}</option>
                                <option value="archived">{{ $t('features.content.status.archived') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.content.form.category') }}
                        </label>
                        <select
                            v-model="form.category_id"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ $t('features.content.form.selectCategory') }}</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.content.form.tags') }}
                        </label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <span
                                v-for="tag in selectedTags"
                                :key="tag.id"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800"
                            >
                                {{ tag.name }}
                                <button
                                    type="button"
                                    @click="removeTag(tag.id)"
                                    class="ml-2 text-indigo-600 hover:text-indigo-800"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                        <select
                            @change="addTag"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">{{ $t('features.content.form.selectTag') }}</option>
                            <option
                                v-for="tag in availableTags"
                                :key="tag.id"
                                :value="tag.id"
                            >
                                {{ tag.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.excerptPlaceholder')"
                        />
                    </div>

                    <!-- Body / Rich Text Editor -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.content.form.body') }} <span class="text-red-500">*</span>
                        </label>
                        <RichTextEditor
                            v-model="form.body"
                            class="min-h-[400px]"
                        />
                    </div>

                    <!-- Published At -->
                    <div v-if="form.status === 'published'">
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Published At
                        </label>
                        <input
                            v-model="form.published_at"
                            type="datetime-local"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ $t('features.content.form.publishImmediatelyHelp') }}</p>
                    </div>
                </div>
            </div>

            <!-- Featured Image Section -->
            <div class="bg-card shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.featuredImage') }}</h2>
                <div class="space-y-4">
                    <div v-if="form.featured_image" class="relative">
                        <img
                            :src="form.featured_image"
                            alt="Featured Image"
                            class="w-full h-64 object-cover rounded-lg"
                        >
                        <button
                            type="button"
                            @click="form.featured_image = null"
                            class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                        >
                            {{ $t('features.content.form.remove') }}
                        </button>
                    </div>
                    <MediaPicker
                        v-else
                        @selected="(media) => form.featured_image = media.url"
                        :label="$t('features.content.form.selectImage')"
                    />
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-card shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.content.form.seoSettings') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.content.form.metaTitle') }}
                        </label>
                        <input
                            v-model="form.meta_title"
                            type="text"
                            maxlength="255"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.seoTitlePlaceholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ form.meta_title?.length || 0 }}/255 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.content.form.metaDescription') }}
                        </label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            maxlength="500"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.seoDescPlaceholder')"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">{{ form.meta_description?.length || 0 }}/500 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Meta Keywords
                        </label>
                        <input
                            v-model="form.meta_keywords"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.content.form.keywordsPlaceholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            Open Graph Image
                        </label>
                        <div v-if="form.og_image" class="relative mb-2">
                            <img
                                :src="form.og_image"
                                alt="OG Image"
                                class="w-full h-48 object-cover rounded-lg"
                            >
                            <button
                                type="button"
                                @click="form.og_image = null"
                                class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                            >
                                {{ $t('features.content.form.remove') }}
                            </button>
                        </div>
                        <MediaPicker
                            v-else
                            @selected="(media) => form.og_image = media.url"
                            :label="$t('features.content.form.selectOgImage')"
                        />
                    </div>
                </div>
            </div>

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
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? $t('features.content.form.updating') : $t('features.content.form.update') }}
                </button>
            </div>
        </form>

        <!-- Preview Modal -->
        <ContentPreviewModal
            :show="showPreviewModal"
            :content="previewContent"
            :can-publish="form.status !== 'published'"
            @close="showPreviewModal = false"
            @publish="handlePublishFromPreview"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import RichTextEditor from '../../../components/RichTextEditor.vue';
import MediaPicker from '../../../components/MediaPicker.vue';
import AutoSaveIndicator from '../../../components/AutoSaveIndicator.vue';
import ContentPreviewModal from '../../../components/admin/ContentPreviewModal.vue';
import { useAutoSave } from '../../../composables/useAutoSave';

const route = useRoute();
const router = useRouter();
const contentId = route.params.id;

const loading = ref(false);
const categories = ref([]);
const tags = ref([]);
const selectedTags = ref([]);
const lockStatus = ref(null);
const lockInterval = ref(null);

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
const autoSaveEnabled = ref(false);
const {
    isSaving: autoSaving,
    lastSaved,
    saveStatus: autoSaveStatus,
    hasChanges,
    startAutoSave,
} = useAutoSave(formWithTags, contentId, {
    interval: 30000, // 30 seconds
    get enabled() {
        return autoSaveEnabled.value;
    },
});

const availableTags = computed(() => {
    if (!Array.isArray(tags.value) || !Array.isArray(selectedTags.value)) {
        return [];
    }
    return tags.value.filter(tag => !selectedTags.value.find(st => st.id === tag.id));
});

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === slugify(form.value.title)) {
        form.value.slug = slugify(form.value.title);
    }
};

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const addTag = (event) => {
    const tagId = parseInt(event.target.value);
    if (!tagId) return;
    
    const tag = tags.value.find(t => t.id === tagId);
    if (tag && !selectedTags.value.find(st => st.id === tag.id)) {
        selectedTags.value.push(tag);
    }
    event.target.value = '';
};

const removeTag = (tagId) => {
    if (Array.isArray(selectedTags.value)) {
        selectedTags.value = selectedTags.value.filter(t => t.id !== tagId);
    }
};

const fetchContent = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}`);
        const content = parseSingleResponse(response) || {};
        
        form.value = {
            title: content.title || '',
            slug: content.slug || '',
            excerpt: content.excerpt || '',
            body: content.body || '',
            featured_image: content.featured_image || null,
            status: content.status || 'draft',
            type: content.type || 'post',
            category_id: content.category_id || null,
            published_at: content.published_at ? formatDateTimeLocal(content.published_at) : null,
            meta_title: content.meta_title || '',
            meta_description: content.meta_description || '',
            meta_keywords: content.meta_keywords || '',
            og_image: content.og_image || null,
        };

        // Set selected tags
        if (content.tags && Array.isArray(content.tags)) {
            selectedTags.value = content.tags;
        }
        
        // Enable auto-save after content is loaded
        autoSaveEnabled.value = true;
        startAutoSave();
        
        // Lock content on edit
        await lockContent();
    } catch (error) {
        console.error('Failed to fetch content:', error);
        alert(t('features.content.messages.loadFailed'));
        router.push({ name: 'contents' });
    } finally {
        loading.value = false;
    }
};

const lockContent = async () => {
    try {
        const response = await api.post(`/admin/cms/contents/${contentId}/lock`);
        lockStatus.value = parseSingleResponse(response) || {};
        
        // Refresh lock status every 30 seconds
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
        lockInterval.value = setInterval(checkLockStatus, 30000);
    } catch (error) {
        console.error('Failed to lock content:', error);
    }
};

const checkLockStatus = async () => {
    try {
        const response = await api.get(`/admin/cms/contents/${contentId}`);
        const content = parseSingleResponse(response) || {};
        if (content.lock_status) {
            lockStatus.value = content.lock_status;
        }
    } catch (error) {
        console.error('Failed to check lock status:', error);
    }
};

const handleUnlock = async () => {
    try {
        await api.post(`/admin/cms/contents/${contentId}/unlock`);
        lockStatus.value = { is_locked: false };
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
    } catch (error) {
        console.error('Failed to unlock content:', error);
        alert(error.response?.data?.message || t('features.content.messages.unlockFailed'));
    }
};

const showPreviewModal = ref(false);

const handlePreview = () => {
    showPreviewModal.value = true;
};

const previewContent = computed(() => {
    const category = categories.value.find(c => c.id === form.value.category_id);
    return {
        title: form.value.title,
        body: form.value.body,
        excerpt: form.value.excerpt,
        featured_image: form.value.featured_image,
        author: { name: 'Current User' }, // You can get from auth store
        category: category ? { name: category.name } : null,
        published_at: form.value.published_at || new Date().toISOString(),
    };
});

const handlePublishFromPreview = async () => {
    form.value.status = 'published';
    await handleSubmit();
};

const formatDateTimeLocal = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories');
        const data = response.data?.data || response.data || [];
        categories.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

const fetchTags = async () => {
    try {
        const response = await api.get('/cms/tags');
        tags.value = response.data.data || response.data || [];
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

        await api.put(`/admin/cms/contents/${contentId}`, payload);
        
        // Unlock content after save
        if (lockInterval.value) {
            clearInterval(lockInterval.value);
        }
        await handleUnlock();
        
        router.push({ name: 'contents' });
    } catch (error) {
        console.error('Failed to update content:', error);
        alert(error.response?.data?.message || t('features.content.messages.updateFailed'));
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchContent();
    fetchCategories();
    fetchTags();
});

onUnmounted(() => {
    // Clean up lock interval
    if (lockInterval.value) {
        clearInterval(lockInterval.value);
    }
    // Unlock content when leaving page
    if (lockStatus.value?.is_locked) {
        handleUnlock().catch(() => {});
    }
});
</script>
