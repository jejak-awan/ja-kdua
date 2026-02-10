<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.coupons.title', 'Discount Coupons') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.coupons.subtitle', 'Manage discount coupons for customer invoices') }}</p>
            </div>
            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.coupons.add', 'Create Coupon') }}
            </Button>
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

        <!-- Table -->
        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="search" type="text" class="pl-10" :placeholder="$t('common.placeholders.search', 'Search code or name...')" />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('common.labels.all', 'All') }}</SelectItem>
                            <SelectItem value="active">{{ $t('common.labels.active', 'Active') }}</SelectItem>
                            <SelectItem value="inactive">{{ $t('common.labels.inactive', 'Inactive') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable :table="table" :loading="loading" :empty-message="t('isp.admin.coupons.empty', 'No coupons found.')" />
            </CardContent>
        </Card>

        <!-- Create/Edit Modal -->
        <Dialog v-model:open="showFormModal">
            <DialogContent class="sm:max-w-[550px]">
                <DialogHeader>
                    <DialogTitle>{{ editingCoupon ? $t('isp.admin.coupons.edit', 'Edit Coupon') : $t('isp.admin.coupons.add', 'Create Coupon') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.code', 'Code') }} *</Label>
                            <Input v-model="form.code" :disabled="!!editingCoupon" placeholder="DISKON20" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.name', 'Name') }} *</Label>
                            <Input v-model="form.name" placeholder="Diskon Tahun Baru" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.description', 'Description') }}</Label>
                        <Input v-model="form.description" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.discount_type', 'Discount Type') }}</Label>
                            <Select v-model="form.discount_type">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="percentage">Percentage (%)</SelectItem>
                                    <SelectItem value="fixed">Fixed (Rp)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.discount_value', 'Discount Value') }} *</Label>
                            <Input v-model="form.discount_value" type="number" min="0" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.min_transaction', 'Min Transaction') }}</Label>
                            <Input v-model="form.min_transaction" type="number" min="0" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.max_discount', 'Max Discount') }}</Label>
                            <Input v-model="form.max_discount" type="number" min="0" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.max_usage', 'Max Usage') }}</Label>
                            <Input v-model="form.max_usage" type="number" min="1" placeholder="Unlimited" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.max_per_customer', 'Max / Customer') }}</Label>
                            <Input v-model="form.max_per_customer" type="number" min="1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.valid_from', 'Valid From') }}</Label>
                            <Input v-model="form.valid_from" type="datetime-local" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.coupons.valid_until', 'Valid Until') }}</Label>
                            <Input v-model="form.valid_until" type="datetime-local" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showFormModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="saveCoupon" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.save', 'Save') }}
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
import Ticket from 'lucide-vue-next/dist/esm/icons/ticket.js';
import Percent from 'lucide-vue-next/dist/esm/icons/percent.js';
import CircleCheck from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import CircleX from 'lucide-vue-next/dist/esm/icons/circle-x.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const submitting = ref(false);
const sorting = ref<SortingState>([]);

interface Coupon {
    id: number;
    code: string;
    name: string;
    description: string | null;
    discount_type: 'percentage' | 'fixed';
    discount_value: number;
    min_transaction: number;
    max_discount: number | null;
    max_usage: number | null;
    used_count: number;
    max_per_customer: number;
    valid_from: string | null;
    valid_until: string | null;
    is_active: boolean;
}

const coupons = ref<Coupon[]>([]);
const search = ref('');
const statusFilter = ref('all');
const showFormModal = ref(false);
const editingCoupon = ref<Coupon | null>(null);

const form = ref({
    code: '',
    name: '',
    description: '',
    discount_type: 'percentage',
    discount_value: 0,
    min_transaction: 0,
    max_discount: undefined as number | undefined,
    max_usage: undefined as number | undefined,
    max_per_customer: 1,
    valid_from: '',
    valid_until: ''
});

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const summaryStats = computed(() => [
    { label: t('isp.admin.coupons.total', 'Total Coupons'), value: coupons.value.length, icon: Ticket, bgClass: 'bg-primary/10', iconClass: 'text-primary' },
    { label: t('isp.admin.coupons.active_count', 'Active'), value: coupons.value.filter(c => c.is_active).length, icon: CircleCheck, bgClass: 'bg-green-500/10', iconClass: 'text-green-500' },
    { label: t('isp.admin.coupons.expired', 'Expired/Inactive'), value: coupons.value.filter(c => !c.is_active).length, icon: CircleX, bgClass: 'bg-red-500/10', iconClass: 'text-red-500' },
    { label: t('isp.admin.coupons.total_used', 'Total Used'), value: coupons.value.reduce((s, c) => s + c.used_count, 0), icon: Percent, bgClass: 'bg-amber-500/10', iconClass: 'text-amber-500' }
]);

const columnHelper = createColumnHelper<Coupon>();

const columns = [
    columnHelper.accessor('code', {
        header: t('isp.admin.coupons.code', 'Code'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [
                h(Ticket, { class: 'w-3.5 h-3.5' })
            ]),
            h('span', { class: 'font-mono font-semibold text-sm' }, row.original.code)
        ])
    }),
    columnHelper.accessor('name', {
        header: t('common.labels.name', 'Name'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.name)
    }),
    columnHelper.accessor('discount_type', {
        header: t('isp.admin.coupons.discount', 'Discount'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.discount_type === 'percentage' ? 'default' : 'secondary'
        }, () => row.original.discount_type === 'percentage'
            ? `${row.original.discount_value}%`
            : `Rp${formatCurrency(row.original.discount_value)}`)
    }),
    columnHelper.accessor('used_count', {
        header: t('isp.admin.coupons.usage', 'Usage'),
        cell: ({ row }) => h('span', { class: 'text-sm font-mono' },
            `${row.original.used_count}/${row.original.max_usage ?? '∞'}`)
    }),
    columnHelper.accessor('is_active', {
        header: t('common.labels.status', 'Status'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.is_active ? 'default' : 'destructive'
        }, () => row.original.is_active ? 'Active' : 'Inactive')
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
                        h(DropdownMenuItem, { onClick: () => openEditModal(row.original) }, () => [
                            h(Pencil, { class: 'w-4 h-4 mr-2' }),
                            t('common.actions.edit', 'Edit')
                        ]),
                        h(DropdownMenuItem, { onClick: () => toggleActive(row.original) }, () => [
                            h(row.original.is_active ? CircleX : CircleCheck, { class: 'w-4 h-4 mr-2' }),
                            row.original.is_active ? 'Deactivate' : 'Activate'
                        ]),
                        h(DropdownMenuItem, { onClick: () => deleteCoupon(row.original.id), class: 'text-destructive' }, () => [
                            h(Trash2, { class: 'w-4 h-4 mr-2' }),
                            t('common.actions.delete', 'Delete')
                        ])
                    ])
                ]
            })
        ])
    })
];

