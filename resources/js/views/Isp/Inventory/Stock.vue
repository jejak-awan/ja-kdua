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
                        <input 
                            v-model="search"
                            type="text" 
                            class="w-full bg-muted/50 border-border/50 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all"
                            :placeholder="$t('common.placeholders.search', 'Search inventory...')"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/30">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.name', 'Item Name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.category', 'Category') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('isp.admin.inventory.stock', 'Stock') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('isp.admin.inventory.unit_price', 'Price') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('common.labels.actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-muted/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium">{{ item.name }}</p>
                                        <p class="text-[10px] text-muted-foreground font-mono">SKU: {{ item.sku || 'N/A' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="outline" class="text-[10px]">{{ item.category }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span :class="['font-bold', item.stock <= item.min_stock ? 'text-destructive' : '']">
                                            {{ item.stock }} {{ item.unit }}
                                        </span>
                                        <span v-if="item.stock <= item.min_stock" class="text-[9px] text-destructive uppercase font-bold tracking-tighter">Low Stock</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs">
                                    Rp{{ formatCurrency(item.unit_price) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" class="h-8 text-xs" @click="adjustStock(item)">
                                            <ArrowLeftRight class="w-3.5 h-3.5 mr-1" />
                                            {{ $t('common.actions.adjust', 'Adjust') }}
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteItem(item.id)">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Card, CardHeader, CardContent, 
    Button, Badge 
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
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const items = ref<any[]>([]);
const search = ref('');
const showAddModal = ref(false);

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

const filteredItems = computed(() => {
    if (!search.value) return items.value;
    const s = search.value.toLowerCase();
    return items.value.filter(i => 
        i.name.toLowerCase().includes(s) || 
        i.sku?.toLowerCase().includes(s)
    );
});

const inventoryStats = computed(() => [
    { label: 'Total Items', value: items.value.length, icon: Package, color: 'bg-blue-500/10 text-blue-500' },
    { label: 'Low Stock', value: items.value.filter(i => i.stock <= i.min_stock).length, icon: TriangleAlert, color: 'bg-destructive/10 text-destructive' },
    { label: 'Total Value', value: 'Rp' + formatCurrency(items.value.reduce((acc, i) => acc + (i.stock * i.unit_price), 0)), icon: ShoppingCart, color: 'bg-green-500/10 text-green-500' },
    { label: 'Recent Moves', value: '12', icon: Activity, color: 'bg-purple-500/10 text-purple-500' },
]);

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const adjustStock = (item: any) => {
    console.warn('Adjust stock', item.id);
};

const deleteItem = async (id: number) => {
    if (!confirm(t('common.messages.confirm_delete', 'Are you sure?'))) return;
    try {
        await api.delete(`/admin/ja/isp/inventory/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

onMounted(fetchData);
</script>
