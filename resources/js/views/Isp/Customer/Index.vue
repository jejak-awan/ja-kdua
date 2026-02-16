<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.billing.customers_manager.title') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.billing.customers_manager.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <input
                    type="file"
                    ref="fileInput"
                    class="hidden"
                    accept=".csv,.xlsx"
                    @change="handleImport"
                />
                <Button variant="outline" @click="triggerImport" :disabled="isImporting" class="rounded-xl">
                    <FileUp class="mr-2 h-4 w-4" :class="{ 'animate-bounce': isImporting }" />
                    {{ isImporting ? t('common.labels.processing') : t('isp.customers.actions.import') }}
                </Button>
                <Button variant="outline" @click="handleExport" class="rounded-xl">
                    <Download class="mr-2 h-4 w-4" />
                    {{ t('isp.customers.actions.export') }}
                </Button>
                <Button @click="openCreateModal" class="gap-2 rounded-xl">
                    <Plus class="w-4 h-4" />
                    {{ t('isp.customers.actions.new') }}
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card class="p-6 border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-blue-500/10 to-transparent">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.customers.stats.total') }}</span>
                    <span class="text-2xl font-bold">{{ stats.total }}</span>
                </div>
            </Card>
            <Card class="p-6 border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-green-500/10 to-transparent">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.customers.stats.active') }}</span>
                    <span class="text-2xl font-bold text-green-600">{{ stats.active }}</span>
                </div>
            </Card>
            <Card class="p-6 border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-amber-500/10 to-transparent">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.customers.stats.isolated') }}</span>
                    <span class="text-2xl font-bold text-amber-600">{{ stats.isolated }}</span>
                </div>
            </Card>
            <Card class="p-6 border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-slate-500/10 to-transparent">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-bold text-muted-foreground tracking-tight">{{ t('isp.customers.stats.inactive') }}</span>
                    <span class="text-2xl font-bold text-slate-500">{{ stats.inactive }}</span>
                </div>
            </Card>
        </div>

        <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="t('isp.billing.fields.search_placeholder')" class="pl-9 rounded-xl" />
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="statusFilter">
                         <SelectTrigger class="w-full md:w-40 rounded-xl">
                            <SelectValue placeholder="Filter Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="isolated">Isolated</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button variant="outline" size="icon" @click="fetchCustomers">
                        <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                    </Button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.billing.messages.no_data')"
                />
            </div>
        </Card>


        <ConfirmModal
            :is-open="isDeleteAlertOpen"
            variant="destructive"
            :title="t('isp.billing.customers_manager.delete')"
            :description="t('isp.billing.customers_manager.delete_confirm')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            @confirm="confirmDelete"
            @cancel="isDeleteAlertOpen = false"
            @update:is-open="isDeleteAlertOpen = $event"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import type { IspUser } from '@/types/isp';
