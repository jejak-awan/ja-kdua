<script setup lang="ts">
import { ref, computed, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { 
    useVueTable, getCoreRowModel, getSortedRowModel, 
    createColumnHelper, type SortingState 
} from '@tanstack/vue-table';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge, Input, DataTable,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';

import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Ticket from 'lucide-vue-next/dist/esm/icons/ticket.js';
import Checkbox from '@/components/ui/Checkbox.vue';
import type { Voucher, Partner, IspPlan } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const vouchers = ref<Voucher[]>([]);
const partners = ref<Partner[]>([]);
const billingPlans = ref<IspPlan[]>([]);

const search = ref('');
const partnerFilter = ref('all');
const profileFilter = ref('all');
const dateRange = ref('today');
const rowSelection = ref({});
const sorting = ref<SortingState>([]);

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const getStatusVariant = (status: string): 'success' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'Available': return 'success';
        case 'Used': return 'secondary';
        case 'Sold': return 'outline';
        case 'Expired': return 'destructive';
        default: return 'outline';
    }
};

const columnHelper = createColumnHelper<Voucher>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
        }),
    }),
    columnHelper.accessor('code', {
        header: t('common.labels.code', 'Voucher Code'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h(Ticket, { class: 'w-4 h-4 text-primary' }),
            h('span', { class: 'font-mono font-bold' }, row.original.code)
        ])
    }),
    columnHelper.accessor('profile', {
        header: t('common.labels.profile', 'Profile'),
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.original.profile)
    }),
    columnHelper.accessor(v => v.partner?.name, {
        id: 'partner_name',
        header: t('common.labels.partner', 'Partner'),
        cell: ({ row }) => row.original.partner?.name || '-'
    }),
    columnHelper.accessor('price', {
        header: t('common.labels.price', 'Price'),
        cell: ({ row }) => `Rp${formatCurrency(row.original.price)}`
    }),
    columnHelper.accessor('status', {
        header: t('common.labels.status', 'Status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status) }, () => row.original.status)
    }),
    columnHelper.accessor('sold_at', {
        header: t('isp.admin.vouchers.sold_at', 'Sold Date'),
        cell: ({ row }) => row.original.sold_at || '-'
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.labels.actions', 'Actions')),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => refundVoucher(row.original.id),
                title: t('isp.admin.vouchers.refund', 'Refund')
            }, () => h(Undo2, { class: 'w-4 h-4 text-orange-500' })),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => resetCounter(row.original.id),
                title: t('isp.admin.vouchers.reset_counter', 'Reset Counter')
            }, () => h(RotateCcw, { class: 'w-4 h-4 text-blue-500' }))
        ])
    })
];

const filteredVouchers = computed(() => {
    let list = vouchers.value;
    if (partnerFilter.value !== 'all') {
        const pId = parseInt(partnerFilter.value);
        list = list.filter(v => v.partner_id === pId);
    }
    if (profileFilter.value !== 'all') {
        list = list.filter(v => (v.profile as IspPlan)?.name === profileFilter.value || (v.profile as IspPlan)?.mikrotik_profile === profileFilter.value);
    }
    if (search.value) {
        const s = search.value.toLowerCase();
        list = list.filter(v => v.code.toLowerCase().includes(s));
    }
    return list;
});

const selectedVoucherIds = computed(() => {
    return Object.keys(rowSelection.value).map(id => parseInt(id));
});

