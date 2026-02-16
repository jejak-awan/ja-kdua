<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.partner.title', 'Partner Management') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.partner.subtitle', 'Manage resellers and billers with balance tracking') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.partner.add', 'Add Partner') }}
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-4 md:grid-cols-4">
            <Card v-for="stat in summaryStats" :key="stat.label" class="border-border/40">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground">{{ stat.label }}</p>
                            <p class="text-2xl font-bold">{{ stat.value }}</p>
                        </div>
                        <div :class="['p-2 rounded', stat.bgClass]">
                            <component :is="stat.icon" class="w-4 h-4" :class="stat.iconClass" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filter & Search -->
        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input 
                            v-model="search"
                            type="text" 
                            class="pl-10"
                            :placeholder="$t('common.placeholders.search', 'Search name or phone...')"
                        />
                    </div>
                    <Select v-model="categoryFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('common.labels.all', 'All Category') }}</SelectItem>
                            <SelectItem value="reseller">Reseller</SelectItem>
                            <SelectItem value="biller">Biller</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.admin.partner.empty', 'No partner found.')"
                />
            </CardContent>
        </Card>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="showFormModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ editingPartner ? $t('isp.admin.partner.edit', 'Edit Partner') : $t('isp.admin.partner.add', 'Add Partner') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.name', 'Name') }} *</Label>
                        <Input v-model="form.name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.category', 'Category') }}</Label>
                            <Select v-model="form.category">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="reseller">Reseller</SelectItem>
                                    <SelectItem value="biller">Biller</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.phone', 'Phone') }}</Label>
                            <Input v-model="form.phone" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.address', 'Address') }}</Label>
                        <Input v-model="form.address" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.partner.limit_hutang', 'Debt Limit') }}</Label>
                            <Input v-model="form.limit_hutang" type="number" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.partner.commission', 'Commission (%)') }}</Label>
                            <Input v-model="form.commission_rate" type="number" min="0" max="100" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showFormModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="savePartner" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.save', 'Save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Balance Modal (Credit/Debit) -->
        <Dialog v-model:open="showSaldoModal">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>{{ saldoMode === 'credit' ? $t('isp.admin.partner.add_credit', 'Add Credit') : $t('isp.admin.partner.add_debit', 'Add Debit') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div v-if="selectedPartner" class="p-3 bg-muted/50 rounded-lg">
                        <p class="font-medium">{{ selectedPartner.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.admin.partner.current_saldo', 'Current Balance') }}: <span class="font-mono font-bold">Rp{{ formatCurrency(selectedPartner.saldo || 0) }}</span></p>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.amount', 'Amount') }} *</Label>
                        <Input v-model="saldoForm.amount" type="number" min="0" />
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.category', 'Category') }} *</Label>
                        <Select v-model="saldoForm.category">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="topup">Top Up</SelectItem>
                                <SelectItem value="voucher_purchase">Voucher Purchase</SelectItem>
                                <SelectItem value="invoice_payment">Invoice Payment</SelectItem>
                                <SelectItem value="withdrawal">Withdrawal</SelectItem>
                                <SelectItem value="adjustment">Adjustment</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.description', 'Description') }}</Label>
                        <Input v-model="saldoForm.description" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showSaldoModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="processSaldo" :disabled="submitting" :variant="saldoMode === 'debit' ? 'destructive' : 'default'">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saldoMode === 'credit' ? $t('isp.admin.partner.credit', 'Credit') : $t('isp.admin.partner.debit', 'Debit') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import type { Partner } from '@/types/isp';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge, Input, Label, DataTable,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Ellipsis from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Wallet from 'lucide-vue-next/dist/esm/icons/wallet.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import ArrowUpCircle from 'lucide-vue-next/dist/esm/icons/circle-arrow-up.js';
import ArrowDownCircle from 'lucide-vue-next/dist/esm/icons/circle-arrow-down.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const submitting = ref(false);
const sorting = ref<SortingState>([]);

const partners = ref<Partner[]>([]);
const search = ref('');
const categoryFilter = ref('all');
const showFormModal = ref(false);
const showSaldoModal = ref(false);
const editingPartner = ref<Partner | null>(null);
const selectedPartner = ref<Partner | null>(null);
const saldoMode = ref<'credit' | 'debit'>('credit');

const form = ref({
    name: '',
    category: 'reseller',
    phone: '',
    address: '',
    limit_hutang: 0,
    commission_rate: 0
});

const saldoForm = ref({
    amount: 0,
    category: 'topup',
    description: ''
});

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const summaryStats = computed(() => [
    { label: t('isp.admin.partner.total', 'Total Partner'), value: partners.value.length, icon: Users, bgClass: 'bg-primary/10', iconClass: 'text-primary' },
    { label: t('isp.admin.partner.resellers', 'Resellers'), value: partners.value.filter(m => m.category === 'reseller').length, icon: TrendingUp, bgClass: 'bg-green-500/10', iconClass: 'text-green-500' },
    { label: t('isp.admin.partner.billers', 'Billers'), value: partners.value.filter(m => m.category === 'biller').length, icon: Wallet, bgClass: 'bg-blue-500/10', iconClass: 'text-blue-500' },
    { label: t('isp.admin.partner.total_saldo', 'Total Balance'), value: `Rp${formatCurrency(partners.value.reduce((s, m) => s + (m.saldo || 0), 0))}`, icon: Wallet, bgClass: 'bg-amber-500/10', iconClass: 'text-amber-500' }
]);

const getCategoryVariant = (category: string): 'default' | 'secondary' => {
    return category === 'reseller' ? 'default' : 'secondary';
};

const columnHelper = createColumnHelper<Partner>();

const columns = [
    columnHelper.accessor('name', {
        header: t('common.labels.name', 'Name'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [
                h(Users, { class: 'w-3.5 h-3.5' })
            ]),
            h('span', { class: 'font-medium' }, row.original.name)
        ])
    }),
    columnHelper.accessor('category', {
        header: t('common.labels.category', 'Category'),
        cell: ({ row }) => h(Badge, { variant: getCategoryVariant(row.original.category) }, () => row.original.category.toUpperCase())
    }),
    columnHelper.accessor('phone', {
        header: t('common.labels.phone', 'Phone'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.phone || '-')
    }),
    columnHelper.display({
        id: 'leads',
        header: t('isp.admin.partner.leads', 'Leads (Act/Total)'),
        cell: ({ row }) => {
            const stats = (row.original as any).statistics;
            if (!stats) return '-';
            return h('div', { class: 'flex items-center gap-1.5' }, [
                h('span', { class: 'font-bold text-green-600' }, stats.active_customers),
                h('span', { class: 'text-muted-foreground' }, '/'),
                h('span', { class: 'text-muted-foreground' }, stats.total_leads)
            ]);
        }
    }),
    columnHelper.accessor('saldo', {
        header: () => h('div', { class: 'text-right' }, t('isp.admin.partner.saldo', 'Balance')),
        cell: ({ row }) => h('div', { 
            class: ['text-right font-mono text-sm font-medium', (row.original.saldo || 0) >= 0 ? 'text-green-600' : 'text-red-600'] 
        }, `Rp${formatCurrency(row.original.saldo || 0)}`)
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.labels.actions', 'Actions')),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-1' }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () => 
                        h(Button, { variant: 'ghost', size: 'icon', class: 'h-8 w-8' }, () => h(Ellipsis, { class: 'w-4 h-4' }))
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => openCreditModal(row.original) }, () => [
                            h(ArrowUpCircle, { class: 'w-4 h-4 mr-2 text-green-500' }),
                            t('isp.admin.partner.add_credit', 'Add Credit')
                        ]),
                        h(DropdownMenuItem, { onClick: () => openDebitModal(row.original) }, () => [
                            h(ArrowDownCircle, { class: 'w-4 h-4 mr-2 text-red-500' }),
                            t('isp.admin.partner.add_debit', 'Add Debit')
                        ]),
                        h(DropdownMenuItem, { onClick: () => openEditModal(row.original) }, () => [
                            h(Pencil, { class: 'w-4 h-4 mr-2' }),
                            t('common.actions.edit', 'Edit')
                        ]),
                        h(DropdownMenuItem, { onClick: () => deletePartner(row.original.id), class: 'text-destructive' }, () => [
                            h(Trash2, { class: 'w-4 h-4 mr-2' }),
                            t('common.actions.delete', 'Delete')
                        ])
                    ])
                ]
            })
        ])
    })
];

