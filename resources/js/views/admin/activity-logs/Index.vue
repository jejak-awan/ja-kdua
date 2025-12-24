<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.activityLogs.title') }}</h1>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.activityLogs.stats.total') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.activityLogs.stats.today') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.today || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.activityLogs.stats.activeUsers') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.active_users || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.activityLogs.stats.thisWeek') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ statistics.this_week || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <!-- Row 1: Search, Filters, Export -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="t('features.activityLogs.filters.search')"
                            class="w-48 px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                        <select
                            v-model="typeFilter"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option value="">{{ t('features.activityLogs.filters.allTypes') }}</option>
                            <option value="created">{{ t('features.activityLogs.filters.types.created') }}</option>
                            <option value="updated">{{ t('features.activityLogs.filters.types.updated') }}</option>
                            <option value="deleted">{{ t('features.activityLogs.filters.types.deleted') }}</option>
                            <option value="login">{{ t('features.activityLogs.filters.types.login') }}</option>
                            <option value="logout">{{ t('features.activityLogs.filters.types.logout') }}</option>
                            <option value="viewed">{{ t('features.activityLogs.filters.types.viewed') }}</option>
                            <option value="published">{{ t('features.activityLogs.filters.types.published') }}</option>
                            <option value="unpublished">{{ t('features.activityLogs.filters.types.unpublished') }}</option>
                        </select>
                        <select
                            v-model="userFilter"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option value="">{{ t('features.activityLogs.filters.allUsers') }}</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="exportLogs"
                        :disabled="exporting"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90 disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ exporting ? 'Exporting...' : 'Export CSV' }}
                    </button>
                </div>
                
                <!-- Row 2: Date Range & Per Page -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.activityLogs.filters.dateFrom') || 'Dari' }}:</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.activityLogs.filters.dateTo') || 'Sampai' }}:</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            >
                        </div>
                        <button
                            v-if="dateFrom || dateTo"
                            @click="clearDateFilter"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Clear
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-muted-foreground">Per halaman:</label>
                        <select
                            v-model="perPage"
                            @change="fetchLogs"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                            <option :value="200">200</option>
                        </select>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.activityLogs.messages.loading') }}</p>
            </div>

            <div v-else-if="filteredLogs.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.activityLogs.messages.empty') }}</p>
            </div>

            <div v-else class="divide-y divide-border">
                <div
                    v-for="log in filteredLogs"
                    :key="log.id"
                    class="px-6 py-4 hover:bg-muted"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-green-500/20 text-green-400': (log.action || log.type) === 'created',
                                        'bg-blue-500/20 text-blue-400': (log.action || log.type) === 'updated',
                                        'bg-red-500/20 text-red-400': (log.action || log.type) === 'deleted',
                                        'bg-purple-100 text-purple-800': (log.action || log.type) === 'login' || (log.action || log.type) === 'logout',
                                    }"
                                >
                                    {{ t(`features.activityLogs.filters.types.${log.action || log.type}`) || (log.action || log.type || t('features.activityLogs.messages.unknown')) }}
                                </span>
                                <span class="text-sm font-medium text-foreground">{{ log.description }}</span>
                            </div>
                            <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                                <span>{{ log.user?.name || t('features.activityLogs.messages.system') }}</span>
                                <span>{{ log.model_type || '' }}</span>
                                <span>{{ formatDate(log.created_at) }}</span>
                            </div>
                            <div v-if="log.properties" class="mt-2 text-xs text-muted-foreground">
                                <details>
                                    <summary class="cursor-pointer hover:text-muted-foreground">{{ t('features.activityLogs.messages.viewDetails') }}</summary>
                                    <pre class="mt-2 p-2 bg-muted rounded text-xs overflow-x-auto">{{ JSON.stringify(log.properties, null, 2) }}</pre>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();

const logs = ref([]);
const users = ref([]);
const statistics = ref(null);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const userFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(50);
const exporting = ref(false);

const filteredLogs = computed(() => {
    if (!Array.isArray(logs.value)) {
        return [];
    }
    
    let filtered = logs.value;
    
    // Client-side filtering for search (server already filtered by action/user/date)
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(log => 
            log?.description?.toLowerCase().includes(searchLower) ||
            log?.model_type?.toLowerCase().includes(searchLower) ||
            log?.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchLogs = async () => {
    loading.value = true;
    try {
        // Build query params for server-side filtering
        const params = new URLSearchParams();
        params.append('per_page', perPage.value);
        
        if (typeFilter.value) params.append('action', typeFilter.value);
        if (userFilter.value) params.append('user_id', userFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        const response = await api.get(`/admin/cms/activity-logs?${params.toString()}`);
        
        // API response structure: { success: true, data: { data: [...items], current_page, ... } }
        let data = [];
        if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
            data = response.data.data.data;
        } else if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
        } else if (Array.isArray(response.data)) {
            data = response.data;
        }
        logs.value = data;
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/activity-logs/statistics');
            statistics.value = statsResponse.data?.data || statsResponse.data;
        } catch (error) {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            if (Array.isArray(logs.value)) {
                statistics.value = {
                    total: logs.value.length,
                    today: logs.value.filter(l => l?.created_at && new Date(l.created_at) >= today).length,
                    this_week: logs.value.filter(l => l?.created_at && new Date(l.created_at) >= weekAgo).length,
                    active_users: new Set(logs.value.map(l => l?.user_id).filter(Boolean)).size,
                };
            } else {
                statistics.value = { total: 0, today: 0, this_week: 0, active_users: 0 };
            }
        }
    } catch (error) {
        console.error('Failed to fetch activity logs:', error);
    } finally {
        loading.value = false;
    }
};

const exportLogs = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (typeFilter.value) params.append('action', typeFilter.value);
        if (userFilter.value) params.append('user_id', userFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        const response = await api.get(`/admin/cms/activity-logs/export?${params.toString()}`, {
            responseType: 'blob'
        });
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `activity-logs-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to export activity logs:', error);
        alert('Gagal mengekspor log aktivitas');
    } finally {
        exporting.value = false;
    }
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs();
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/admin/cms/users');
        const data = response.data?.data?.data || response.data?.data || response.data || [];
        users.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

// Watch filters for auto-refresh
import { watch } from 'vue';

watch([typeFilter, userFilter, dateFrom, dateTo], () => {
    fetchLogs();
});

onMounted(() => {
    fetchLogs();
    fetchUsers();
});
</script>

