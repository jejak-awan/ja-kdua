<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.content_templates.title') }}</h1>
            <router-link
                :to="{ name: 'content-templates.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('features.content_templates.create') }}
            </router-link>
        </div>

        <div class="bg-card shadow rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('features.content_templates.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.content_templates.loading') }}</p>
            </div>

            <div v-else-if="filteredTemplates.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.content_templates.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.content_templates.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.content_templates.table.type') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.content_templates.table.description') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.content_templates.table.updated') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.content_templates.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="template in filteredTemplates" :key="template.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ template.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500/20 text-blue-400">
                                {{ template.type || 'post' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-muted-foreground truncate max-w-xs">
                                {{ template.description || '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ formatDate(template.updated_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="createFromTemplate(template)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    {{ t('features.content_templates.actions.createContent') }}
                                </button>
                                <router-link
                                    :to="{ name: 'content-templates.edit', params: { id: template.id } }"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ t('features.content_templates.actions.edit') }}
                                </router-link>
                                <button
                                    @click="handleDelete(template)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ t('features.content_templates.actions.delete') }}
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
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();
const router = useRouter();
const templates = ref([]);
const loading = ref(false);
const search = ref('');

const filteredTemplates = computed(() => {
    if (!search.value) return templates.value;
    
    const searchLower = search.value.toLowerCase();
    return templates.value.filter(template => 
        template.name.toLowerCase().includes(searchLower) ||
        (template.description && template.description.toLowerCase().includes(searchLower))
    );
});

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/content-templates');
        const { data } = parseResponse(response);
        templates.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const createFromTemplate = async (template) => {
    try {
        const response = await api.post(`/admin/cms/content-templates/${template.id}/create-content`);
        const content = parseSingleResponse(response);
        if (content && content.id) {
            router.push({ name: 'contents.edit', params: { id: content.id } });
        }
    } catch (error) {
        console.error('Failed to create content from template:', error);
        alert(error.response?.data?.message || t('features.content_templates.messages.createError'));
    }
};

const handleDelete = async (template) => {
    if (!confirm(t('features.content_templates.messages.deleteConfirm', { name: template.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/content-templates/${template.id}`);
        await fetchTemplates();
    } catch (error) {
        console.error('Failed to delete template:', error);
        alert(t('features.content_templates.messages.deleteError'));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