const filteredCoupons = computed(() => {
    let list = coupons.value;
    if (statusFilter.value === 'active') list = list.filter(c => c.is_active);
    else if (statusFilter.value === 'inactive') list = list.filter(c => !c.is_active);
    if (search.value) {
        const s = search.value.toLowerCase();
        list = list.filter(c => c.code.toLowerCase().includes(s) || c.name.toLowerCase().includes(s));
    }
    return list;
});

const table = useVueTable({
    get data() { return filteredCoupons.value },
    columns,
    state: { get sorting() { return sorting.value } },
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
        const response = await api.get('/admin/ja/isp/coupons');
        coupons.value = response.data.data || [];
    } catch (error) {
        console.error('Coupon fetch failed', error);
        toast.error.action(t('common.errors.fetch', 'Failed to fetch data'));
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    editingCoupon.value = null;
    form.value = { code: '', name: '', description: '', discount_type: 'percentage', discount_value: 0, min_transaction: 0, max_discount: undefined, max_usage: undefined, max_per_customer: 1, valid_from: '', valid_until: '' };
    showFormModal.value = true;
};

const openEditModal = (coupon: Coupon) => {
    editingCoupon.value = coupon;
    form.value = {
        code: coupon.code,
        name: coupon.name,
        description: coupon.description || '',
        discount_type: coupon.discount_type,
        discount_value: coupon.discount_value,
        min_transaction: coupon.min_transaction,
        max_discount: coupon.max_discount ?? undefined,
        max_usage: coupon.max_usage ?? undefined,
        max_per_customer: coupon.max_per_customer,
        valid_from: coupon.valid_from || '',
        valid_until: coupon.valid_until || ''
    };
    showFormModal.value = true;
};

const saveCoupon = async () => {
    if (!form.value.code || !form.value.name) {
        toast.error.action(t('common.errors.required', 'Code and name are required'));
        return;
    }
    submitting.value = true;
    try {
        if (editingCoupon.value) {
            await api.put(`/admin/ja/isp/coupons/${editingCoupon.value.id}`, form.value);
            toast.success.action(t('isp.admin.coupons.updated', 'Coupon updated'));
        } else {
            await api.post('/admin/ja/isp/coupons', form.value);
            toast.success.action(t('isp.admin.coupons.created', 'Coupon created'));
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

const toggleActive = async (coupon: Coupon) => {
    try {
        await api.put(`/admin/ja/isp/coupons/${coupon.id}`, { is_active: !coupon.is_active });
        toast.success.action(coupon.is_active ? 'Coupon deactivated' : 'Coupon activated');
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

const deleteCoupon = async (id: number) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('isp.admin.coupons.confirm_delete', 'Are you sure you want to delete this coupon?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/ja/isp/coupons/${id}`);
        toast.success.action(t('isp.admin.coupons.deleted', 'Coupon deleted'));
        await fetchData();
    } catch (error) {
        toast.error.action(error);
    }
};

onMounted(fetchData);
</script>
