<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">Email Templates</h1>
            <router-link
                :to="{ name: 'email-templates.create' }"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Template
            </router-link>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search templates..."
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">Loading...</p>
            </div>

            <div v-else-if="filteredTemplates.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">No templates found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            Subject
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                            Updated
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="template in filteredTemplates" :key="template.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ template.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-foreground">{{ template.subject || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-500/20 text-blue-400">
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
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Preview
                                </button>
                                <button
                                    @click="sendTestEmail(template)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    Test
                                </button>
                                <router-link
                                    :to="{ name: 'email-templates.edit', params: { id: template.id } }"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </router-link>
                                <button
                                    @click="handleDelete(template)"
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
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import toast from '../../../services/toast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const router = useRouter();
const { confirm } = useConfirm();

const templates = ref([]);
const loading = ref(false);
const search = ref('');

const filteredTemplates = computed(() => {
    if (!search.value) return templates.value;
    
    const searchLower = search.value.toLowerCase();
    return templates.value.filter(template => 
        template.name.toLowerCase().includes(searchLower) ||
        (template.subject && template.subject.toLowerCase().includes(searchLower))
    );
});

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/email-templates');
        const { data } = parseResponse(response);
        templates.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const previewTemplate = async (template) => {
    try {
        const response = await api.post(`/admin/cms/email-templates/${template.id}/preview`);
        const previewWindow = window.open('', '_blank');
        if (previewWindow) {
            previewWindow.document.write(response.data.html || response.data);
        }
    } catch (error) {
        console.error('Failed to preview template:', error);
        toast.error('Error', 'Failed to preview template');
    }
};

const sendTestEmail = async (template) => {
    try {
        await api.post(`/admin/cms/email-templates/${template.id}/send-test`);
        toast.success('Test email sent successfully');
    } catch (error) {
        console.error('Failed to send test email:', error);
        toast.error('Error', error.response?.data?.message || 'Failed to send test email');
    }
};

const deleteTemplate = async (template) => {
    const confirmed = await confirm({
        title: 'Delete Template',
        message: `Are you sure you want to delete "${template.name}"?`,
        variant: 'danger',
        confirmText: 'Delete',
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/email-templates/${template.id}`);
        toast.success('Template deleted successfully');
        fetchTemplates();
    } catch (error) {
        console.error('Failed to delete template:', error);
        toast.error('Error', 'Failed to delete template');
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

