<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.billing.customers_manager.title') }}</h1>
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
                <Button variant="outline" @click="triggerImport" :disabled="isImporting">
                    <FileUp class="mr-2 h-4 w-4" :class="{ 'animate-bounce': isImporting }" />
                    {{ isImporting ? 'Importing...' : 'Import CSV' }}
                </Button>
                <Button variant="outline" @click="handleExport">
                    <Download class="mr-2 h-4 w-4" />
                    Export CSV
                </Button>
                <Button @click="openCreateModal" class="gap-2 rounded-xl">
                    <Plus class="w-4 h-4" />
                    {{ t('isp.billing.customers_manager.new') }}
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <div class="p-6 flex flex-col gap-1">
                    <span class="text-sm font-medium text-muted-foreground">{{ t('isp.billing.stats.revenue') || 'Total Customers' }}</span>
                    <span class="text-2xl font-bold">{{ stats.total }}</span>
                </div>
            </Card>
            <Card>
                <div class="p-6 flex flex-col gap-1">
                    <span class="text-sm font-medium text-muted-foreground">{{ t('isp.billing.stats.paid') || 'Active' }}</span>
                    <span class="text-2xl font-bold text-green-600">{{ stats.active }}</span>
                </div>
            </Card>
            <Card>
                <div class="p-6 flex flex-col gap-1">
                    <span class="text-sm font-medium text-muted-foreground">{{ t('isp.billing.stats.pending') || 'Isolated' }}</span>
                    <span class="text-2xl font-bold text-red-600">{{ stats.isolated }}</span>
                </div>
            </Card>
            <Card>
                <div class="p-6 flex flex-col gap-1">
                    <span class="text-sm font-medium text-muted-foreground">{{ t('isp.billing.stats.unpaid') || 'Inactive' }}</span>
                    <span class="text-2xl font-bold text-gray-500">{{ stats.inactive }}</span>
                </div>
            </Card>
        </div>

        <Card>
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="t('isp.billing.fields.search_placeholder')" class="pl-9" />
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="statusFilter">
                         <SelectTrigger class="w-full md:w-40">
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

        <CustomerModal
            v-model:open="isModalOpen"
            :customer="selectedCustomer"
            :loading="isSaving"
            @save="handleSave"
        />

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
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import type { IspUser } from '@/types/isp';
import {
    Button, Input, Card, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    DataTable, DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import CustomerModal from './Modals/CustomerModal.vue';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import MoreHorizontal from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import FileUp from 'lucide-vue-next/dist/esm/icons/file-up.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';

const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const isSaving = ref(false);
const customers = ref<IspUser[]>([]);
const isImporting = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const stats = ref({ total: 0, active: 0, isolated: 0, inactive: 0 });
const search = ref('');
const statusFilter = ref('all');

const isModalOpen = ref(false);
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
        header: t('isp.billing.customers_manager.fields.status'),
        cell: info => {
            const status = info.getValue() || 'inactive';
            const colors: Record<string, string> = {
                active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                isolated: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                inactive: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
            };
            return h('span', { class: `px-2 py-1 rounded-full text-xs font-semibold ${colors[status]}` }, status.toUpperCase());
        }
    }),
    columnHelper.accessor(row => row.customer?.mikrotik_login, {
        header: 'MikroTik User',
        cell: info => h('code', { class: 'bg-muted px-1 py-0.5 rounded text-xs' }, info.getValue() || '-')
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
        const response = await api.get('/admin/ja/isp/customers/stats');
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
        await api.post('/admin/ja/isp/customers/import', formData, {
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
    window.open('/api/v1/admin/ja/isp/customers/export', '_blank');
};

const fetchCustomers = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/customers', {
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
    selectedCustomer.value = null;
    isModalOpen.value = true;
};

const editCustomer = (customer: IspUser) => {
    selectedCustomer.value = customer;
    isModalOpen.value = true;
};

const handleSave = async (data: Record<string, unknown>) => {
    isSaving.value = true;
    try {
        if (selectedCustomer.value) {
            await api.put(`/admin/ja/isp/customers/${selectedCustomer.value.id}`, data);
            toast.success.default(t('common.messages.success.saved'));
        } else {
            await api.post('/admin/ja/isp/customers', data);
            toast.success.default(t('common.messages.success.saved'));
        }
        isModalOpen.value = false;
        fetchCustomers();
    } catch (_error) {
        toast.error.default(t('common.messages.error.save'));
    } finally {
        isSaving.value = false;
    }
};

const confirmDeleteCustomer = (id: number) => {
    customerToDelete.value = id;
    isDeleteAlertOpen.value = true;
};

const confirmDelete = async () => {
    if (!customerToDelete.value) return;
    try {
        await api.delete(`/admin/ja/isp/customers/${customerToDelete.value}`);
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
