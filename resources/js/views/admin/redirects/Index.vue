<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Redirects</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Redirect
            </button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Redirects</p>
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
                        <p class="text-sm font-medium text-gray-500">Active Redirects</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Hits</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_hits || 0 }}</p>
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
                        placeholder="Search redirects..."
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="filteredRedirects.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No redirects found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            From URL
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            To URL
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hits
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
                    <tr v-for="redirect in filteredRedirects" :key="redirect.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ redirect.from_url }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ redirect.to_url }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ redirect.status_code || 301 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ redirect.hits || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="redirect.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                {{ redirect.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="editRedirect(redirect)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteRedirect(redirect)"
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
        <RedirectModal
            v-if="showCreateModal || showEditModal"
            @close="closeModal"
            @saved="handleRedirectSaved"
            :redirect="editingRedirect"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import RedirectModal from '../../../components/redirects/RedirectModal.vue';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';

const redirects = ref([]);
const statistics = ref(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRedirect = ref(null);

const filteredRedirects = computed(() => {
    if (!search.value) return redirects.value;
    
    const searchLower = search.value.toLowerCase();
    return redirects.value.filter(redirect => 
        redirect.from_url.toLowerCase().includes(searchLower) ||
        redirect.to_url.toLowerCase().includes(searchLower)
    );
});

const fetchRedirects = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/redirects');
        const { data } = parseResponse(response);
        redirects.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/redirects/statistics');
            statistics.value = parseSingleResponse(statsResponse);
        } catch (error) {
            // Calculate from redirects if endpoint doesn't exist
            statistics.value = {
                total: redirects.value.length,
                active: redirects.value.filter(r => r.is_active).length,
                total_hits: redirects.value.reduce((sum, r) => sum + (r.hits || 0), 0),
            };
        }
    } catch (error) {
        console.error('Failed to fetch redirects:', error);
    } finally {
        loading.value = false;
    }
};

const editRedirect = (redirect) => {
    editingRedirect.value = redirect;
    showEditModal.value = true;
};

const deleteRedirect = async (redirect) => {
    if (!confirm(`Are you sure you want to delete redirect from "${redirect.from_url}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/redirects/${redirect.id}`);
        await fetchRedirects();
    } catch (error) {
        console.error('Failed to delete redirect:', error);
        alert('Failed to delete redirect');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingRedirect.value = null;
};

const handleRedirectSaved = () => {
    fetchRedirects();
    closeModal();
};

onMounted(() => {
    fetchRedirects();
});
</script>