const filteredPartners = computed(() => {
    let list = partners.value;
    if (categoryFilter.value !== 'all') {
        list = list.filter(m => m.category === categoryFilter.value);
    }
    if (search.value) {
        const s = search.value.toLowerCase();
        list = list.filter(m => m.name.toLowerCase().includes(s) || (m.phone && m.phone.includes(s)));
    }
    return list;
});

const table = useVueTable({
    get data() { return filteredPartners.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/isp/partners');
        partners.value = response.data.data || [];
    } catch (error) {
        console.error('Partner fetch failed', error);
        toast.error.action(t('common.errors.fetch', 'Failed to fetch data'));
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    editingPartner.value = null;
    form.value = { name: '', category: 'reseller', phone: '', address: '', limit_hutang: 0, commission_rate: 0 };
    showFormModal.value = true;
};

const openEditModal = (partner: Partner) => {
    editingPartner.value = partner;
    form.value = {
        name: partner.name,
        category: partner.category,
        phone: partner.phone || '',
        address: partner.address || '',
        limit_hutang: partner.limit_hutang || 0,
        commission_rate: partner.commission_rate || 0
    };
    showFormModal.value = true;
};

const openCreditModal = (partner: Partner) => {
    selectedPartner.value = partner;
    saldoMode.value = 'credit';
    saldoForm.value = { amount: 0, category: 'topup', description: '' };
    showSaldoModal.value = true;
};

const openDebitModal = (partner: Partner) => {
    selectedPartner.value = partner;
    saldoMode.value = 'debit';
    saldoForm.value = { amount: 0, category: 'voucher_purchase', description: '' };
    showSaldoModal.value = true;
};

const savePartner = async () => {
    if (!form.value.name) {
        toast.error.action(t('common.errors.required', 'Name is required'));
        return;
    }
    
    submitting.value = true;
    try {
        if (editingPartner.value) {
            await api.put(`/isp/partners/${editingPartner.value.id}`, form.value);
            toast.success.action(t('isp.admin.partner.updated', 'Partner updated'));
        } else {
            await api.post('/isp/partners', form.value);
            toast.success.action(t('isp.admin.partner.created', 'Partner created'));
        }
        showFormModal.value = false;
        await fetchData();
    } catch (error) {
        console.error('Save failed', error);
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const processSaldo = async () => {
    if (!selectedPartner.value || saldoForm.value.amount <= 0) {
        toast.error.action(t('common.errors.invalid_amount', 'Please enter a valid amount'));
        return;
    }
    
    submitting.value = true;
    try {
        const endpoint = saldoMode.value === 'credit' ? 'credit' : 'debit';
        await api.post(`/isp/partners/${selectedPartner.value.id}/${endpoint}`, saldoForm.value);
        toast.success.action(saldoMode.value === 'credit' 
            ? t('isp.admin.partner.credit_success', 'Credit added') 
            : t('isp.admin.partner.debit_success', 'Debit processed')
        );
        showSaldoModal.value = false;
        await fetchData();
    } catch (error) {
        console.error('Balance operation failed', error);
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const deletePartner = async (id: number) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('isp.admin.partner.confirm_delete', 'Are you sure you want to delete this partner?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    
    try {
        await api.delete(`/isp/partners/${id}`);
        toast.success.action(t('isp.admin.partner.deleted', 'Partner deleted'));
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
        toast.error.action(error);
    }
};

onMounted(fetchData);
</script>
