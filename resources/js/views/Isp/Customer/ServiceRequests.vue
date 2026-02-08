<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('ispServiceRequests.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('ispServiceRequests.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="fetchRequests">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                    {{ $t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <Card>
            <div class="p-4 border-b border-border/40 flex justify-between items-center">
                <div class="flex gap-4">
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="$t('ispServiceRequests.fields.status')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('common.status.all') }}</SelectItem>
                            <SelectItem value="Pending">{{ $t('ispServiceRequests.status.Pending') }}</SelectItem>
                            <SelectItem value="Approved">{{ $t('ispServiceRequests.status.Approved') }}</SelectItem>
                            <SelectItem value="Rejected">{{ $t('ispServiceRequests.status.Rejected') }}</SelectItem>
                            <SelectItem value="Completed">{{ $t('ispServiceRequests.status.Completed') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="typeFilter">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="$t('ispServiceRequests.fields.type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('common.status.all') }}</SelectItem>
                            <SelectItem value="Upgrade">{{ $t('ispServiceRequests.types.Upgrade') }}</SelectItem>
                            <SelectItem value="Downgrade">{{ $t('ispServiceRequests.types.Downgrade') }}</SelectItem>
                            <SelectItem value="Cancellation">{{ $t('ispServiceRequests.types.Cancellation') }}</SelectItem>
                            <SelectItem value="Relocation">{{ $t('ispServiceRequests.types.Relocation') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div v-if="selectedRowsCount > 0" class="flex items-center gap-4 py-2 px-4 bg-primary/5 border border-primary/20 rounded-xl mb-4 mx-4 mt-4 animate-in slide-in-from-top-2">
                <span class="text-sm font-medium">
                    {{ selectedRowsCount }} {{ $t('ispServiceRequests.title') }}
                </span>
                <div class="h-4 w-px bg-border mx-2"></div>
                <div class="flex items-center gap-1">
                    <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                        <SelectTrigger class="w-[140px] h-8 text-xs rounded-lg">
                            <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Approved">Approve All</SelectItem>
                            <SelectItem value="Rejected">Reject All</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                        {{ t('common.actions.cancel') }}
                    </Button>
                </div>
            </div>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('common.messages.no_data')"
                class="border-0 rounded-none shadow-none"
            />
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, h, computed } from 'vue';
import { 
    Button, Card, Badge, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DataTable, Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';
import type { ServiceRequest } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const executing = ref<number | null>(null);
const requests = ref<ServiceRequest[]>([]);
const statusFilter = ref('all');
const typeFilter = ref('all');
const rowSelection = ref({});
const sorting = ref([{ id: 'date', desc: true }]);
const bulkActionSelection = ref('');
const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<ServiceRequest>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        size: 50,
    }),
    columnHelper.display({
        id: 'customer',
        header: t('ispServiceRequests.fields.customer'),
        cell: info => {
            const req = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, req.customer?.name),
                h('span', { class: 'text-xs text-muted-foreground' }, req.customer?.email)
            ]);
        }
    }),
    columnHelper.accessor('type', {
        header: t('ispServiceRequests.fields.type'),
        cell: info => h(Badge, { variant: 'outline' }, () => t(`ispServiceRequests.types.${info.getValue()}`)),
    }),
    columnHelper.accessor('created_at', {
        id: 'date',
        header: t('ispServiceRequests.fields.date'),
        cell: info => h('span', { class: 'text-xs' }, formatDate(info.getValue())),
    }),
    columnHelper.accessor('status', {
        header: t('ispServiceRequests.fields.status'),
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()) }, () => t(`ispServiceRequests.status.${info.getValue()}`)),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('ispServiceRequests.fields.actions')),
        cell: info => {
            const request = info.row.original;
            return h('div', { class: 'flex justify-end gap-2' }, [
                request.status === 'Pending' ? [
                    h(Button, {
                        variant: 'default',
                        size: 'sm',
                        onClick: (e: MouseEvent) => { e.stopPropagation(); handleUpdateStatus(request, 'Approved'); }
                    }, () => t('ispServiceRequests.actions.approve')),
                    h(Button, {
                        variant: 'destructive',
                        size: 'sm',
                        onClick: (e: MouseEvent) => { e.stopPropagation(); handleUpdateStatus(request, 'Rejected'); }
                    }, () => t('ispServiceRequests.actions.reject'))
                ] : (request.status === 'Approved' ? [
                    h(Button, {
                        variant: 'default',
                        size: 'sm',
                        disabled: executing.value === request.id,
                        onClick: (e: MouseEvent) => { e.stopPropagation(); handleExecute(request); }
                    }, () => [
                        executing.value === request.id ? h(Loader2, { class: 'w-3 h-3 mr-1 animate-spin' }) : null,
                        t('ispServiceRequests.actions.execute')
                    ])
                ] : null)
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return requests.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
        get sorting() { return sorting.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true
});

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    try {
        await Promise.all(ids.map(id => api.patch(`/admin/ja/isp/service-requests/${id}/status`, { status: action })));
        toast.success.action(t('ispServiceRequests.messages.successUpdate'));
        table.resetRowSelection();
        fetchRequests();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
    bulkActionSelection.value = '';
};

const fetchRequests = async () => {
    loading.value = true;
    try {
        const params: Record<string, string> = {};
        if (statusFilter.value !== 'all') params.status = statusFilter.value;
        if (typeFilter.value !== 'all') params.type = typeFilter.value;

        const response = await api.get('/admin/ja/isp/service-requests', { params });
        requests.value = response.data.data.data;
    } catch (_error) {
        toast.error.default(t('common.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const handleUpdateStatus = async (request: ServiceRequest, status: string) => {
    try {
        await api.patch(`/admin/ja/isp/service-requests/${request.id}/status`, { status });
        toast.success.action(t('ispServiceRequests.messages.successUpdate'));
        fetchRequests();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
};

const handleExecute = async (request: ServiceRequest) => {
    if (!confirm(t('ispServiceRequests.messages.confirmExecute'))) return;
    
    executing.value = request.id;
    try {
        await api.post(`/admin/ja/isp/service-requests/${request.id}/execute`);
        toast.success.action(t('ispServiceRequests.messages.successExecute'));
        fetchRequests();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        executing.value = null;
    }
};

const formatDate = (date: string) => {
    return dayjs(date).format('DD MMM YYYY HH:mm');
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'Completed': return 'success';
        case 'Approved': return 'default';
        case 'Pending': return 'warning';
        case 'Rejected': return 'destructive';
        default: return 'secondary';
    }
};

watch([statusFilter, typeFilter], () => {
    fetchRequests();
});

onMounted(() => {
    fetchRequests();
});
</script>
