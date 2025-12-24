<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.content.list.title') }}</h1>
            <div class="flex items-center space-x-2">
                <router-link
                    :to="{ name: 'content-templates' }"
                    class="inline-flex items-center px-4 py-2 border border-input text-sm font-medium rounded-md text-foreground bg-card hover:bg-muted"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                    </svg>
                    {{ $t('features.content.list.templates') }}
                </router-link>
                <router-link
                    :to="{ name: 'contents.create' }"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $t('features.content.list.createNew') }}
                </router-link>
            </div>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('features.comments.filter.searchPlaceholder')"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <select
                            v-model="statusFilter"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">{{ $t('features.comments.filter.allStatus') }}</option>
                            <option value="published">{{ $t('features.content.status.published') }}</option>
                            <option value="draft">{{ $t('features.content.status.draft') }}</option>
                            <option value="archived">{{ $t('features.content.status.archived') }}</option>
                        </select>
                    </div>
                    <div v-if="selectedContents.length > 0" class="flex items-center space-x-2">
                        <span class="text-sm text-foreground">{{ $t('features.content.list.selected', { count: selectedContents.length }) }}</span>
                        <div class="relative">
                            <select
                                v-model="bulkAction"
                                @change="handleBulkAction"
                                class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">{{ $t('features.content.list.bulkActions') }}</option>
                                <option value="publish">{{ $t('features.content.actions.publishNow') }}</option>
                                <option value="draft">{{ $t('features.content.actions.saveDraft') }}</option>
                                <option value="archive">{{ $t('features.content.status.archived') }}</option>
                                <option value="delete">{{ $t('features.languages.actions.delete') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ $t('common.loading.default') }}</p>
            </div>

            <div v-else-if="contents.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ $t('features.content.list.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                @change="toggleSelectAll"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                            >
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.content.form.title') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.comments.detail.author') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('common.labels.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.content.form.featured') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.content.form.publishDate') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.languages.list.headers.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="content in filteredContents" :key="content.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                :value="content.id"
                                v-model="selectedContents"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                            >
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ content.title }}</div>
                            <div class="text-sm text-muted-foreground">{{ content.slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ content.author?.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="{
                                    'bg-green-500/20 text-green-400': content.status === 'published',
                                    'bg-yellow-500/20 text-yellow-400': content.status === 'draft',
                                    'bg-secondary text-secondary-foreground': content.status === 'archived',
                                }"
                            >
                                {{ content.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button
                                @click="toggleFeatured(content)"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                :class="content.is_featured ? 'bg-indigo-600' : 'bg-gray-200'"
                            >
                                <span class="sr-only">Use setting</span>
                                <span
                                    aria-hidden="true"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-card border border-border transform ring-0 transition ease-in-out duration-200"
                                    :class="content.is_featured ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
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
                                    class="text-muted-foreground hover:text-foreground"
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
                                    {{ $t('common.actions.edit') }}
                                </router-link>
                                <button
                                    @click="handleDelete(content)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ $t('features.languages.actions.delete') }}
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
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();
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

const toggleFeatured = async (content) => {
    const originalState = content.is_featured;
    // Optimistic update
    content.is_featured = !originalState;
    
    try {
        await api.post(`/admin/cms/contents/${content.id}/toggle-featured`);
    } catch (error) {
        console.error('Failed to toggle featured status:', error);
        // Revert on failure
        content.is_featured = originalState;
        alert('Failed to update featured status');
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
        // Handle data wrapped in "data" key from BaseApiController
        const data = response.data?.data || response.data;
        const previewUrl = data?.url || data?.preview_url;
        
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

