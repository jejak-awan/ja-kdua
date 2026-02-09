<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.vouchers.title', 'Hotspot Voucher Engine') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.vouchers.subtitle', 'Bulk generate and manage hotspot access vouchers') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Download class="w-4 h-4 mr-2" />
                            {{ $t('common.actions.export', 'Export') }}
                            <ChevronDown class="w-4 h-4 ml-2" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="exportScript">
                            <FileCode class="w-4 h-4 mr-2" />
                            {{ $t('isp.admin.vouchers.export_script', 'RouterOS Script (.rsc)') }}
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="exportCsv">
                            <FileSpreadsheet class="w-4 h-4 mr-2" />
                            {{ $t('isp.admin.vouchers.export_csv', 'CSV File (.csv)') }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button variant="outline" @click="printCurrentSelection" :disabled="filteredVouchers.length === 0">
                    <Printer class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.vouchers.print_batch', 'Print Batch') }}
                </Button>
                <Button @click="showGenerateModal = true">
                    <Ticket class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.vouchers.generate', 'Generate Batch') }}
                </Button>
            </div>
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
                            :placeholder="$t('common.placeholders.search', 'Search code...')"
                        />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="Available">Available</SelectItem>
                            <SelectItem value="Used">Used</SelectItem>
                            <SelectItem value="Expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.admin.vouchers.empty', 'No vouchers matching your search.')"
                />
            </CardContent>
        </Card>

        <!-- Generate Batch Modal -->
        <Dialog v-model:open="showGenerateModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ $t('isp.admin.vouchers.generate_title', 'Generate New Batch') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.count', 'Quantity') }}</Label>
                            <Input v-model="form.count" type="number" min="1" max="1000" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.prefix', 'Code Prefix') }}</Label>
                            <Input v-model="form.prefix" placeholder="e.g. VIP-" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.plan', 'Billing Plan (Profile)') }}</Label>
                        <Select v-model="form.profile">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="plan in billingPlans" :key="plan.id" :value="plan.mikrotik_profile || plan.name">
                                    {{ plan.name }} ({{ plan.mikrotik_profile }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.pattern', 'Code Pattern') }}</Label>
                            <Select v-model="form.pattern">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mixed">Mixed (aB1c2D)</SelectItem>
                                    <SelectItem value="numbers">Numbers Only (123456)</SelectItem>
                                    <SelectItem value="lowercase">Lowercase (abcdef)</SelectItem>
                                    <SelectItem value="uppercase">Uppercase (ABCDEF)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.duration', 'Validity Duration') }}</Label>
                            <Input v-model="form.duration" placeholder="e.g. 1h, 1d, 3600" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.price', 'Sell Price') }}</Label>
                        <Input v-model="form.price" type="number" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showGenerateModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="generateVouchers" :disabled="submitting">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.generate', 'Generate') }}
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

import Ticket from 'lucide-vue-next/dist/esm/icons/ticket.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Printer from 'lucide-vue-next/dist/esm/icons/printer.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import FileSpreadsheet from 'lucide-vue-next/dist/esm/icons/file-spreadsheet.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const submitting = ref(false);
const sorting = ref<SortingState>([]);

interface BillingPlan {
    id: number;
    name: string;
    mikrotik_profile: string | null;
    price: number;
}

interface Voucher {
    id: number;
    code: string;
    profile: string;
    price: number;
    status: string;
    used_at: string | null;
    batch_id?: string;
}

const vouchers = ref<Voucher[]>([]);
const billingPlans = ref<BillingPlan[]>([]);
const search = ref('');
const statusFilter = ref('all');
const showGenerateModal = ref(false);

const form = ref({
    count: 10,
    prefix: '',
    profile: '',
    price: 0,
    pattern: 'mixed',
    duration: '1h'
});

const getStatusVariant = (status: string): 'success' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'Available': return 'success';
        case 'Used': return 'secondary';
        case 'Expired': return 'destructive';
        default: return 'outline';
    }
};

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const columnHelper = createColumnHelper<Voucher>();

const columns = [
    columnHelper.accessor('code', {
        header: t('common.labels.code', 'Voucher Code'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'p-1.5 rounded bg-primary/10 text-primary' }, [
                h(Ticket, { class: 'w-3.5 h-3.5' })
            ]),
            h('span', { class: 'font-mono font-bold tracking-wider' }, row.original.code)
        ])
    }),
    columnHelper.accessor('profile', {
        header: t('common.labels.profile', 'Profile'),
        cell: ({ row }) => h(Badge, { variant: 'secondary', class: 'text-[10px]' }, () => row.original.profile)
    }),
    columnHelper.accessor('price', {
        header: () => h('div', { class: 'text-right' }, t('common.labels.price', 'Price')),
        cell: ({ row }) => h('div', { class: 'text-right font-mono text-xs' }, `Rp${formatCurrency(row.original.price)}`)
    }),
    columnHelper.accessor('status', {
        header: t('common.labels.status', 'Status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status) }, () => row.original.status)
    }),
    columnHelper.accessor('used_at', {
        header: t('common.labels.used_at', 'Used At'),
        cell: ({ row }) => h('span', { class: 'text-[10px] text-muted-foreground' }, row.original.used_at || '-')
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.labels.actions', 'Actions')),
        cell: ({ row }) => {
            if (row.original.status !== 'Available') return null;
            return h('div', { class: 'flex items-center justify-end' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: () => deleteVoucher(row.original.id),
                    class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10'
                }, () => h(Trash2, { class: 'w-3.5 h-3.5' }))
            ]);
        }
    })
];

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

const table = useVueTable({
    get data() { return filteredVouchers.value },
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
        const [vItems, pItems] = await Promise.all([
            api.get('/admin/ja/isp/vouchers'),
            api.get('/admin/ja/isp/billing-plans')
        ]);
        vouchers.value = vItems.data.data.data;
        billingPlans.value = pItems.data.data;
        
        if (billingPlans.value.length > 0) {
            form.value.profile = billingPlans.value[0].mikrotik_profile || billingPlans.value[0].name;
            form.value.price = billingPlans.value[0].price;
        }
    } catch (error) {
        console.error('Voucher fetch failed', error);
    } finally {
        loading.value = false;
    }
};

const generateVouchers = async () => {
    submitting.value = true;
    try {
        await api.post('/admin/ja/isp/vouchers/generate', form.value);
        toast.success.action(t('isp.admin.vouchers.generate_success', 'Batch generated and synced to RADIUS'));
        showGenerateModal.value = false;
        await fetchData();
    } catch (error) {
        console.error('Generation failed', error);
        toast.error.action(error);
    } finally {
        submitting.value = false;
    }
};

const printCurrentSelection = () => {
    if (filteredVouchers.value.length === 0) return;
    const batchId = filteredVouchers.value[0].batch_id || 'latest';
    window.open(`/admin/isp/vouchers/print/${batchId}`, '_blank');
};

const deleteVoucher = async (id: number) => {
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('common.messages.confirm_delete', 'Are you sure?'),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    if (!confirmed) return;
    
    try {
        await api.delete(`/admin/ja/isp/vouchers/${id}`);
        await fetchData();
    } catch (error) {
        console.error('Delete failed', error);
    }
};

const exportScript = () => {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
    window.location.href = `/api/v1/admin/ja/isp/vouchers/export/script?${params.toString()}`;
};

const exportCsv = () => {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
    window.location.href = `/api/v1/admin/ja/isp/vouchers/export/csv?${params.toString()}`;
};

onMounted(fetchData);
</script>
