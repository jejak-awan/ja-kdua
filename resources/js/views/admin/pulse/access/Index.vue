<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <router-link to="/admin/journal-dashboard" class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </router-link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ t('features.accessJournal.title') }}</h1>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.accessJournal.description') }}</p>
                </div>
            </div>
            <Button
                variant="destructive"
                variant-type="outline"
                @click="clearLogs"
            >
                {{ t('features.system.logs.clear') }}
            </Button>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.accessJournal.stats.totalLogins') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.total_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.accessJournal.stats.failedLogins') }}</p>
                <p class="text-2xl font-semibold text-red-500">{{ statistics.failed_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.accessJournal.stats.todayLogins') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.today_logins || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.accessJournal.stats.uniqueIps') }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ statistics.unique_ips_today || 0 }}</p>
            </div>
            <div class="bg-card border border-border rounded-lg p-4">
                <p class="text-sm font-medium text-muted-foreground">{{ t('features.accessJournal.stats.activeSessions') }}</p>
                <p class="text-2xl font-semibold text-green-500">{{ statistics.active_sessions || 0 }}</p>
            </div>
        </div>

        <div class="bg-card border border-border rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <!-- Filters Row -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <Select
                            v-model="userFilter"
                            @update:model-value="fetchHistory()"
                        >
                            <SelectTrigger class="w-[180px]">
                                <SelectValue :placeholder="t('features.accessJournal.filters.allUsers')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('features.accessJournal.filters.allUsers') }}</SelectItem>
                                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            v-model="statusFilter"
                            @update:model-value="fetchHistory()"
                        >
                            <SelectTrigger class="w-[180px]">
                                <SelectValue :placeholder="t('features.accessJournal.filters.allStatus')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('features.accessJournal.filters.allStatus') }}</SelectItem>
                                <SelectItem value="success">{{ t('features.accessJournal.status.success') }}</SelectItem>
                                <SelectItem value="failed">{{ t('features.accessJournal.status.failed') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.accessJournal.filters.dateFrom') }}:</label>
                            <Input
                                v-model="dateFrom"
                                type="date"
                                @change="fetchHistory"
                                class="w-36"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.accessJournal.filters.dateTo') }}:</label>
                            <Input
                                v-model="dateTo"
                                type="date"
                                @change="fetchHistory"
                                class="w-36"
                            />
                        </div>
                    </div>
                    <Button
                        @click="exportHistory"
                        :disabled="exporting"
                    >
                        <Download class="w-4 h-4 mr-2" />
                        {{ exporting ? t('features.accessJournal.export.exporting') : t('features.accessJournal.export.button') }}
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.accessJournal.messages.loading') }}</p>
            </div>

            <div v-else-if="history.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.accessJournal.messages.empty') }}</p>
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
                                <Check v-if="entry.status === 'success'" class="w-5 h-5" />
                                <X v-else class="w-5 h-5" />
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
                            <p v-if="entry.failure_reason" class="text-xs text-destructive">{{ entry.failure_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination
                v-if="totalRecords > 0"
                :current-page="currentPage"
                :total-items="totalRecords"
                :per-page="perPage"
                @page-change="fetchHistory"
                @update:per-page="(val) => { perPage = val; fetchHistory(1); }"
                class="border-none shadow-none"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import {
    Button,
    Pagination,
    Input,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui';

import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';

interface User {
    id: number;
    name: string;
    email: string;
}

interface LoginEntry {
    id: number;
    user?: User | null;
    ip_address: string;
    status: 'success' | 'failed';
    login_at: string;
    failure_reason: string | null;
}

interface LoginStatistics {
    total_logins: number;
    failed_logins: number;
    today_logins: number;
    unique_ips_today: number;
    active_sessions: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const history = ref<LoginEntry[]>([]);
const users = ref<User[]>([]);
const statistics = ref<LoginStatistics | null>(null);
const loading = ref(false);
const exporting = ref(false);
const userFilter = ref('');
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const currentPage = ref(1);
const totalRecords = ref(0);

const fetchHistory = async (page: number = 1) : Promise<void> => {
    currentPage.value = page;
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', String(page));
        params.append('per_page', String(perPage.value));
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/admin/ja/access-journal?${params.toString()}`);
        
        // Parse nested response
        let data: LoginEntry[] = [];
        if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
            data = response.data.data.data;
            totalRecords.value = response.data.data.total || data.length;
        } else if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
            totalRecords.value = data.length;
        }
        history.value = data;
    } catch (error: any) {
        logger.error('Failed to fetch login history:', error.message);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () : Promise<void> => {
    try {
        const response = await api.get('/admin/ja/access-journal/statistics');
        statistics.value = response.data?.data || response.data;
    } catch (error: any) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const fetchUsers = async () : Promise<void> => {
    try {
        const response = await api.get('/admin/ja/users');
        const data = response.data?.data?.data || response.data?.data || [];
        users.value = Array.isArray(data) ? data : [];
    } catch (error: any) {
        logger.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const clearLogs = async () : Promise<void> => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?',
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/access-journal/clear');
        await fetchHistory();
        await fetchStatistics();
        toast.success.action(t('features.system.logs.messages.cleared') || 'Logs cleared successfully');
    } catch (error: any) {
        logger.error('Failed to clear logs:', error.message);
        toast.error.fromResponse(error);
    }
};

const exportHistory = async () : Promise<void> => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/admin/ja/access-journal/export?${params.toString()}`, {
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
        toast.success.action(t('features.analytics.export.success') || 'Export started');
    } catch (error: any) {
        logger.error('Failed to export:', error.message);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString();
};

onMounted(() => {
    fetchHistory();
    fetchStatistics();
    fetchUsers();
});
</script>
