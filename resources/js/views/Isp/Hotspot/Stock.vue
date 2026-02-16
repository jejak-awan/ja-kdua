<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.admin.inventory.title', 'Stock Manager') }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('isp.admin.inventory.subtitle', 'Track hardware inventory and warehouse transactions') }}</p>
            </div>
            <Button @click="showAddModal = true" class="rounded-xl">
                <Plus class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.inventory.add_item', 'Add New Item') }}
            </Button>
        </div>

        <!-- Inventory Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card v-for="stat in inventoryStats" :key="stat.label" class="border-border/40 shadow-sm rounded-xl overflow-hidden bg-card">
                <CardContent class="p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-muted-foreground opacity-60 mb-1">{{ stat.label }}</p>
                        <h3 class="text-xl font-bold font-mono tracking-tight">{{ stat.value }}</h3>
                    </div>
                    <div :class="['p-2 rounded-xl bg-opacity-10', stat.color]">
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
                            class="pl-10 h-9 rounded-xl"
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

        <!-- Add Item Modal -->
        <Dialog v-model:open="showAddModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ $t('isp.admin.inventory.modals.add_title') }}</DialogTitle>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ $t('isp.admin.inventory.fields.name') }}</Label>
                        <Input v-model="addForm.name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.sku') }}</Label>
                            <Input v-model="addForm.sku" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.category') }}</Label>
                            <Select v-model="addForm.category">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.unit') }}</Label>
                            <Input v-model="addForm.unit" placeholder="pcs, m, etc" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.price') }}</Label>
                            <Input v-model="addForm.unit_price" type="number" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.stock') }}</Label>
                            <Input v-model="addForm.stock" type="number" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.inventory.fields.min_stock') }}</Label>
                            <Input v-model="addForm.min_stock" type="number" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showAddModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button :disabled="submitting" @click="handleCreate">{{ $t('common.actions.save') }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Adjust Stock Modal -->
        <Dialog v-model:open="showAdjustModal">
            <!-- ... -->
        </Dialog>

        <!-- QR Code Modal -->
        <Dialog v-model:open="showQrModal">
            <DialogContent class="sm:max-w-[400px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>Hardware QR Track</DialogTitle>
                    <DialogDescription>
                        SKU: {{ selectedItem?.sku || 'N/A' }}
                    </DialogDescription>
                </DialogHeader>
                
                <div class="flex flex-col items-center justify-center p-8 space-y-4 bg-muted/30 rounded-2xl border border-dashed border-border/60">
                    <div class="bg-popover p-4 rounded-xl shadow-inner border border-border/20">
                        <img 
                            v-if="qrData"
                            :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${qrData.qr_data}`" 
                            class="w-48 h-48"
                            alt="Inventory QR"
                        />
                        <div v-else class="w-48 h-48 flex items-center justify-center">
                            <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
                        </div>
                    </div>
                    <p class="text-xs font-mono text-center text-muted-foreground bg-muted/50 px-3 py-1 rounded-full border border-border/40">
                        {{ qrData?.qr_data }}
                    </p>
                </div>

                <DialogFooter class="flex sm:justify-center gap-2">
                    <Button variant="outline" @click="printQr" class="rounded-xl flex-1">
                        <Printer class="w-4 h-4 mr-2" />
                        Print Label
                    </Button>
                    <Button variant="secondary" @click="showQrModal = false" class="rounded-xl flex-1">
                        Close
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
import { useConfirm } from '@/composables/useConfirm';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge, Input, DataTable,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Textarea
} from '@/components/ui';
import { useToast } from '@/composables/useToast';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ArrowLeftRight from 'lucide-vue-next/dist/esm/icons/arrow-left-right.js';
import Package from 'lucide-vue-next/dist/esm/icons/package.js';
import TriangleAlert from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import ShoppingCart from 'lucide-vue-next/dist/esm/icons/shopping-cart.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import QrCode from 'lucide-vue-next/dist/esm/icons/qr-code.js';
import Printer from 'lucide-vue-next/dist/esm/icons/printer.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

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
const showAdjustModal = ref(false);
const showQrModal = ref(false);
const qrData = ref<any>(null);
const selectedItem = ref<InventoryItem | null>(null);
const submitting = ref(false);
const toast = useToast();

const categories = ['ONU', 'Router', 'Cable', 'SFP', 'Splitter', 'Other'];

const addForm = ref({
    name: '',
    sku: '',
    category: 'ONU',
    unit: 'pcs',
    stock: 0,
    min_stock: 5,
    unit_price: 0
});

const adjustForm = ref({
    type: 'In',
    quantity: 1,
    notes: '',
    customer_id: null
});

const filteredItems = computed(() => {
    if (!search.value) return items.value;
    const s = search.value.toLowerCase();
    return items.value.filter(i => 
        i.name.toLowerCase().includes(s) || 
        i.sku?.toLowerCase().includes(s)
    );
});

const adjustStock = (item: InventoryItem) => {
    selectedItem.value = item;
    adjustForm.value = {
        type: 'In',
        quantity: 1,
        notes: '',
        customer_id: null
    };
    showAdjustModal.value = true;
};

const viewQr = async (item: InventoryItem) => {
    selectedItem.value = item;
    qrData.value = null;
    showQrModal.value = true;
    try {
        const response = await api.get(`/admin/janet/isp/inventory/${item.id}/qr`);
        qrData.value = response.data.data;
    } catch (_e) {
        toast.error.default('Failed to generate QR');
    }
};

const printQr = () => {
    window.print();
};

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
                isLowStock ? h('span', { class: 'text-[9px] text-destructive font-bold tracking-tight' }, 'Low Stock') : null
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
                size: 'icon',
                onClick: () => viewQr(row.original),
                class: 'h-8 w-8 text-primary border-primary/20 hover:bg-primary/5'
            }, () => h(QrCode, { class: 'w-3.5 h-3.5' })),
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
        const response = await api.get('/admin/janet/isp/inventory');
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

const handleCreate = async () => {
    submitting.value = true;
    try {
        await api.post('/admin/janet/isp/inventory', addForm.value);
        toast.success.action(t('isp.admin.inventory.messages.success_create'));
        showAddModal.value = false;
        fetchData();
        // Reset form
        addForm.value = {
            name: '',
            sku: '',
            category: 'ONU',
            unit: 'pcs',
            stock: 0,
            min_stock: 5,
            unit_price: 0
        };
    } catch (error) {
        console.error('Create failed', error);
    } finally {
        submitting.value = false;
    }
};

const handleAdjust = async () => {
    if (!selectedItem.value) return;
    submitting.value = true;
    try {
        await api.post(`/admin/janet/isp/inventory/${selectedItem.value.id}/adjust`, adjustForm.value);
        toast.success.action(t('isp.admin.inventory.messages.success_adjust'));
        showAdjustModal.value = false;
        fetchData();
    } catch (error) {
        console.error('Adjustment failed', error);
    } finally {
        submitting.value = false;
    }
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
        await api.delete(`/admin/janet/isp/inventory/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

onMounted(fetchData);
</script>
