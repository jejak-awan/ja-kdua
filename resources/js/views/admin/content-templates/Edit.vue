<template>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Edit Content Template</h1>
            <router-link
                :to="{ name: 'content-templates' }"
                class="text-gray-600 hover:text-gray-900"
            >
                ← Back to Templates
            </router-link>
        </div>

        <div v-if="loading && !form.name" class="text-center py-8">
            <p class="text-gray-500">Loading template...</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Template name"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Template description"
                        ></textarea>
                    </div>

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
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Content</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Title
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Content title template"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Body
                        </label>
                        <RichTextEditor
                            v-model="form.body"
                            placeholder="Enter content body..."
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Content excerpt"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <router-link
                    :to="{ name: 'content-templates' }"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ saving ? 'Updating...' : 'Update Template' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';
import RichTextEditor from '../../../components/RichTextEditor.vue';

const route = useRoute();
const router = useRouter();
const templateId = route.params.id;

const loading = ref(false);
const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    type: 'post',
    title: '',
    body: '',
    excerpt: '',
});

const fetchTemplate = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/content-templates/${templateId}`);
        const template = parseSingleResponse(response) || {};
        
        form.value = {
            name: template.name || '',
            description: template.description || '',
            type: template.type || 'post',
            title: template.title || '',
            body: template.body || '',
            excerpt: template.excerpt || '',
        };
    } catch (error) {
        console.error('Failed to fetch template:', error);
        alert('Failed to load template');
        router.push({ name: 'content-templates' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/content-templates/${templateId}`, form.value);
        router.push({ name: 'content-templates' });
    } catch (error) {
        console.error('Failed to update template:', error);
        alert(error.response?.data?.message || 'Failed to update template');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchTemplate();
});
</script>

