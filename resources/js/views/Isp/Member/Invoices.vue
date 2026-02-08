<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div>
            <h1 class="text-2xl font-bold text-foreground">{{ t('isp.member.invoices.title') }}</h1>
            <p class="text-sm text-muted-foreground">{{ t('isp.member.invoices.subtitle') }}</p>
        </div>

        <Card class="rounded-2xl border border-border shadow-sm overflow-hidden">
            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('isp.member.invoices.no_invoices')"
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
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Badge, Button, DataTable, Pagination } from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { parseResponse, type PaginationData } from '@/utils/responseParser';
import dayjs from 'dayjs';
import type { IspInvoice } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const invoices = ref<IspInvoice[]>([]);
const loading = ref(true);
const pagination = ref<PaginationData | null>(null);

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<IspInvoice>();

const columns = [
    columnHelper.accessor('id', {
        header: t('isp.member.invoices.table.id'),
        cell: info => h('span', { class: 'font-mono text-xs opacity-70' }, `INV-${info.getValue()}-${info.row.original.billing_period}`),
    }),
    columnHelper.accessor('billing_period', {
        header: t('isp.member.invoices.table.period'),
        cell: info => h('span', { class: 'font-medium' }, info.getValue()),
    }),
    columnHelper.accessor('amount', {
        header: t('isp.member.invoices.table.amount'),
        cell: info => h('span', { class: 'font-bold' }, `Rp ${new Intl.NumberFormat('id-ID').format(info.getValue())}`),
    }),
    columnHelper.accessor('due_date', {
        header: t('isp.member.invoices.table.due_date'),
        cell: info => dayjs(info.getValue()).format('DD MMM YYYY'),
    }),
    columnHelper.accessor('status', {
        header: t('isp.member.invoices.table.status'),
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()) }, () => info.getValue()),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.member.invoices.table.actions')),
        cell: info => {
            const invoice = info.row.original;
            return h('div', { class: 'flex justify-end gap-2' }, [
                invoice.status === 'unpaid' && h(Button, {
                    variant: 'outline',
                    size: 'sm',
                    class: 'rounded-xl h-8',
                    onClick: () => handlePay(invoice)
                }, () => [
                    h(CreditCard, { class: 'w-3.5 h-3.5 mr-2' }),
                    t('isp.member.invoices.pay_now')
                ]),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'rounded-xl h-8',
                    onClick: () => handleDownload()
                }, () => [
                    h(Download, { class: 'w-3.5 h-3.5 mr-2 opacity-70' }),
                    t('isp.member.invoices.download_pdf')
                ])
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return invoices.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const getStatusVariant = (status: IspInvoice['status']) => {
    switch (status) {
        case 'paid': return 'success';
        case 'unpaid': return 'warning';
        case 'cancelled': return 'destructive';
        default: return 'secondary';
    }
};

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/member/invoices', { params: { page, per_page: 10 } });
        const parsed = parseResponse(response);
        invoices.value = parsed.data as IspInvoice[];
        pagination.value = parsed.pagination;
    } catch (_error) {
        toast.error.default(t('isp.member.invoices.error_load'));
    } finally {
        loading.value = false;
    }
};

const handlePay = async (invoice: IspInvoice) => {
    try {
        const response = await api.post(`/admin/ja/isp/payments/invoice/${invoice.id}/create`);
        const { redirect_url } = response.data.data;
        
        toast.info('Payment Initialized', 'Redirecting to secure payment gateway...');
        
        // In a real app, this would open Midtrans Snap or redirect
        setTimeout(() => {
            window.open(redirect_url, '_blank');
        }, 1000);
        
        // Refresh data after a delay assuming user might Have paid
        setTimeout(() => fetchData(pagination.value?.current_page || 1), 5000);
    } catch (error) {
        toast.error.action(error as Error);
    }
};

const handleDownload = () => {
    toast.info(t('isp.member.invoices.downloading'), t('isp.member.invoices.download_desc'));
};

const handlePageChange = (page: number) => fetchData(page);

onMounted(() => fetchData());
</script>
