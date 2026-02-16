<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.admin.vouchers.title', 'Hotspot Voucher Engine') }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ $t('isp.admin.vouchers.subtitle', 'Bulk generate and manage hotspot access vouchers') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="showImportModal = true" class="rounded-xl">
                    <FileUp class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.import', 'Import') }}
                </Button>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm" class="rounded-xl">
                            <Settings class="w-4 h-4 mr-2" />
                            {{ $t('common.labels.options', 'Options') }}
                            <ChevronDown class="w-4 h-4 ml-2" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="exportData">
                            <FileSpreadsheet class="w-4 h-4 mr-2" />
                            {{ $t('isp.admin.vouchers.export_excel', 'Excel Spreadsheet (.xlsx)') }}
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="exportScript">
                            <FileCode class="w-4 h-4 mr-2" />
                            {{ $t('isp.admin.vouchers.export_script', 'RouterOS Script (.rsc)') }}
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="showCleanupModal = true" class="text-destructive">
                            <Eraser class="w-4 h-4 mr-2" />
                            {{ $t('isp.admin.vouchers.cleanup', 'System Cleanup') }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button variant="outline" @click="printCurrentSelection" :disabled="filteredVouchers.length === 0" class="rounded-xl">
                    <Printer class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.vouchers.print_batch', 'Print Batch') }}
                </Button>
                <Button variant="outline" @click="showSingleModal = true" class="rounded-xl">
                    <UserPlus class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.vouchers.create_single', 'Create Single') }}
                </Button>
                <Button variant="outline" size="sm" @click="handleSyncUsage" :disabled="loading" class="rounded-xl">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': syncLoading }" />
                    {{ $t('isp.admin.vouchers.sync_usage', 'Sync Usage') }}
                </Button>
                <Button @click="showGenerateModal = true" class="rounded-xl">
                    <Ticket class="w-4 h-4 mr-2" />
                    {{ $t('isp.admin.vouchers.generate', 'Generate Batch') }}
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card v-for="(stat, idx) in summaryStats" :key="idx" class="border-border/40 shadow-sm rounded-xl overflow-hidden relative">
                <div :class="['absolute top-0 left-0 w-1 h-full', stat.accent]" />
                <CardContent class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-muted-foreground opacity-60 mb-1">{{ stat.label }}</p>
                        <h3 class="text-2xl font-bold font-mono tracking-tighter">{{ stat.value }}</h3>
                    </div>
                    <div :class="['p-3 rounded-xl bg-opacity-10', stat.color]">
                        <component :is="stat.icon" class="w-6 h-6" />
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
                            class="pl-10 h-9 rounded-xl"
                            :placeholder="$t('common.placeholders.search', 'Search code...')"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ $t('common.labels.status', 'Status') }}:</Label>
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-[120px] h-9 rounded-xl">
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

                    <div class="flex items-center gap-2">
                        <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ $t('common.labels.partner', 'Partner') }}:</Label>
                        <Select v-model="partnerFilter">
                            <SelectTrigger class="w-[140px] h-9 rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Partners</SelectItem>
                                <SelectItem v-for="p in partners" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <Label class="text-xs text-muted-foreground whitespace-nowrap">{{ $t('common.labels.profile', 'Profile') }}:</Label>
                        <Select v-model="profileFilter">
                            <SelectTrigger class="w-[140px] h-9 rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Profiles</SelectItem>
                                <SelectItem v-for="p in billingPlans" :key="p.id" :value="p.mikrotik_profile || p.name">{{ p.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        <DropdownMenu v-if="selectedVoucherIds.length > 0">
                            <DropdownMenuTrigger as-child>
                                <Button variant="secondary" size="sm" class="h-9 rounded-xl">
                                    {{ $t('common.labels.bulk_actions', 'Bulk Actions') }} ({{ selectedVoucherIds.length }})
                                    <ChevronDown class="w-4 h-4 ml-2" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="bulkDeactivate">
                                    <Ban class="w-4 h-4 mr-2" />
                                    {{ $t('common.actions.deactivate', 'Deactivate') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="bulkDelete" class="text-destructive">
                                    <Trash2 class="w-4 h-4 mr-2" />
                                    {{ $t('common.actions.delete', 'Delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
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
            <DialogContent class="sm:max-w-[500px] rounded-xl border border-border/40">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold">{{ $t('isp.admin.vouchers.generate_title', 'Generate New Batch') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.count', 'Quantity') }}</Label>
                            <Input v-model="form.count" type="number" min="1" max="1000" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.prefix', 'Code Prefix') }}</Label>
                            <Input v-model="form.prefix" placeholder="e.g. VIP-" maxlength="10" class="rounded-xl" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.plan', 'Billing Plan (Profile)') }}</Label>
                        <Select v-model="form.profile">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="plan in billingPlans" :key="plan.id" :value="plan.mikrotik_profile || plan.name" @click="form.price = plan.price">
                                    {{ plan.name }} ({{ plan.mikrotik_profile }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.pattern', 'Code Pattern') }}</Label>
                            <Select v-model="form.pattern">
                                <SelectTrigger class="rounded-xl">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mixed">Mixed (aB1c2D)</SelectItem>
                                    <SelectItem value="numbers">Numbers Only (123456)</SelectItem>
                                    <SelectItem value="lowercase">Lowercase (abcdef)</SelectItem>
                                    <SelectItem value="uppercase_pattern">Uppercase (ABCDEF)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.duration', 'Validity Duration') }}</Label>
                            <Input v-model="form.duration" placeholder="e.g. 1h, 1d, 3600" class="rounded-xl" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.price', 'Sell Price') }}</Label>
                            <Input v-model="form.price" type="number" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.partner', 'Partner (Optional)') }}</Label>
                            <Select v-model="form.partner_id">
                                <SelectTrigger class="rounded-xl">
                                    <SelectValue placeholder="Stock for Server" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">Stock for Server</SelectItem>
                                    <SelectItem v-for="p in partners" :key="p.id" :value="String(p.id)">{{ p.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <Checkbox id="print_immediate" :checked="form.print_immediate" @update:checked="form.print_immediate = $event" />
                        <Label for="print_immediate" class="text-sm font-medium leading-none cursor-pointer">
                            {{ $t('isp.admin.vouchers.print_immediate', 'Print batch immediately after generation') }}
                        </Label>
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

        <!-- Create Single Voucher Modal -->
        <Dialog v-model:open="showSingleModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ $t('isp.admin.vouchers.create_single_title', 'Create Single Hotspot User') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.partner', 'Partner') }}</Label>
                        <Select v-model="singleForm.partner_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="$t('isp.admin.vouchers.select_partner_optional', 'Direct Sale (Optional)')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">None (Direct)</SelectItem>
                                <SelectItem v-for="p in partners" :key="p.id" :value="String(p.id)">{{ p.name }} (Saldo: Rp{{ formatCurrency(p.saldo) }})</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.plan', 'Billing Plan (Profile)') }}</Label>
                        <Select v-model="singleForm.profile">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="plan in billingPlans" :key="plan.id" :value="plan.mikrotik_profile || plan.name" @click="updateSinglePrice(plan)">
                                    {{ plan.name }} ({{ plan.mikrotik_profile }}) - Rp{{ formatCurrency(plan.price) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.code', 'Voucher Code (User/Pass)') }}</Label>
                            <div class="flex gap-2">
                                <Input v-model="singleForm.code" class="font-mono" />
                                <Button variant="outline" size="icon" @click="generateRandomCode">
                                    <RefreshCw class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.duration', 'Validity Duration') }}</Label>
                            <Input v-model="singleForm.duration" placeholder="e.g. 1h, 1d" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('common.labels.price', 'Sell Price') }}</Label>
                            <Input v-model="singleForm.price" type="number" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.admin.vouchers.commission', 'Commission (Preview)') }}</Label>
                            <Input :value="calculatedCommission" disabled class="bg-muted/50" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showSingleModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="createSingleVoucher" :disabled="submitting || !singleForm.code || !singleForm.profile">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.create', 'Create User') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Import Vouchers Modal -->
        <Dialog v-model:open="showImportModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ $t('isp.admin.vouchers.import_title', 'Import Vouchers') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                <div class="p-6 border-2 border-dashed border-border rounded-lg text-center space-y-2 hover:border-primary/50 transition-colors cursor-pointer" @click="triggerFileInput">
                        <FileUp class="w-10 h-10 mx-auto text-muted-foreground" />
                        <div class="text-sm font-medium">{{ importFile ? importFile.name : $t('isp.admin.vouchers.select_file') }}</div>
                        <div class="text-xs text-muted-foreground">{{ $t('isp.admin.vouchers.formats_supported') }}</div>
                        <input type="file" ref="fileInput" class="hidden" accept=".xlsx,.xls,.cvs" @change="handleFileChange" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showImportModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="importVouchers" :disabled="submitting || !importFile">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.import', 'Start Import') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Cleanup Vouchers Modal -->
        <Dialog v-model:open="showCleanupModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="text-destructive">{{ $t('isp.admin.vouchers.cleanup_title', 'System Cleanup') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <p class="text-sm text-muted-foreground">
                        {{ $t('isp.admin.vouchers.cleanup_description', 'Permanently remove expired or available vouchers that are no longer needed.') }}
                    </p>
                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.status', 'Filter by Status') }}</Label>
                        <Select v-model="cleanupForm.status">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Expired">Expired Vouchers Only</SelectItem>
                                <SelectItem value="Available">Available Vouchers (Unsold)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('isp.admin.vouchers.before_date', 'Created Before') }}</Label>
                        <Input type="date" v-model="cleanupForm.before_date" />
                    </div>
                    <Alert variant="destructive">
                        <AlertTitle>{{ $t('isp.admin.vouchers.danger_zone') }}</AlertTitle>
                        <AlertDescription>{{ $t('isp.admin.vouchers.cannot_be_undone') }}</AlertDescription>
                    </Alert>
                </div>
                <DialogFooter>
                    <Button variant="ghost" @click="showCleanupModal = false">{{ $t('common.actions.cancel') }}</Button>
                    <Button variant="destructive" @click="runCleanup" :disabled="submitting || !cleanupForm.before_date">
                        <LoaderCircle v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ $t('common.actions.delete', 'Confirm Delete') }}
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
    DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem,
    Alert, AlertTitle, AlertDescription
} from '@/components/ui';

import Ticket from 'lucide-vue-next/dist/esm/icons/ticket.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Printer from 'lucide-vue-next/dist/esm/icons/printer.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import FileSpreadsheet from 'lucide-vue-next/dist/esm/icons/file-spreadsheet.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Package from 'lucide-vue-next/dist/esm/icons/package.js';
import CircleCheck from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Banknote from 'lucide-vue-next/dist/esm/icons/banknote.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import Eraser from 'lucide-vue-next/dist/esm/icons/eraser.js';
import Checkbox from '@/components/ui/Checkbox.vue';
import type { Partner } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const syncLoading = ref(false);
const submitting = ref(false);
const sorting = ref<SortingState>([]);
interface IspPlan {
    id: number;
    name: string;
    mikrotik_profile: string | null;
    price: number;
    partner_price: number | null;
}

const showGenerateModal = ref(false);
const showSingleModal = ref(false);
const showImportModal = ref(false);
const showCleanupModal = ref(false);
const importFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const rowSelection = ref({});

const form = ref({
    count: 10,
    prefix: '',
    profile: '',
    price: 0,
    pattern: 'mixed',
    duration: '',
    partner_id: 'none',
    print_immediate: false
});

const cleanupForm = ref({
    status: 'Expired',
    before_date: new Date().toISOString().split('T')[0],
});

const singleForm = ref({
    code: '',
    profile: '',
    profile_id: undefined as number | undefined,
    price: 0,
    duration: '',
    partner_id: 'none'
});

interface Voucher {
    id: number;
    code: string;
    profile: string;
    price: number;
    status: string;
    used_at: string | null;
    batch_id?: string;
    partner_id?: number | null;
}

const vouchers = ref<Voucher[]>([]);
const billingPlans = ref<IspPlan[]>([]);
const partners = ref<Partner[]>([]);
const globalStats = ref({
    total_stock: 0,
    total_available: 0,
    total_sold: 0,
    total_used: 0,
    total_value: 0,
    total_hpp: 0
});
const search = ref('');
const statusFilter = ref('all');
const partnerFilter = ref('all');
const profileFilter = ref('all');
// Status filtering options removed as they are not used in current filter logic or handled in template directly
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
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    }),
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
        cell: ({ row }) => h(Badge, { variant: 'secondary', class: 'text-[10px] rounded-xl' }, () => row.original.profile)
    }),
    columnHelper.accessor('price', {
        header: () => h('div', { class: 'text-right' }, t('common.labels.price', 'Price')),
        cell: ({ row }) => h('div', { class: 'text-right font-mono text-xs' }, `Rp${formatCurrency(row.original.price)}`)
    }),
    columnHelper.accessor('status', {
        header: t('common.labels.status', 'Status'),
        cell: ({ row }) => h(Badge, { variant: getStatusVariant(row.original.status), class: 'rounded-xl' }, () => row.original.status)
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
    
    if (partnerFilter.value !== 'all') {
        const pId = parseInt(partnerFilter.value);
        list = list.filter(v => v.partner_id === pId);
    }

    if (profileFilter.value !== 'all') {
        list = list.filter(v => v.profile === profileFilter.value);
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

const calculatedCommission = computed(() => {
    if (!singleForm.value.profile_id) return 'Rp0';
    const plan = billingPlans.value.find(p => p.id === singleForm.value.profile_id);
    if (!plan || !plan.partner_price) return 'Rp0';
    const comm = singleForm.value.price - plan.partner_price;
    return `Rp${formatCurrency(Math.max(0, comm))}`;
});

const updateSinglePrice = (plan: IspPlan) => {
    singleForm.value.profile = plan.mikrotik_profile || plan.name;
    singleForm.value.profile_id = plan.id;
    singleForm.value.price = plan.price;
};

const generateRandomCode = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    singleForm.value.code = result;
};

const summaryStats = computed(() => [
    { 
        label: t('isp.admin.inventory.stats.total_stock', 'Total Stock'), 
        value: globalStats.value.total_stock, 
        icon: Package, 
        color: 'text-blue-500 bg-blue-500',
        accent: 'bg-blue-500'
    },
    { 
        label: t('isp.admin.inventory.stats.available', 'Available'), 
        value: globalStats.value.total_available, 
        icon: CircleCheck, 
        color: 'text-green-500 bg-green-500',
        accent: 'bg-green-500' 
    },
    { 
        label: t('isp.admin.inventory.stats.total_hpp', 'Total HPP'), 
        value: `Rp${formatCurrency(globalStats.value.total_hpp)}`, 
        icon: Banknote, 
        color: 'text-orange-500 bg-orange-500',
        accent: 'bg-orange-500' 
    },
    { 
        label: t('isp.admin.inventory.stats.total_value', 'Total Value'), 
        value: `Rp${formatCurrency(globalStats.value.total_value)}`, 
        icon: TrendingUp, 
        color: 'text-indigo-500 bg-indigo-500',
        accent: 'bg-indigo-500' 
    },
]);

const fetchData = async () => {
    loading.value = true;
    try {
        const [vItems, pItems, statsRes, mitraRes] = await Promise.all([
            api.get('/admin/janet/isp/vouchers'),
            api.get('/admin/janet/isp/billing-plans'),
            api.get('/admin/janet/isp/vouchers/stock-summary'),
            api.get('/admin/janet/isp/partners')
        ]);
        vouchers.value = vItems.data.data.data;
        billingPlans.value = pItems.data.data;
        globalStats.value = statsRes.data.data.global;
        partners.value = mitraRes.data.data || [];
        
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

const bulkDelete = async () => {
    if (selectedVoucherIds.value.length === 0) return;
    
    const confirmed = await confirm({
        title: t('common.actions.delete', 'Delete'),
        message: t('isp.admin.vouchers.bulk_delete_confirm', 'Are you sure you want to delete {count} vouchers?').replace('{count}', String(selectedVoucherIds.value.length)),
        variant: 'danger',
        confirmText: t('common.actions.delete', 'Delete'),
    });
    
    if (!confirmed) return;
    
    try {
        await api.post('/admin/janet/isp/vouchers/bulk-delete', { ids: selectedVoucherIds.value });
        toast.success.action(t('common.messages.deleted_successfully', 'Deleted successfully'));
        rowSelection.value = {};
        await fetchData();
    } catch (error: unknown) {
        console.error('Bulk delete failed', error);
        toast.error.action(error instanceof Error ? error.message : 'Bulk delete failed');
    } finally {
        submitting.value = false;
    }
};

const bulkDeactivate = async () => {
    if (selectedVoucherIds.value.length === 0) return;

    try {
        await api.post('/admin/janet/isp/vouchers/bulk-update-status', { 
            ids: selectedVoucherIds.value,
            status: 'Expired'
        });
        toast.success.action(t('common.messages.updated_successfully', 'Updated successfully'));
        rowSelection.value = {};
        await fetchData();
    } catch (error: unknown) {
        console.error('Bulk deactivate failed', error);
        toast.error.action(error instanceof Error ? error.message : 'Bulk deactivate failed');
    } finally {
        submitting.value = false;
    }
};

const createSingleVoucher = async () => {
    submitting.value = true;
    try {
        const payload = {
            ...singleForm.value,
            partner_id: singleForm.value.partner_id === 'none' ? null : parseInt(singleForm.value.partner_id)
        };
        await api.post('/admin/janet/isp/vouchers/create-single', payload);
        toast.success.action(t('isp.admin.vouchers.create_success', 'Hotspot user created successfully'));
        showSingleModal.value = false;
        // Reset form
        singleForm.value.code = '';
        await fetchData();
    } catch (error: unknown) {
        console.error('Creation failed', error);
        const errorMessage = error instanceof Error ? error.message : String(error);
        toast.error.action(errorMessage || 'Creation failed');
    } finally {
        submitting.value = false;
    }
};

const generateVouchers = async () => {
    if (form.value.count > 1000) {
        toast.error.action(t('isp.admin.vouchers.limit_exceeded', 'Limit 1000 vouchers per batch'));
        return;
    }
    
    submitting.value = true;
    try {
        const payload = {
            ...form.value,
            partner_id: form.value.partner_id === 'none' ? null : parseInt(form.value.partner_id)
        };
        const res = await api.post('/admin/janet/isp/vouchers/generate', payload);
        const data = res.data.data;
        
        toast.success.action(t('isp.admin.vouchers.generate_success', 'Batch generated and synced to RADIUS'));
        showGenerateModal.value = false;
        
        if (form.value.print_immediate && data.batch_id) {
            window.open(`/admin/isp/vouchers/print/${data.batch_id}`, '_blank');
        }
        
        await fetchData();
    } catch (error: unknown) {
        console.error('Generation failed', error);
        const errorMessage = error instanceof Error ? error.message : String(error);
        toast.error.action(errorMessage || 'Generation failed');
    } finally {
        submitting.value = false;
    }
};

const printCurrentSelection = () => {
    if (filteredVouchers.value.length === 0) return;
    const batchId = filteredVouchers.value[0].batch_id || 'latest';
    window.open(`/admin/isp/vouchers/print/${batchId}`, '_blank');
};

const handleSyncUsage = async () => {
    syncLoading.value = true;
    try {
        const response = await api.post('/admin/janet/isp/vouchers/sync-usage');
        const count = response.data.data.count;
        toast.success.action(t('isp.admin.vouchers.sync_success', 'Synchronized {count} vouchers').replace('{count}', String(count)));
        await fetchData();
    } catch (error: unknown) {
        console.error('Sync failed', error);
        toast.error.action('Sync failed');
    } finally {
        syncLoading.value = false;
    }
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
        await api.delete(`/admin/janet/isp/vouchers/${id}`);
        await fetchData();
    } catch (error: unknown) {
        console.error('Delete failed', error);
        toast.error.action(error instanceof Error ? error.message : 'Delete failed');
    }
};

const exportScript = () => {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
    window.location.href = `/api/v1/admin/janet/isp/vouchers/export/script?${params.toString()}`;
};

const exportData = () => {
    const params = new URLSearchParams();
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
    if (partnerFilter.value !== 'all') params.set('partner_id', partnerFilter.value);
    window.location.href = `/api/v1/admin/janet/isp/vouchers/export?${params.toString()}`;
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        importFile.value = target.files[0];
    }
};

const importVouchers = async () => {
    if (!importFile.value) return;
    
    submitting.value = true;
    const formData = new FormData();
    formData.append('file', importFile.value);

    try {
        await api.post('/admin/janet/isp/vouchers/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success.action(t('common.messages.success', 'Imported successfully'));
        showImportModal.value = false;
        importFile.value = null;
        await fetchData();
    } catch (error: unknown) {
        console.error('Import failed', error);
        const errorMessage = error instanceof Error ? error.message : String(error);
        toast.error.action(errorMessage || 'Import failed');
    } finally {
        submitting.value = false;
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const runCleanup = async () => {
    submitting.value = true;
    try {
        const res = await api.post('/admin/janet/isp/vouchers/cleanup', cleanupForm.value);
        toast.success.action(res.data?.message || 'Cleanup successful');
        showCleanupModal.value = false;
        await fetchData();
    } catch (error: unknown) {
        console.error('Cleanup failed', error);
        toast.error.action(error instanceof Error ? error.message : 'Cleanup failed');
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchData);
</script>