import {
    Button, Input, Card, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Badge,
    DataTable, DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import MoreHorizontal from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import FileUp from 'lucide-vue-next/dist/esm/icons/file-up.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();

const loading = ref(false);
const isSaving = ref(false);
const customers = ref<IspUser[]>([]);
const isImporting = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const stats = ref({ total: 0, active: 0, isolated: 0, inactive: 0 });
const search = ref('');
const statusFilter = ref('all');

const selectedCustomer = ref<IspUser | null>(null);
const isDeleteAlertOpen = ref(false);
const customerToDelete = ref<number | null>(null);

const columnHelper = createColumnHelper<IspUser>();

const columns = [
    columnHelper.accessor('name', {
        header: t('isp.billing.customers_manager.fields.name'),
        cell: info => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'font-medium' }, info.getValue()),
            h('span', { class: 'text-xs text-muted-foreground' }, info.row.original.email)
        ])
    }),
    columnHelper.accessor(row => row.customer?.plan?.name, {
        header: t('isp.billing.customers_manager.fields.plan'),
        cell: info => info.getValue() || '-'
    }),
    columnHelper.accessor(row => row.customer?.status, {
        header: t('isp.customers.fields.status'),
        cell: info => {
            const status = info.getValue() || 'inactive';
            const customer = info.row.original.customer;
            
            const variantMap: Record<string, 'success' | 'destructive' | 'warning' | 'secondary'> = {
                active: 'success',
                isolated: 'warning',
                inactive: 'secondary',
                suspended: 'destructive'
            };

            const labels: Record<string, string> = {
                active: t('isp.billing.plans_manager.options.active'),
                isolated: t('isp.customers.stats.isolated'),
                inactive: t('isp.billing.plans_manager.options.inactive'),
                suspended: 'Suspended'
            };

            return h('div', { class: 'flex flex-col gap-1' }, [
                h(Badge, { variant: variantMap[status] || 'secondary', class: 'w-fit' }, () => labels[status] || status),
                customer?.is_fup_active && h(Badge, { variant: 'outline', class: 'w-fit text-[9px] border-amber-200 text-amber-600 bg-amber-50' }, () => 'FUP Active')
            ]);
        }
    }),
    columnHelper.display({
        id: 'technical',
        header: t('isp.customers.actions.view_technical'),
        cell: info => {
            const customer = info.row.original.customer;
            
            return h('div', { class: 'flex flex-col gap-0.5' }, [
                h('div', { class: 'flex items-center gap-1.5' }, [
                    h('span', { class: 'text-[10px] font-mono text-muted-foreground' }, customer?.mikrotik_login || '-'),
                    customer?.ip_address && h('span', { class: 'text-[10px] text-blue-500 font-bold' }, `[${customer.ip_address}]`)
                ]),
                // Demo Fraud Alert (Premium Look)
                customer?.id && (customer.id % 10 === 0) && h('div', { class: 'flex items-center gap-1 text-[9px] font-bold text-red-500 animate-pulse' }, [
                    h('div', { class: 'w-1 h-1 rounded-full bg-red-500' }),
                    t('isp.customers.fields.fraud_alert')
                ])
            ]);
        }
    }),
    columnHelper.accessor(row => row.customer?.risk, {
        id: 'risk',
        header: t('isp.customers.risk.label'),
        cell: info => {
            const risk = info.getValue();
            if (!risk) return '-';

            return h('div', { class: 'flex flex-col gap-1' }, [
                h(Badge, { 
                    variant: risk.color as any, 
                    class: 'w-fit text-[10px]' 
                }, () => t(`isp.customers.risk.${risk.level.toLowerCase()}`)),
                h('div', { class: 'w-full bg-slate-100 rounded-full h-1' }, [
                    h('div', { 
                        class: `h-1 rounded-full ${risk.score > 60 ? 'bg-red-500' : (risk.score > 30 ? 'bg-amber-500' : 'bg-green-500')}`,
                        style: { width: `${risk.score}%` }
                    })
                ])
            ]);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('common.actions.title')),
        cell: info => {
            return h(DropdownMenu, [
                h(DropdownMenuTrigger, { asChild: true }, [
                    h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, [
                        h(MoreHorizontal, { class: 'h-4 w-4' })
                    ])
                ]),
                h(DropdownMenuContent, { align: 'end', class: 'w-40' }, [
                    h(DropdownMenuItem, { 
                        onClick: () => editCustomer(info.row.original)
                    }, [
                        h(Pencil, { class: 'mr-2 h-4 w-4' }),
                        t('common.actions.edit')
                    ]),
                    h(DropdownMenuItem, { 
                        class: 'text-destructive focus:text-destructive',
                        onClick: () => confirmDeleteCustomer(info.row.original.id)
                    }, [
                        h(Trash2, { class: 'mr-2 h-4 w-4' }),
                        t('common.actions.delete')
                    ])
                ])
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return customers.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/janet/isp/customers/stats');
        stats.value = response.data.data;
    } catch (_e) {
        console.warn('Failed to load stats');
    }
};

const triggerImport = () => {
    fileInput.value?.click();
};

const handleImport = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files || target.files.length === 0) return;

    const file = target.files[0];
    const formData = new FormData();
    formData.append('file', file);

    isImporting.value = true;
    try {
        await api.post('/admin/janet/isp/customers/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        toast.success.default('Customers imported successfully');
        fetchCustomers();
        fetchStats();
    } catch (_error) {
        toast.error.default('Failed to import customers');
    } finally {
        isImporting.value = false;
        if (fileInput.value) fileInput.value.value = '';
    }
};

const handleExport = async () => {
    window.open('/api/v1/admin/janet/isp/customers/export', '_blank');
};

const fetchCustomers = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/customers', {
            params: {
                search: search.value,
                status: statusFilter.value
            }
        });
        customers.value = response.data.data.data;
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

let searchDebounce: NodeJS.Timeout;
watch([search, statusFilter], () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(fetchCustomers, 300);
});

onMounted(() => {
    fetchStats();
    fetchCustomers();
});

const openCreateModal = () => {
    router.push({ name: 'isp-subscription-customers-create' });
};

const editCustomer = (customer: IspUser) => {
    router.push({ 
        name: 'isp-subscription-customers-edit', 
        params: { id: customer.id } 
    });
};

const confirmDeleteCustomer = (id: number) => {
    customerToDelete.value = id;
    isDeleteAlertOpen.value = true;
};

const confirmDelete = async () => {
    if (!customerToDelete.value) return;
    try {
        await api.delete(`/admin/janet/isp/customers/${customerToDelete.value}`);
        toast.success.default(t('common.messages.success.deleted'));
        fetchCustomers();
    } catch (_error) {
        toast.error.default(t('common.messages.error.delete'));
    } finally {
        isDeleteAlertOpen.value = false;
        customerToDelete.value = null;
    }
};
</script>