const table = useVueTable({
    get data() { return filteredVouchers.value },
    columns,
    state: {
        get sorting() { return sorting.value },
        get rowSelection() { return rowSelection.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    onRowSelectionChange: updaterOrValue => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
    enableRowSelection: true,
});

const fetchData = async () => {
    loading.value = true;
    try {
        const [vRes, mitraRes, pRes] = await Promise.all([
            api.get('/admin/ja/isp/vouchers', {
                params: {
                    status: 'Sold,Used',
                    ...getDateParams()
                }
            }),
            api.get('/api/core/isp/partners'),
            api.get('/admin/ja/isp/billing-plans')
        ]);
        vouchers.value = vRes.data.data.data;
        partners.value = mitraRes.data.data || [];
        billingPlans.value = pRes.data.data;
    } catch (error) {
        console.error('Fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const refundVoucher = async (id: number) => {
    const confirmed = await confirm({
        title: t('isp.admin.vouchers.refund', 'Refund Voucher'),
        message: t('isp.admin.vouchers.refund_confirm', 'Are you sure you want to refund this voucher? This will return saldo to Partner.'),
        variant: 'warning',
    });
    if (!confirmed) return;
    
    try {
        await api.post(`/admin/ja/isp/vouchers/${id}/refund`);
        toast.success.action(t('common.messages.success'));
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const resetCounter = async (id: number) => {
    const confirmed = await confirm({
        title: t('isp.admin.vouchers.reset_counter', 'Reset Counter'),
        message: t('isp.admin.vouchers.reset_confirm', 'Reset usage counter on RADIUS?'),
    });
    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/isp/vouchers/${id}/reset-counter`);
        toast.success.action(t('common.messages.success'));
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const bulkRefund = async () => {
    if (selectedVoucherIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('isp.admin.vouchers.bulk_refund', 'Bulk Refund'),
        message: t('isp.admin.vouchers.bulk_refund_confirm', 'Refund {count} vouchers? This will credit partner balances.').replace('{count}', String(selectedVoucherIds.value.length)),
        variant: 'warning',
    });
    
    if (!confirmed) return;
    
    try {
        await api.post('/admin/ja/isp/vouchers/bulk-refund', { ids: selectedVoucherIds.value });
        toast.success.action(t('common.messages.success'));
        rowSelection.value = {};
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const bulkResetCounter = async () => {
    if (selectedVoucherIds.value.length === 0) return;

    try {
        await api.post('/admin/ja/isp/vouchers/bulk-reset-counter', { ids: selectedVoucherIds.value });
        toast.success.action(t('common.messages.success'));
        rowSelection.value = {};
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const getDateParams = () => {
    const params: Record<string, string> = {};
    const now = new Date();
    
    if (dateRange.value === 'today') {
        params.start_date = now.toISOString().split('T')[0];
    } else if (dateRange.value === 'yesterday') {
        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);
        params.start_date = yesterday.toISOString().split('T')[0];
        params.end_date = yesterday.toISOString().split('T')[0];
    } else if (dateRange.value === 'this_month') {
        params.start_date = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    }
    
    return params;
};

const exportData = () => {
    const params = new URLSearchParams();
    params.set('status', 'Sold,Used');
    if (partnerFilter.value !== 'all') params.set('partner_id', partnerFilter.value);
    
    const dateParams = getDateParams();
    if (dateParams.start_date) params.set('start_date', dateParams.start_date);
    if (dateParams.end_date) params.set('end_date', dateParams.end_date);
    
    window.location.href = `/api/core/admin/ja/isp/vouchers/export?${params.toString()}`;
};

onMounted(fetchData);
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.vouchers.sold_title', 'Sold Voucher Analysis') }}</h1>
            <p class="text-muted-foreground">{{ $t('isp.admin.vouchers.sold_subtitle', 'Track and manage reseller voucher sales and performance') }}</p>
        </div>

        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input 
                            v-model="search"
                            type="text" 
                            class="pl-10 h-9"
                            :placeholder="$t('common.placeholders.search', 'Search code...')"
                        />
                    </div>
                    
                    <Select v-model="partnerFilter">
                        <SelectTrigger class="w-[180px] h-9">
                            <SelectValue :placeholder="$t('common.labels.all_partners', 'All Partners')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Partners</SelectItem>
                            <SelectItem v-for="p in partners" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="profileFilter">
                        <SelectTrigger class="w-[180px] h-9">
                            <SelectValue :placeholder="$t('common.labels.all_profiles', 'All Profiles')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Profiles</SelectItem>
                            <SelectItem v-for="p in billingPlans" :key="p.id" :value="p.mikrotik_profile || p.name">{{ p.name }}</SelectItem>
                        </SelectContent>
                    </Select>

                    <div class="ml-auto flex items-center gap-2">
                        <DropdownMenu v-if="selectedVoucherIds.length > 0">
                            <DropdownMenuTrigger as-child>
                                <Button variant="secondary" size="sm" class="h-9">
                                    {{ $t('common.labels.bulk_actions', 'Bulk Actions') }} ({{ selectedVoucherIds.length }})
                                    <ChevronDown class="w-4 h-4 ml-2" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="bulkRefund">
                                    <Undo2 class="w-4 h-4 mr-2 text-orange-500" />
                                    {{ $t('isp.admin.vouchers.refund', 'Bulk Refund') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="bulkResetCounter">
                                    <RotateCcw class="w-4 h-4 mr-2 text-blue-500" />
                                    {{ $t('isp.admin.vouchers.reset_counter', 'Reset Counters') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <Select v-model="dateRange" @update:model-value="fetchData">
                            <SelectTrigger class="w-[140px] h-9">
                                <Calendar class="w-4 h-4 mr-2" />
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Time</SelectItem>
                                <SelectItem value="today">Today</SelectItem>
                                <SelectItem value="yesterday">Yesterday</SelectItem>
                                <SelectItem value="this_month">This Month</SelectItem>
                            </SelectContent>
                        </Select>

                        <Button variant="outline" size="sm" class="h-9" @click="exportData">
                            <Download class="w-4 h-4 mr-2" />
                            {{ $t('common.actions.export', 'Export Report') }}
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.admin.vouchers.sold_empty', 'No sales data found.')"
                />
            </CardContent>
        </Card>
    </div>
</template>
