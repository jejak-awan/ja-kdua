<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <router-link to="/admin/logs-dashboard" class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </router-link>
                <h1 class="text-2xl font-bold text-foreground">{{ t('features.activityLogs.title') }}</h1>
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
                        <Input
                            v-model="search"
                            type="text"
                            :placeholder="t('features.activityLogs.filters.search')"
                            class="w-48"
                        />
                        <Select v-model="typeFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue :placeholder="t('features.activityLogs.filters.allTypes')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('features.activityLogs.filters.allTypes') }}</SelectItem>
                                <SelectItem value="created">{{ t('features.activityLogs.filters.types.created') }}</SelectItem>
                                <SelectItem value="updated">{{ t('features.activityLogs.filters.types.updated') }}</SelectItem>
                                <SelectItem value="deleted">{{ t('features.activityLogs.filters.types.deleted') }}</SelectItem>
                                <SelectItem value="login">{{ t('features.activityLogs.filters.types.login') }}</SelectItem>
                                <SelectItem value="logout">{{ t('features.activityLogs.filters.types.logout') }}</SelectItem>
                                <SelectItem value="viewed">{{ t('features.activityLogs.filters.types.viewed') }}</SelectItem>
                                <SelectItem value="published">{{ t('features.activityLogs.filters.types.published') }}</SelectItem>
                                <SelectItem value="unpublished">{{ t('features.activityLogs.filters.types.unpublished') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="userFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue :placeholder="t('features.activityLogs.filters.allUsers')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('features.activityLogs.filters.allUsers') }}</SelectItem>
                                <SelectItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button
                        @click="exportLogs"
                        :disabled="exporting"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ exporting ? 'Exporting...' : 'Export CSV' }}
                    </Button>
                </div>
                
                <!-- Row 2: Date Range & Per Page -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.activityLogs.filters.dateFrom') || 'Dari' }}:</label>
                            <Input
                                v-model="dateFrom"
                                type="date"
                                class="w-36"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-muted-foreground">{{ t('features.activityLogs.filters.dateTo') || 'Sampai' }}:</label>
                            <Input
                                v-model="dateTo"
                                type="date"
                                class="w-36"
                            />
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
                        <Select
                            :model-value="String(perPage)"
                            @update:model-value="perPage = Number($event); fetchLogs()"
                        >
                            <SelectTrigger class="w-[80px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="25">25</SelectItem>
                                <SelectItem value="50">50</SelectItem>
                                <SelectItem value="100">100</SelectItem>
                                <SelectItem value="200">200</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.activityLogs.messages.loading') }}</p>
            </div>

            <div v-else-if="filteredLogs.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.activityLogs.messages.empty') }}</p>
            </div>

            <div v-if="filteredLogs.length > 0" class="divide-y divide-border">
                <div
                    v-for="log in filteredLogs"
                    :key="log.id"
                    class="px-6 py-4 hover:bg-muted"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <Badge
                                    :variant="(log.action || log.type) === 'deleted' ? 'destructive' : 'default'"
                                    :class="{
                                        'bg-green-500 hover:bg-green-600': (log.action || log.type) === 'created',
                                        'bg-blue-500 hover:bg-blue-600': (log.action || log.type) === 'updated',
                                    }"
                                >
                                    {{ t(`features.activityLogs.filters.types.${log.action || log.type}`) || (log.action || log.type || t('features.activityLogs.messages.unknown')) }}
                                </Badge>
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

            <!-- Pagination -->
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(perPage)"
                @page-change="fetchLogs"
                @update:per-page="(val) => { perPage = val; fetchLogs(1); }"
                class="border-none shadow-none mt-4 px-6 py-4"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useConfirm } from '../../../composables/useConfirm';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import Button from '../../../components/ui/button.vue';
import Pagination from '../../../components/ui/pagination.vue';
import Input from '../../../components/ui/input.vue';
import Badge from '../../../components/ui/badge.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const logs = ref([]);
const users = ref([]);
const statistics = ref(null);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const userFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const ipFilter = ref('');
const perPage = ref(25);
const pagination = ref(null);
const sortField = ref('created_at');
const sortOrder = ref('desc');
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

const fetchLogs = async (page = 1) => {
    loading.value = true;
    try {
        // Build query params for server-side filtering
        const params = {
            page,
            per_page: perPage.value
        };
        
        if (typeFilter.value && typeFilter.value !== 'all') params.action = typeFilter.value;
        if (userFilter.value && userFilter.value !== 'all') params.user_id = userFilter.value;
        if (dateFrom.value) params.date_from = dateFrom.value;
        if (dateTo.value) params.date_to = dateTo.value;
        if (search.value) params.search = search.value;
        
        const response = await api.get('/admin/ja/activity-logs', { params });
        const { data, pagination: pag } = parseResponse(response);
        
        logs.value = ensureArray(data);
        pagination.value = pag;
        
        // Fetch statistics (only on first load or if needed)
        try {
            const statsResponse = await api.get('/admin/ja/activity-logs/statistics');
            statistics.value = statsResponse.data?.data || statsResponse.data;
        } catch (error) {
            // Fallback stats if endpoint fails
            statistics.value = {
                total: pagination.value?.total || logs.value.length,
                today: 0,
                this_week: 0,
                active_users: 0
            };
        }
    } catch (error) {
        console.error('Failed to fetch activity logs:', error);
    } finally {
        loading.value = false;
    }
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs();
};

const clearLogs = async () => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?',
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/ja/activity-logs/clear');
        toast.success.action(t('features.system.logs.messages.cleared'));
        fetchLogs();
    } catch (error) {
        console.error('Failed to clear logs:', error);
        toast.error.fromResponse(error);
    }
};
const exportLogs = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (typeFilter.value && typeFilter.value !== 'all') params.append('action', typeFilter.value);
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        const response = await api.get(`/admin/ja/activity-logs/export?${params.toString()}`, {
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
        toast.success.action(t('features.analytics.export.success') || 'Export started');
    } catch (error) {
        console.error('Failed to export activity logs:', error);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/admin/ja/users');
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

watch([typeFilter, userFilter, dateFrom, dateTo], () => {
    fetchLogs();
});

onMounted(() => {
    fetchLogs();
    fetchUsers();
});
</script>

