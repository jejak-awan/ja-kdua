<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.inventory.title', 'Stock Manager') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.inventory.subtitle', 'Track hardware inventory and warehouse transactions') }}</p>
            </div>
            <Button @click="showAddModal = true">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.inventory.add_item', 'Add New Item') }}
            </Button>
        </div>

        <!-- Inventory Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card v-for="stat in inventoryStats" :key="stat.label" class="border-border/40 shadow-sm">
                <CardContent class="p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-medium text-muted-foreground uppercase">{{ stat.label }}</p>
                        <h3 class="text-xl font-bold">{{ stat.value }}</h3>
                    </div>
                    <div :class="['p-2 rounded-lg', stat.color]">
                        <component :is="stat.icon" class="w-4 h-4" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input 
                            v-model="search"
                            type="text" 
                            class="pl-10"
                            :placeholder="$t('common.placeholders.search', 'Search inventory...')"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.admin.inventory.empty', 'No inventory items found.')"
                />
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useConfirm } from '@/composables/useConfirm';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge, Input, DataTable
} from '@/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ArrowLeftRight from 'lucide-vue-next/dist/esm/icons/arrow-left-right.js';
import Package from 'lucide-vue-next/dist/esm/icons/package.js';
import TriangleAlert from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import ShoppingCart from 'lucide-vue-next/dist/esm/icons/shopping-cart.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';

const { t } = useI18n();
const { confirm } = useConfirm();
const loading = ref(true);
const sorting = ref<SortingState>([]);

interface InventoryItem {
    id: number;
    name: string;
    sku: string | null;
    category: string;
    stock: number;
    min_stock: number;
    unit: string;
    unit_price: number;
}

const items = ref<InventoryItem[]>([]);
const search = ref('');
const showAddModal = ref(false);

const columnHelper = createColumnHelper<InventoryItem>();

const columns = [
    columnHelper.accessor('name', {
        header: t('common.labels.name', 'Item Name'),
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium' }, row.original.name),
            h('p', { class: 'text-[10px] text-muted-foreground font-mono' }, `SKU: ${row.original.sku || 'N/A'}`)
        ])
    }),
    columnHelper.accessor('category', {
        header: t('common.labels.category', 'Category'),
        cell: ({ row }) => h(Badge, { variant: 'outline', class: 'text-[10px]' }, () => row.original.category)
    }),
    columnHelper.accessor('stock', {
        header: () => h('div', { class: 'text-right' }, t('isp.admin.inventory.stock', 'Stock')),
        cell: ({ row }) => {
            const item = row.original;
            const isLowStock = item.stock <= item.min_stock;
            return h('div', { class: 'flex flex-col items-end' }, [
                h('span', { class: `font-bold ${isLowStock ? 'text-destructive' : ''}` }, `${item.stock} ${item.unit}`),
                isLowStock ? h('span', { class: 'text-[9px] text-destructive uppercase font-bold tracking-tighter' }, 'Low Stock') : null
            ]);
        }
    }),
    columnHelper.accessor('unit_price', {
        header: () => h('div', { class: 'text-right' }, t('isp.admin.inventory.unit_price', 'Price')),
        cell: ({ row }) => h('div', { class: 'text-right font-mono text-xs' }, `Rp${formatCurrency(row.original.unit_price)}`)
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.labels.actions', 'Actions')),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-end gap-2' }, [
            h(Button, {
                variant: 'outline',
                size: 'sm',
                onClick: () => adjustStock(row.original),
                class: 'h-8 text-xs'
            }, () => [
                h(ArrowLeftRight, { class: 'w-3.5 h-3.5 mr-1' }),
                t('common.actions.adjust', 'Adjust')
            ]),
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => deleteItem(row.original.id),
                class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10'
            }, () => h(Trash2, { class: 'w-3.5 h-3.5' }))
        ])
    })
];

const filteredItems = computed(() => {
    if (!search.value) return items.value;
    const s = search.value.toLowerCase();
    return items.value.filter(i => 
        i.name.toLowerCase().includes(s) || 
        i.sku?.toLowerCase().includes(s)
    );
});

const table = useVueTable({
    get data() { return filteredItems.value },
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
        const response = await api.get('/admin/ja/isp/inventory');
        items.value = response.data.data;
    } catch (error) {
        console.error('Inventory fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const inventoryStats = computed(() => [
    { label: 'Total Items', value: items.value.length, icon: Package, color: 'bg-blue-500/10 text-blue-500' },
    { label: 'Low Stock', value: items.value.filter(i => i.stock <= i.min_stock).length, icon: TriangleAlert, color: 'bg-destructive/10 text-destructive' },
    { label: 'Total Value', value: 'Rp' + formatCurrency(items.value.reduce((acc, i) => acc + (i.stock * i.unit_price), 0)), icon: ShoppingCart, color: 'bg-green-500/10 text-green-500' },
    { label: 'Recent Moves', value: '12', icon: Activity, color: 'bg-purple-500/10 text-purple-500' },
]);

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const adjustStock = (item: InventoryItem) => {
    console.warn('Adjust stock', item.id);
    // TODO: Open stock adjustment modal
};

const deleteItem = async (id: number) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('common.messages.confirm_delete', 'Are you sure?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    
    try {
        await api.delete(`/admin/ja/isp/inventory/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

onMounted(fetchData);
</script>
