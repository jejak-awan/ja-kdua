<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.email_templates.list.title') }}</h1>
            <router-link
                :to="{ name: 'email-templates.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80 transition-colors"
            >
                <Plus class="w-5 h-5 mr-2" />
                {{ $t('features.email_templates.list.create') }}
            </router-link>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('features.email_templates.list.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                    >
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">Loading...</p>
            </div>

            <div v-else-if="filteredTemplates.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ $t('features.email_templates.list.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.email_templates.table.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.email_templates.table.subject') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.email_templates.table.type') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.email_templates.table.updated') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            {{ $t('features.email_templates.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="template in filteredTemplates" :key="template.id" class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ template.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-foreground">{{ template.subject || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                {{ template.type || 'custom' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ formatDate(template.updated_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="previewTemplate(template)"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors"
                                >
                                    {{ $t('common.actions.preview') }}
                                </button>
                                <button
                                    @click="sendTestEmail(template)"
                                    class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 transition-colors"
                                >
                                    Test
                                </button>
                                <router-link
                                    :to="{ name: 'email-templates.edit', params: { id: template.id } }"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors"
                                >
                                    {{ $t('common.actions.edit') }}
                                </router-link>
                                <button
                                    @click="handleDelete(template)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
                                >
                                    {{ $t('common.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';

const router = useRouter();
const { confirm } = useConfirm();

const templates = ref<any[]>([]);
const loading = ref(false);
const search = ref('');

const filteredTemplates = computed(() => {
    if (!search.value) return templates.value;
    
    const searchLower = search.value.toLowerCase();
    return templates.value.filter((template: any) => 
        template.name.toLowerCase().includes(searchLower) ||
        (template.subject && template.subject.toLowerCase().includes(searchLower))
    );
});

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/email-templates');
        const { data } = parseResponse(response);
        templates.value = ensureArray(data);
    } catch (error: any) {
        console.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const previewTemplate = async (template: any) => {
    try {
        const response = await api.post(`/admin/ja/email-templates/${template.id}/preview`);
        const previewWindow = window.open('', '_blank');
        if (previewWindow) {
            previewWindow.document.write(response.data.html || response.data);
        }
    } catch (error: any) {
        console.error('Failed to preview template:', error);
        toast.error('Error', 'Failed to preview template');
    }
};

const sendTestEmail = async (template: any) => {
    try {
        await api.post(`/admin/ja/email-templates/${template.id}/send-test`);
        toast.success('Test email sent successfully');
    } catch (error: any) {
        console.error('Failed to send test email:', error);
        toast.error('Error', error.response?.data?.message || 'Failed to send test email');
    }
};

const handleDelete = async (template: any) => {
    const confirmed = await confirm({
        title: 'Delete Template',
        message: `Are you sure you want to delete "${template.name}"?`,
        variant: 'danger',
        confirmText: 'Delete',
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/ja/email-templates/${template.id}`);
        toast.success('Template deleted successfully');
        fetchTemplates();
    } catch (error: any) {
        console.error('Failed to delete template:', error);
        toast.error('Error', 'Failed to delete template');
    }
};

const formatDate = (date: any) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

