<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Webhooks</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Webhook
            </button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Webhooks</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.active || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Calls</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_calls || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Failed Calls</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.failed_calls || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search webhooks..."
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="filteredWebhooks.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No webhooks found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            URL
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Events
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Calls
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="webhook in filteredWebhooks" :key="webhook.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ webhook.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-mono truncate max-w-xs">{{ webhook.url }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="event in (webhook.events || [])"
                                    :key="event"
                                    class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded"
                                >
                                    {{ event }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ webhook.total_calls || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="webhook.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                {{ webhook.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="testWebhook(webhook)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Test
                                </button>
                                <button
                                    @click="editWebhook(webhook)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteWebhook(webhook)"
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

        <!-- Create/Edit Modal -->
        <WebhookModal
            v-if="showCreateModal || showEditModal"
            @close="closeModal"
            @saved="handleWebhookSaved"
            :webhook="editingWebhook"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import WebhookModal from '../../../components/webhooks/WebhookModal.vue';

const webhooks = ref([]);
const statistics = ref(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWebhook = ref(null);

const filteredWebhooks = computed(() => {
    if (!search.value) return webhooks.value;
    
    const searchLower = search.value.toLowerCase();
    return webhooks.value.filter(webhook => 
        webhook.name.toLowerCase().includes(searchLower) ||
        webhook.url.toLowerCase().includes(searchLower)
    );
});

const fetchWebhooks = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/webhooks');
        webhooks.value = response.data.data || response.data || [];
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/webhooks/statistics');
            statistics.value = statsResponse.data;
        } catch (error) {
            // Calculate from webhooks if endpoint doesn't exist
            statistics.value = {
                total: webhooks.value.length,
                active: webhooks.value.filter(w => w.is_active).length,
                total_calls: webhooks.value.reduce((sum, w) => sum + (w.total_calls || 0), 0),
                failed_calls: webhooks.value.reduce((sum, w) => sum + (w.failed_calls || 0), 0),
            };
        }
    } catch (error) {
        console.error('Failed to fetch webhooks:', error);
    } finally {
        loading.value = false;
    }
};

const editWebhook = (webhook) => {
    editingWebhook.value = webhook;
    showEditModal.value = true;
};

const testWebhook = async (webhook) => {
    try {
        await api.post(`/admin/cms/webhooks/${webhook.id}/test`);
        alert('Webhook test sent successfully');
    } catch (error) {
        console.error('Failed to test webhook:', error);
        alert(error.response?.data?.message || 'Failed to test webhook');
    }
};

const deleteWebhook = async (webhook) => {
    if (!confirm(`Are you sure you want to delete webhook "${webhook.name}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/webhooks/${webhook.id}`);
        await fetchWebhooks();
    } catch (error) {
        console.error('Failed to delete webhook:', error);
        alert('Failed to delete webhook');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingWebhook.value = null;
};

const handleWebhookSaved = () => {
    fetchWebhooks();
    closeModal();
};

onMounted(() => {
    fetchWebhooks();
});
</script>

