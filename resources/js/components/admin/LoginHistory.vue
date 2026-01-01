<template>
    <div class="bg-card rounded-lg shadow">
        <div class="p-6">
            <div v-if="loading && !history.length" class="flex justify-center py-8">
                <Spinner />
            </div>

            <div v-else-if="history.length === 0" class="text-center py-8">
                <p class="text-muted-foreground">No login history found</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="entry in history"
                    :key="entry.id"
                    class="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors"
                >
                    <div class="flex items-center space-x-4 flex-1">
                        <!-- Status Icon -->
                        <div
                            :class="[
                                'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                                entry.status === 'success'
                                    ? 'bg-green-100 dark:bg-green-900/30'
                                    : entry.status === 'failed'
                                    ? 'bg-red-100 dark:bg-red-900/30'
                                    : 'bg-yellow-100 dark:bg-yellow-900/30',
                            ]"
                        >
                            <svg
                                v-if="entry.status === 'success'"
                                class="w-5 h-5 text-green-600 dark:text-green-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5 text-red-600 dark:text-red-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <p
                                    :class="[
                                        'text-sm font-medium',
                                        entry.status === 'success'
                                            ? 'text-green-700 dark:text-green-400'
                                            : 'text-red-700 dark:text-red-400',
                                    ]"
                                >
                                    {{ entry.status === 'success' ? 'Successful Login' : 'Failed Login' }}
                                </p>
                                <span
                                    v-if="entry.failure_reason"
                                    class="text-xs text-muted-foreground"
                                >
                                    ({{ entry.failure_reason }})
                                </span>
                            </div>
                            <div class="mt-1 space-y-1">
                                <p class="text-sm text-muted-foreground">
                                    <span class="font-medium">IP:</span> {{ entry.ip_address }}
                                </p>
                                <p
                                    v-if="entry.user_agent"
                                    class="text-xs text-muted-foreground truncate"
                                    :title="entry.user_agent"
                                >
                                    {{ parseUserAgent(entry.user_agent) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(entry.login_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="totalItems > 0" class="mt-6">
                <Pagination
                    :current-page="currentPage"
                    :total-items="totalItems"
                    :per-page="Number(perPage)"
                    :show-page-numbers="true"
                    @page-change="handlePageChange"
                    @update:per-page="handlePerPageChange"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import Spinner from '../Spinner.vue';
import Pagination from '../ui/pagination.vue';

const history = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalItems = ref(0);
const perPage = ref(10); // Default to 10 as requested

const fetchHistory = async () => {
    loading.value = true;
    try {
        const response = await api.get('/profile/login-history', {
            params: {
                per_page: perPage.value,
                page: currentPage.value,
            },
        });

        if (response.data?.success) {
            // Check BaseApiController paginated structure:
            // response.data.data = { data: [...], current_page: 1, total: 100, ... }
            const paginatedData = response.data.data;
            
            if (paginatedData) {
                history.value = paginatedData.data || [];
                totalItems.value = paginatedData.total || 0;
                currentPage.value = paginatedData.current_page || 1;
                perPage.value = parseInt(paginatedData.per_page) || 10;
            }
        }
    } catch (error) {
        console.error('Failed to fetch login history:', error);
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchHistory();
};

const handlePerPageChange = (newPerPage) => {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchHistory();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const parseUserAgent = (userAgent) => {
    if (!userAgent) return 'Unknown';
    
    // Simple user agent parsing
    if (userAgent.includes('Chrome')) return 'Chrome';
    if (userAgent.includes('Firefox')) return 'Firefox';
    if (userAgent.includes('Safari')) return 'Safari';
    if (userAgent.includes('Edge')) return 'Edge';
    if (userAgent.includes('Opera')) return 'Opera';
    
    return userAgent.substring(0, 50) + (userAgent.length > 50 ? '...' : '');
};

onMounted(() => {
    fetchHistory();
});
</script>

