<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.tags.title') }}</h1>
            <router-link
                :to="{ name: 'tags.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.tags.createNew') }}
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-card border border-border rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="$t('features.tags.search')"
                    class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.total') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.total_tags || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.used') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.used_tags || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ $t('features.tags.stats.usage') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.total_usage || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tags List -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.tags.loading') }}</p>
        </div>

        <div v-else-if="filteredTags.length === 0" class="bg-card border border-border rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ $t('features.tags.empty') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.tags.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.tags.table.slug') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.tags.table.description') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.tags.table.usage') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.tags.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="tag in filteredTags" :key="tag.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ tag.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ tag.slug }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-muted-foreground truncate max-w-xs">
                                {{ tag.description || '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ tag.contents_count || 0 }} contents</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="editTag(tag)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ $t('features.tags.actions.edit') }}
                                </button>
                                <button
                                    @click="deleteTag(tag)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ $t('features.tags.actions.delete') }}
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
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const loading = ref(false);
const tags = ref([]);
const statistics = ref(null);
const search = ref('');

const filteredTags = computed(() => {
    if (!search.value) return tags.value;
    
    const searchLower = search.value.toLowerCase();
    return tags.value.filter(tag => 
        tag.name.toLowerCase().includes(searchLower) ||
        tag.slug.toLowerCase().includes(searchLower) ||
        (tag.description && tag.description.toLowerCase().includes(searchLower))
    );
});

const fetchTags = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/tags');
        const { data } = parseResponse(response);
        tags.value = ensureArray(data);
        
        // Fetch statistics if available
        try {
            const statsResponse = await api.get('/admin/cms/tags/statistics');
            statistics.value = statsResponse.data;
        } catch (error) {
            // Statistics endpoint might not exist, calculate from tags
            statistics.value = {
                total_tags: tags.value.length,
                used_tags: tags.value.filter(t => (t.contents_count || 0) > 0).length,
                total_usage: tags.value.reduce((sum, t) => sum + (t.contents_count || 0), 0),
            };
        }
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    } finally {
        loading.value = false;
    }
};

const editTag = (tag) => {
    router.push({ name: 'tags.edit', params: { id: tag.id } });
};

const deleteTag = async (tag) => {
    if (!confirm(t('features.tags.messages.deleteConfirm', { name: tag.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/tags/${tag.id}`);
        await fetchTags();
    } catch (error) {
        console.error('Failed to delete tag:', error);
        const message = error.response?.data?.message || t('features.tags.messages.deleteError');
        alert(message);
    }
};

onMounted(() => {
    fetchTags();
});
</script>
