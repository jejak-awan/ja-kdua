<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.vouchers.title', 'Hotspot Voucher Engine') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.vouchers.subtitle', 'Bulk generate and manage hotspot access vouchers') }}</p>
            </div>
            <Button @click="showGenerateModal = true">
                <Ticket class="w-4 h-4 mr-2" />
                {{ $t('isp.admin.vouchers.generate', 'Generate Batch') }}
            </Button>
        </div>

        <!-- Filter & Search -->
        <Card class="border-border/40 shadow-sm">
            <CardHeader class="p-4 border-b border-border/40">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <input 
                            v-model="search"
                            type="text" 
                            class="w-full bg-muted/50 border-border/50 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 transition-all"
                            :placeholder="$t('common.placeholders.search', 'Search code...')"
                        />
                    </div>
                    <select v-model="statusFilter" class="bg-muted/50 border-border/50 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="all">All Status</option>
                        <option value="Available">Available</option>
                        <option value="Used">Used</option>
                        <option value="Expired">Expired</option>
                    </select>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/30">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.code', 'Voucher Code') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.profile', 'Profile') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('common.labels.price', 'Price') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.status', 'Status') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('common.labels.used_at', 'Used At') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('common.labels.actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="voucher in filteredVouchers" :key="voucher.id" class="hover:bg-muted/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded bg-primary/10 text-primary">
                                            <Ticket class="w-3.5 h-3.5" />
                                        </div>
                                        <span class="font-mono font-bold tracking-wider">{{ voucher.code }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="secondary" class="text-[10px]">{{ voucher.profile }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs">
                                    Rp{{ formatCurrency(voucher.price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :variant="getStatusVariant(voucher.status)">{{ voucher.status }}</Badge>
                                </td>
                                <td class="px-6 py-4 text-[10px] text-muted-foreground">
                                    {{ voucher.used_at || '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button v-if="voucher.status === 'Available'" variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteVoucher(voucher.id)">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="loading" class="p-12 flex flex-col items-center justify-center text-muted-foreground">
                    <LoaderCircle class="w-8 h-8 animate-spin mb-2" />
                    <p class="text-sm">Fetching vouchers...</p>
                </div>
                
                <div v-else-if="filteredVouchers.length === 0" class="p-12 text-center text-muted-foreground">
                    <p class="text-sm">No vouchers matching your search.</p>
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
import Ticket from 'lucide-vue-next/dist/esm/icons/ticket.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const vouchers = ref<any[]>([]);
const search = ref('');
const statusFilter = ref('all');
const showGenerateModal = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/vouchers');
        vouchers.value = response.data.data.data; // Paginated
    } catch (error) {
        console.error('Voucher fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const filteredVouchers = computed(() => {
    let list = vouchers.value;
    if (statusFilter.value !== 'all') {
        list = list.filter(v => v.status === statusFilter.value);
    }
    if (search.value) {
        const s = search.value.toLowerCase();
        list = list.filter(v => v.code.toLowerCase().includes(s));
    }
    return list;
});

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const getStatusVariant = (status: string): any => {
    switch (status) {
        case 'Available': return 'success';
        case 'Used': return 'secondary';
        case 'Expired': return 'destructive';
        default: return 'outline';
    }
};

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const deleteVoucher = async (id: number) => {
    if (!confirm(t('common.messages.confirm_delete', 'Are you sure?'))) return;
    try {
        await api.delete(`/admin/ja/isp/vouchers/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

onMounted(fetchData);
</script>
