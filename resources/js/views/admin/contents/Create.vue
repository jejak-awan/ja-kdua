<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Create New Content</h1>
            <router-link
                :to="{ name: 'contents' }"
                class="text-gray-600 hover:text-gray-900"
            >
                ← Back to Contents
            </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Content Details</h2>
                
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter content title"
                        />
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="content-slug"
                        />
                        <p class="mt-1 text-xs text-gray-500">URL-friendly version of the title</p>
                    </div>

                    <!-- Type & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Category
                        </label>
                        <select
                            v-model="form.category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">No Category</option>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tags
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Select a tag...</option>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Brief description of the content"
                        ></textarea>
                    </div>

                    <!-- Body / Rich Text Editor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <RichTextEditor
                            v-model="form.body"
                            class="min-h-[400px]"
                        />
                    </div>

                    <!-- Published At -->
                    <div v-if="form.status === 'published'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Published At
                        </label>
                        <input
                            v-model="form.published_at"
                            type="datetime-local"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">Leave empty to publish immediately</p>
                    </div>
                </div>
            </div>

            <!-- Featured Image Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Image</h2>
                <div class="space-y-4">
                    <div v-if="form.featured_image" class="relative">
                        <img
                            :src="form.featured_image"
                            alt="Featured Image"
                            class="w-full h-64 object-cover rounded-lg"
                        />
                        <button
                            type="button"
                            @click="form.featured_image = null"
                            class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                        >
                            Remove
                        </button>
                    </div>
                    <MediaPicker
                        v-else
                        @selected="(media) => form.featured_image = media.url"
                        label="Select Featured Image"
                    />
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Title
                        </label>
                        <input
                            v-model="form.meta_title"
                            type="text"
                            maxlength="255"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="SEO title (defaults to content title)"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ form.meta_title?.length || 0 }}/255 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Description
                        </label>
                        <textarea
                            v-model="form.meta_description"
                            rows="3"
                            maxlength="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="SEO description (defaults to excerpt)"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ form.meta_description?.length || 0 }}/500 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Meta Keywords
                        </label>
                        <input
                            v-model="form.meta_keywords"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="keyword1, keyword2, keyword3"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Open Graph Image
                        </label>
                        <div v-if="form.og_image" class="relative mb-2">
                            <img
                                :src="form.og_image"
                                alt="OG Image"
                                class="w-full h-48 object-cover rounded-lg"
                            />
                            <button
                                type="button"
                                @click="form.og_image = null"
                                class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600"
                            >
                                Remove
                            </button>
                        </div>
                        <MediaPicker
                            v-else
                            @selected="(media) => form.og_image = media.url"
                            label="Select OG Image"
                        />
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <router-link
                    :to="{ name: 'contents' }"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </router-link>
                <button
                    type="submit"
                    :disabled="loading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ loading ? 'Creating...' : 'Create Content' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import RichTextEditor from '../../../components/RichTextEditor.vue';
import MediaPicker from '../../../components/MediaPicker.vue';

const router = useRouter();

const loading = ref(false);
const categories = ref([]);
const tags = ref([]);
const selectedTags = ref([]);

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

        const response = await api.post('/admin/cms/contents', payload);
        
        router.push({ name: 'contents' });
    } catch (error) {
        console.error('Failed to create content:', error);
        alert(error.response?.data?.message || 'Failed to create content');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchTags();
});
</script>
