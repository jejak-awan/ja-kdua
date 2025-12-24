<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <router-link to="/admin/logs-dashboard" class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </router-link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ t('features.login_history.title') }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.login_history.description') }}</p>
                </div>
            </div>
            <button
                @click="clearLogs"
                class="px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-card hover:bg-red-500/20"
            >
                {{ t('features.system.logs.clear') }}
            </button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.login_history.stats.totalLogins') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.total_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.login_history.stats.failedLogins') }}</p>
                <p class="text-2xl font-semibold text-red-500">{{ statistics.failed_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.login_history.stats.todayLogins') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.today_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.login_history.stats.uniqueIps') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.unique_ips_today || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.login_history.stats.activeSessions') }}</p>
                <p class="text-2xl font-semibold text-green-500">{{ statistics.active_sessions || 0 }}</p>
            </div>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <!-- Filters Row -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="userFilter"
                            @change="fetchHistory"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option value="">{{ t('features.login_history.filters.allUsers') }}</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                        <select
                            v-model="statusFilter"
                            @change="fetchHistory"
                            class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option value="">{{ t('features.login_history.filters.allStatus') }}</option>
                            <option value="success">{{ t('features.login_history.status.success') }}</option>
                            <option value="failed">{{ t('features.login_history.status.failed') }}</option>
                        </select>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.login_history.filters.dateFrom') }}:</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                @change="fetchHistory"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.login_history.filters.dateTo') }}:</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                @change="fetchHistory"
                                class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            >
                        </div>
                    </div>
                    <button
                        @click="exportHistory"
                        :disabled="exporting"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90 disabled:opacity-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ exporting ? t('features.login_history.export.exporting') : t('features.login_history.export.button') }}
                    </button>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.login_history.messages.loading') }}</p>
            </div>

            <div v-else-if="history.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.login_history.messages.empty') }}</p>
            </div>

            <div v-else class="divide-y divide-border">
                <div
                    v-for="entry in history"
                    :key="entry.id"
                    class="px-6 py-4 hover:bg-muted/50 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Status Icon -->
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    entry.status === 'success'
                                        ? 'bg-green-500/20 text-green-500'
                                        : 'bg-red-500/20 text-red-500'
                                ]"
                            >
                                <svg v-if="entry.status === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <!-- Details -->
                            <div>
                                <p class="font-medium text-foreground">{{ entry.user?.name || 'Unknown User' }}</p>
                                <p class="text-sm text-muted-foreground">{{ entry.user?.email || '' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-foreground">{{ entry.ip_address }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(entry.login_at) }}</p>
                            <p v-if="entry.failure_reason" class="text-xs text-red-500">{{ entry.failure_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="history.length > 0" class="px-6 py-4 border-t border-border flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ t('features.login_history.pagination.showing', { count: history.length, total: totalRecords }) }}
                </p>
                <div class="flex items-center gap-2">
                    <select
                        v-model="perPage"
                        @change="fetchHistory"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm"
                    >
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();

const history = ref([]);
const users = ref([]);
const statistics = ref(null);
const loading = ref(false);
const exporting = ref(false);
const userFilter = ref('');
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const totalRecords = ref(0);

const fetchHistory = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('per_page', perPage.value);
        if (userFilter.value) params.append('user_id', userFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/admin/cms/login-history?${params.toString()}`);
        
        // Parse nested response
        let data = [];
        if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
            data = response.data.data.data;
            totalRecords.value = response.data.data.total || data.length;
        } else if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
            totalRecords.value = data.length;
        }
        history.value = data;
    } catch (error) {
        console.error('Failed to fetch login history:', error);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () => {
    try {
        const response = await api.get('/admin/cms/login-history/statistics');
        statistics.value = response.data?.data || response.data;
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/admin/cms/users');
        const data = response.data?.data?.data || response.data?.data || [];
        users.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
};

const clearLogs = async () => {
    if (!confirm(t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?')) {
        return;
    }

    try {
        await api.post('/admin/cms/login-history/clear');
        fetchHistory();
        fetchStatistics();
    } catch (error) {
        console.error('Failed to clear logs:', error);
    }
};

const exportHistory = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (userFilter.value) params.append('user_id', userFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/admin/cms/login-history/export?${params.toString()}`, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `login-history-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Failed to export:', error);
        alert(t('features.login_history.export.failed'));
    } finally {
        exporting.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString();
};

onMounted(() => {
    fetchHistory();
    fetchStatistics();
    fetchUsers();
});
</script>
