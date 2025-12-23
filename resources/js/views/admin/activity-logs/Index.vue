<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.activityLogs.title') }}</h1>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-card shadow rounded-lg p-6">
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
            <div class="bg-card shadow rounded-lg p-6">
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
            <div class="bg-card shadow rounded-lg p-6">
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
            <div class="bg-card shadow rounded-lg p-6">
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

        <div class="bg-card shadow rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="t('features.activityLogs.filters.search')"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <select
                            v-model="typeFilter"
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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
                            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
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
                            <div v-if="log.properties" class="mt-2 text-xs text-gray-400">
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

const filteredLogs = computed(() => {
    if (!Array.isArray(logs.value)) {
        return [];
    }
    
    let filtered = logs.value;
    
    if (typeFilter.value) {
        // Check both 'type' and 'action' fields
        filtered = filtered.filter(log => 
            log?.type === typeFilter.value || log?.action === typeFilter.value
        );
    }
    
    if (userFilter.value) {
        filtered = filtered.filter(log => log?.user_id === parseInt(userFilter.value));
    }
    
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
        const response = await api.get('/admin/cms/activity-logs');
        // Handle paginated response
        let data = [];
        if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
        } else if (Array.isArray(response.data)) {
            data = response.data;
        } else if (response.data?.items && Array.isArray(response.data.items)) {
            data = response.data.items;
        }
        logs.value = data;
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/activity-logs/statistics');
            statistics.value = statsResponse.data;
        } catch (error) {
            // Calculate from logs if endpoint doesn't exist
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
                statistics.value = {
                    total: 0,
                    today: 0,
                    this_week: 0,
                    active_users: 0,
                };
            }
        }
    } catch (error) {
        console.error('Failed to fetch activity logs:', error);
    } finally {
        loading.value = false;
    }
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/admin/cms/users');
        const data = response.data?.data || response.data || [];
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

onMounted(() => {
    fetchLogs();
    fetchUsers();
});
</script>

