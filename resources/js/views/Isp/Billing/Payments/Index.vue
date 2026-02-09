<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('isp.billing.payments.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.billing.payments.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="exportPayments">
                    <Download class="w-4 h-4 mr-2" />
                    {{ t('common.actions.export') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.today') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.today) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.todayCount }} {{ t('isp.billing.payments.transactions') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.this_month') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(stats.month) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.monthCount }} {{ t('isp.billing.payments.transactions') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.pending') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats.pending) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.pendingCount }} {{ t('isp.billing.payments.awaiting') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.billing.payments.stats.failed') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.failed) }}</div>
                    <p class="text-xs text-muted-foreground">{{ stats.failedCount }} {{ t('isp.billing.payments.failed_tx') }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <Input
                            v-model="filters.search"
                            :placeholder="t('isp.billing.payments.search_placeholder')"
                            class="w-full"
                        />
                    </div>
                    <Select v-model="filters.status">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('common.status.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('common.status.all') }}</SelectItem>
                            <SelectItem value="success">{{ t('isp.billing.payments.status.success') }}</SelectItem>
                            <SelectItem value="pending">{{ t('isp.billing.payments.status.pending') }}</SelectItem>
                            <SelectItem value="failed">{{ t('isp.billing.payments.status.failed') }}</SelectItem>
                            <SelectItem value="expired">{{ t('isp.billing.payments.status.expired') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filters.gateway">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('isp.billing.payments.all_gateways')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('isp.billing.payments.all_gateways') }}</SelectItem>
                            <SelectItem value="midtrans">Midtrans</SelectItem>
                            <SelectItem value="xendit">Xendit</SelectItem>
                            <SelectItem value="manual">Manual</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>
        </Card>

        <!-- Payments Table -->
        <Card>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.billing.payments.no_data')"
                />
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-border/40 bg-card rounded-b-lg">
            <Pagination
                v-if="pagination.total > 0"
                :total-items="pagination.total"
                :per-page="pagination.perPage"
                :current-page="pagination.currentPage"
                @page-change="handlePageChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Card, CardContent, CardHeader, CardDescription,
    Button, Input, Badge, Pagination,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DataTable
} from '@/components/ui';

import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState
} from '@tanstack/vue-table';

import api from '@/services/api';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';

const { t } = useI18n();

interface Payment {
    id: number;
    transaction_id: string;
    customer_id: string;
    customer_name: string;
    invoice_number: string;
    amount: number;
    gateway: string;
    status: 'success' | 'pending' | 'failed' | 'expired';
    created_at: string;
}

const payments = ref<Payment[]>([]);
const loading = ref(false);

const columnHelper = createColumnHelper<Payment>();

const columns = [
    columnHelper.accessor('transaction_id', {
        header: t('isp.billing.payments.columns.id'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.transaction_id)
    }),
    columnHelper.accessor('customer_name', {
        header: t('isp.billing.payments.columns.customer'),
        cell: ({ row }) => h('div', [
            h('div', { class: 'font-medium' }, row.original.customer_name),
            h('div', { class: 'text-xs text-muted-foreground' }, row.original.customer_id)
        ])
    }),
    columnHelper.accessor('invoice_number', {
        header: t('isp.billing.payments.columns.invoice'),
        cell: ({ row }) => h('a', { 
            href: '#', 
            class: 'text-primary hover:underline',
            onClick: (e) => { e.preventDefault(); /* Handle invoice click */ }
        }, row.original.invoice_number)
    }),
    columnHelper.accessor('amount', {
        header: t('isp.billing.payments.columns.amount'),
        cell: ({ row }) => h('span', { class: 'font-medium' }, formatCurrency(row.original.amount))
    }),
    columnHelper.accessor('gateway', {
        header: t('isp.billing.payments.columns.gateway'),
        cell: ({ row }) => h(Badge, { variant: 'outline' }, row.original.gateway)
    }),
    columnHelper.accessor('status', {
        header: t('isp.billing.payments.columns.status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status) }, t(`isp.billing.payments.status.${row.original.status}`))
    }),
    columnHelper.accessor('created_at', {
        header: t('isp.billing.payments.columns.date'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Button, {
                variant: 'ghost',
                size: 'sm',
                onClick: () => viewPayment(row.original)
            }, [
                h(Eye, { class: 'w-4 h-4' })
            ])
        ])
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return payments.value },
    columns,
    state: {
        get sorting() { return sorting.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

const stats = reactive({
    today: 0,
    todayCount: 0,
    month: 0,
    monthCount: 0,
    pending: 0,
    pendingCount: 0,
    failed: 0,
    failedCount: 0
});

const filters = reactive({
    search: '',
    status: 'all',
    gateway: 'all'
});

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    perPage: 15,
    total: 0
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        success: 'default',
        pending: 'secondary',
        failed: 'destructive',
        expired: 'outline'
    };
    return variants[status] || 'outline';
};

const loadPayments = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/isp/payments', { params: { ...filters, page: pagination.currentPage } });
        if (response.data.success) {
            payments.value = response.data.data.items || [];
            pagination.total = response.data.data.total || 0;
            pagination.totalPages = response.data.data.last_page || 1;
            Object.assign(stats, response.data.data.stats || {});
        }
    } catch (error) {
        console.error('Failed to load payments:', error);
    } finally {
        loading.value = false;
    }
};

const handlePageChange = (page: number) => {
    pagination.currentPage = page;
    loadPayments();
};

const exportPayments = () => {
    window.open(`/api/admin/isp/payments/export?${new URLSearchParams(filters as Record<string, string>).toString()}`, '_blank');
};

const viewPayment = (payment: Payment) => {
    console.warn('View payment:', payment);
};

onMounted(() => {
    loadPayments();
});
</script>
