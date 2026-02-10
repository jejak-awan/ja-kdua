<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.billing.invoices.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.billing.invoices.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="gap-2 rounded-xl" @click="fetchInvoices">
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                    {{ t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Filters & Table -->
        <Card class="rounded-2xl border border-border shadow-sm overflow-hidden">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="t('common.labels.search')" class="pl-9" />
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue :placeholder="t('common.status.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('common.status.all') }}</SelectItem>
                            <SelectItem value="paid">{{ t('isp.billing.status.paid') }}</SelectItem>
                            <SelectItem value="unpaid">{{ t('isp.billing.status.unpaid') }}</SelectItem>
                            <SelectItem value="cancelled">{{ t('isp.billing.status.cancelled') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Standardized Bulk Action Bar -->
            <Transition
                enter-active-class="transition-[opacity,transform] duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-[opacity,transform] duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="selectedRowsCount > 0" class="flex items-center gap-4 py-2 px-4 bg-primary/5 border-b border-primary/20 animate-in slide-in-from-top-2">
                    <span class="text-sm font-medium">
                        {{ selectedRowsCount }} {{ t('isp.billing.invoices.title') }} Selected
                    </span>
                    <div class="h-4 w-px bg-border mx-2"></div>
                    <div class="flex items-center gap-2">
                        <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                            <SelectTrigger class="w-[160px] h-8 text-xs rounded-lg">
                                <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="paid" class="text-success focus:text-success">Mark as Paid</SelectItem>
                                <SelectItem value="cancelled" class="text-destructive focus:text-destructive">Cancel Invoices</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                            {{ t('common.actions.cancel') }}
                        </Button>
                    </div>
                </div>
            </Transition>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('common.messages.no_data')"
            />
            
            <div class="p-4 border-t border-border/40" v-if="pagination">
                <Pagination
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="pagination.per_page"
                    @page-change="handlePageChange"
                />
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Button, Card, Input, Badge, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Pagination,
    DataTable, Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/check-circle.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { parseResponse, type PaginationData } from '@/utils/responseParser';
import type { IspInvoice } from '@/types/isp';
import dayjs from 'dayjs';

const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const invoices = ref<IspInvoice[]>([]);
const pagination = ref<PaginationData | null>(null);
const search = ref('');
const statusFilter = ref('all');
const rowSelection = ref({});
const bulkActionSelection = ref('');

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<IspInvoice>();

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
    columnHelper.accessor('id', {
        header: 'ID',
        cell: info => h('span', { class: 'font-mono text-xs' }, `#${info.getValue()}`),
    }),
    columnHelper.accessor('user.name', {
        header: t('isp.billing.fields.customer'),
        cell: info => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'font-medium' }, info.row.original.user?.name || 'Unknown'),
            h('span', { class: 'text-xs text-muted-foreground' }, info.row.original.user?.email || '')
        ]),
    }),
    columnHelper.accessor('billing_period', {
        header: t('isp.billing.fields.period'),
        cell: info => info.getValue(),
    }),
    columnHelper.accessor('amount', {
        header: t('isp.billing.fields.amount'),
        cell: info => h('span', { class: 'font-bold' }, `Rp ${new Intl.NumberFormat('id-ID').format(info.getValue())}`),
    }),
    columnHelper.accessor('due_date', {
        header: t('isp.billing.fields.due_date'),
        cell: info => dayjs(info.getValue()).format('DD MMM YYYY'),
    }),
    columnHelper.accessor('status', {
        header: t('common.status.title'),
        cell: info => h(Badge, { 
            variant: info.getValue() === 'paid' ? 'success' : (info.getValue() === 'unpaid' ? 'warning' : 'destructive') 
        }, () => t(`isp.billing.status.${info.getValue()}`)),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: info => {
            const invoice = info.row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                invoice.status === 'unpaid' && h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: () => handleAction(invoice, 'paid'),
                    title: 'Mark as Paid'
                }, () => h(CheckCircle, { class: 'w-4 h-4 text-success' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: () => handleDownload(invoice),
                    title: 'Download PDF'
                }, () => h(Download, { class: 'w-4 h-4' })),
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return invoices.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
});

const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

const fetchInvoices = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = { page, per_page: 10 };
        if (search.value) params.search = search.value;
        if (statusFilter.value !== 'all') params.status = statusFilter.value;

        const response = await api.get('/admin/ja/isp/billing/invoices', { params });
        const parsed = parseResponse(response);
        invoices.value = parsed.data as IspInvoice[];
        pagination.value = parsed.pagination;
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loading.value = false;
    }
};

const handleAction = async (invoice: IspInvoice, action: string) => {
    try {
        await api.patch(`/admin/ja/isp/billing/invoices/${invoice.id}`, { status: action });
        toast.success.action(t('common.messages.success.action'));
        fetchInvoices(pagination.value?.current_page || 1);
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    try {
        await Promise.all(ids.map(id => api.patch(`/admin/ja/isp/billing/invoices/${id}`, { status: action })));
        toast.success.action(t('common.messages.success.action'));
        table.resetRowSelection();
        fetchInvoices();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
    bulkActionSelection.value = '';
};

const handleDownload = (invoice: IspInvoice) => {
    toast.info('Downloading...', `Invoice #${invoice.id} PDF is being generated.`);
};

const handlePageChange = (page: number) => {
    fetchInvoices(page);
};

watch([search, statusFilter], () => {
    fetchInvoices(1);
});

onMounted(() => {
    fetchInvoices();
});
</script>
