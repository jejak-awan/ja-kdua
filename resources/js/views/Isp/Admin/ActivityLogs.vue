<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.activity_logs.title', 'Activity Logs') }}</h1>
            <p class="text-muted-foreground">{{ $t('isp.admin.activity_logs.subtitle', 'Track all admin actions with IP and color-coded categories') }}</p>
        </div>

        <!-- Filters -->
        <Card class="border-border/40">
            <CardContent class="p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px] max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="filters.search" placeholder="Search description..." class="pl-9" @input="debouncedFetch" />
                    </div>
                    <Select v-model="filters.action" @update:model-value="() => fetchData()">
                        <SelectTrigger class="w-[140px]"><SelectValue placeholder="All Actions" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Actions</SelectItem>
                            <SelectItem value="create">Create</SelectItem>
                            <SelectItem value="update">Update</SelectItem>
                            <SelectItem value="delete">Delete</SelectItem>
                            <SelectItem value="login">Login</SelectItem>
                            <SelectItem value="export">Export</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filters.resource_type" @update:model-value="() => fetchData()">
                        <SelectTrigger class="w-[160px]"><SelectValue placeholder="All Resources" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Resources</SelectItem>
                            <SelectItem value="Customer">Customer</SelectItem>
                            <SelectItem value="Invoice">Invoice</SelectItem>
                            <SelectItem value="Voucher">Voucher</SelectItem>
                            <SelectItem value="Coupon">Coupon</SelectItem>
                            <SelectItem value="Mitra">Mitra</SelectItem>
                            <SelectItem value="PrintTemplate">PrintTemplate</SelectItem>
                        </SelectContent>
                    </Select>
                    <Input v-model="filters.from" type="date" class="w-[140px]" @change="fetchData" />
                    <Input v-model="filters.to" type="date" class="w-[140px]" @change="fetchData" />
                </div>
            </CardContent>
        </Card>

        <!-- Logs Table -->
        <Card class="border-border/40">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/30 border-b border-border/30">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Time</th>
                                <th class="px-4 py-3 text-left font-medium">User</th>
                                <th class="px-4 py-3 text-left font-medium">Action</th>
                                <th class="px-4 py-3 text-left font-medium">Description</th>
                                <th class="px-4 py-3 text-left font-medium">Resource</th>
                                <th class="px-4 py-3 text-left font-medium">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <LoaderCircle class="w-6 h-6 animate-spin mx-auto text-primary" />
                                </td>
                            </tr>
                            <tr v-else-if="logs.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                    No activity logs found.
                                </td>
                            </tr>
                            <tr v-for="log in logs" :key="log.id" class="border-b border-border/10 hover:bg-muted/5 transition-colors">
                                <td class="px-4 py-2.5 text-xs text-muted-foreground whitespace-nowrap">{{ formatTime(log.created_at) }}</td>
                                <td class="px-4 py-2.5 text-xs font-medium">{{ log.user?.name || '-' }}</td>
                                <td class="px-4 py-2.5">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase text-white"
                                        :style="{ backgroundColor: actionColors[log.action] || '#6b7280' }"
                                    >{{ log.action }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-xs max-w-[300px] truncate" :title="log.description">{{ log.description }}</td>
                                <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                    <span v-if="log.resource_type">{{ log.resource_type }}#{{ log.resource_id }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-2.5 text-xs font-mono text-muted-foreground">{{ log.ip_address || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.lastPage > 1" class="flex items-center justify-between p-4 border-t border-border/20">
                    <span class="text-xs text-muted-foreground">Page {{ pagination.currentPage }} of {{ pagination.lastPage }} ({{ pagination.total }} records)</span>
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" :disabled="pagination.currentPage <= 1" @click="goToPage(pagination.currentPage - 1)">Prev</Button>
                        <Button variant="outline" size="sm" :disabled="pagination.currentPage >= pagination.lastPage" @click="goToPage(pagination.currentPage + 1)">Next</Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import {
    Card, CardContent, Button, Input,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';

import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

useI18n();
const loading = ref(true);

interface LogEntry {
    id: number;
    user_id: number | null;
    action: string;
    resource_type: string | null;
    resource_id: number | null;
    description: string;
    ip_address: string | null;
    created_at: string;
    user?: { id: number; name: string } | null;
}

const logs = ref<LogEntry[]>([]);
const actionColors = ref<Record<string, string>>({});
const filters = ref({
    search: '',
    action: 'all',
    resource_type: 'all',
    from: '',
    to: '',
});
const pagination = ref({ currentPage: 1, lastPage: 1, total: 0 });

let debounceTimer: ReturnType<typeof setTimeout>;
const debouncedFetch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchData(), 400);
};

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = { page, per_page: 25 };
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.action !== 'all') params.action = filters.value.action;
        if (filters.value.resource_type !== 'all') params.resource_type = filters.value.resource_type;
        if (filters.value.from) params.from = filters.value.from;
        if (filters.value.to) params.to = filters.value.to;

        const response = await api.get('/admin/ja/isp/activity-logs', { params });
        const data = response.data.data;
        const paginatedData = data.data;
        logs.value = paginatedData.data || [];
        pagination.value = {
            currentPage: paginatedData.current_page || 1,
            lastPage: paginatedData.last_page || 1,
            total: paginatedData.total || 0,
        };
        if (data.colors) actionColors.value = data.colors;
    } catch (error) {
        console.error('Fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const goToPage = (page: number) => fetchData(page);

const formatTime = (dt: string) => {
    if (!dt) return '-';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
        d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

onMounted(fetchData);
</script>
