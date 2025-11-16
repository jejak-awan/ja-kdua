<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Contents</h1>
            <div class="flex items-center space-x-2">
                <router-link
                    :to="{ name: 'content-templates' }"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                    </svg>
                    Templates
                </router-link>
                <router-link
                    :to="{ name: 'contents.create' }"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Content
                </router-link>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search contents..."
                            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <select
                            v-model="statusFilter"
                            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div v-if="selectedContents.length > 0" class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">{{ selectedContents.length }} selected</span>
                        <div class="relative">
                            <select
                                v-model="bulkAction"
                                @change="handleBulkAction"
                                class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="draft">Draft</option>
                                <option value="archive">Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="contents.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No contents found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                @change="toggleSelectAll"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Author
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="content in filteredContents" :key="content.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                :value="content.id"
                                v-model="selectedContents"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ content.title }}</div>
                            <div class="text-sm text-gray-500">{{ content.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ content.author?.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800': content.status === 'published',
                                    'bg-yellow-100 text-yellow-800': content.status === 'draft',
                                    'bg-gray-100 text-gray-800': content.status === 'archived',
                                }"
                            >
                                {{ content.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(content.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end items-center space-x-2">
                                <button
                                    @click="handlePreview(content)"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Preview"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button
                                    @click="handleDuplicate(content)"
                                    class="text-gray-600 hover:text-gray-900"
                                    title="Duplicate"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <router-link
                                    :to="{ name: 'contents.revisions', params: { id: content.id } }"
                                    class="text-purple-600 hover:text-purple-900"
                                    title="Revisions"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </router-link>
                                <router-link
                                    :to="{ name: 'contents.edit', params: { id: content.id } }"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </router-link>
                                <button
                                    @click="handleDelete(content)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';

const router = useRouter();
const contents = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref('');
const selectedContents = ref([]);
const bulkAction = ref('');
const pagination = ref(null);

const allSelected = computed(() => {
    return contents.value.length > 0 && selectedContents.value.length === contents.value.length;
});

const filteredContents = computed(() => {
    if (!Array.isArray(contents.value)) {
        return [];
    }
    
    let filtered = contents.value;
    
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(content => 
            content?.title?.toLowerCase().includes(searchLower) ||
            content?.slug?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchContents = async () => {
    loading.value = true;
    try {
        const params = {};
        if (statusFilter.value) {
            params.status = statusFilter.value;
        }
        const response = await api.get('/admin/cms/contents', { params });
        // Handle paginated response from BaseApiController
        let data = [];
        if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
            // Paginated response: { success: true, message: "...", data: { data: [...], pagination: {...} } }
            data = response.data.data.data;
            if (response.data.data.pagination) {
                pagination.value = response.data.data.pagination;
            }
        } else if (response.data?.data && Array.isArray(response.data.data)) {
            // Simple array response: { success: true, data: [...] }
            data = response.data.data;
        } else if (Array.isArray(response.data)) {
            // Direct array response
            data = response.data;
        } else if (response.data?.items && Array.isArray(response.data.items)) {
            // Alternative paginated format
            data = response.data.items;
        }
        contents.value = data;
        selectedContents.value = [];
    } catch (error) {
        console.error('Failed to fetch contents:', error);
    } finally {
        loading.value = false;
    }
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedContents.value = [];
    } else {
        selectedContents.value = contents.value.map(c => c.id);
    }
};

const handleDuplicate = async (content) => {
    if (!confirm(`Are you sure you want to duplicate "${content.title}"?`)) {
        return;
    }

    try {
        const response = await api.post(`/admin/cms/contents/${content.id}/duplicate`);
        const duplicatedContent = response.data.data || response.data;
        router.push({ name: 'contents.edit', params: { id: duplicatedContent.id } });
    } catch (error) {
        console.error('Failed to duplicate content:', error);
        alert(error.response?.data?.message || 'Failed to duplicate content');
    }
};

const handlePreview = async (content) => {
    try {
        const response = await api.get(`/admin/cms/contents/${content.id}/preview`);
        const previewUrl = response.data.url || response.data.preview_url;
        if (previewUrl) {
            window.open(previewUrl, '_blank');
        } else {
            // Fallback: open content URL if preview URL not available
            window.open(`/${content.slug}`, '_blank');
        }
    } catch (error) {
        console.error('Failed to get preview URL:', error);
        // Fallback: open content URL
        window.open(`/${content.slug}`, '_blank');
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedContents.value.length === 0) {
        bulkAction.value = '';
        return;
    }

    const action = bulkAction.value;
    const count = selectedContents.value.length;
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${count} content(s)?`)) {
            bulkAction.value = '';
            return;
        }
    } else {
        if (!confirm(`Are you sure you want to ${action} ${count} content(s)?`)) {
            bulkAction.value = '';
            return;
        }
    }

    try {
        await api.post('/admin/cms/contents/bulk-action', {
            action: action,
            ids: selectedContents.value,
        });
        await fetchContents();
        bulkAction.value = '';
    } catch (error) {
        console.error('Failed to perform bulk action:', error);
        alert(error.response?.data?.message || 'Failed to perform bulk action');
        bulkAction.value = '';
    }
};

const handleDelete = async (content) => {
    if (!confirm(`Are you sure you want to delete "${content.title}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/contents/${content.id}`);
        await fetchContents();
    } catch (error) {
        console.error('Failed to delete content:', error);
        alert('Failed to delete content');
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

watch([search, statusFilter], () => {
    fetchContents();
});

onMounted(() => {
    fetchContents();
});
</script>

