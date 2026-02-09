<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('isp.contracts.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.contracts.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ t('isp.contracts.actions.new') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.contracts.stats.active') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.contracts.stats.expiring_soon') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.expiringSoon }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.contracts.stats.expired') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ stats.expired }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>{{ t('isp.contracts.stats.total_value') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(stats.totalValue) }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <Input
                            v-model="filters.search"
                            :placeholder="t('isp.contracts.search_placeholder')"
                            class="w-full"
                        />
                    </div>
                    <Select v-model="filters.status">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('common.status.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('common.status.all') }}</SelectItem>
                            <SelectItem value="active">{{ t('isp.contracts.status.active') }}</SelectItem>
                            <SelectItem value="pending">{{ t('isp.contracts.status.pending') }}</SelectItem>
                            <SelectItem value="expired">{{ t('isp.contracts.status.expired') }}</SelectItem>
                            <SelectItem value="terminated">{{ t('isp.contracts.status.terminated') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filters.type">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue :placeholder="t('isp.contracts.all_types')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ t('isp.contracts.all_types') }}</SelectItem>
                            <SelectItem value="residential">{{ t('isp.contracts.types.residential') }}</SelectItem>
                            <SelectItem value="business">{{ t('isp.contracts.types.business') }}</SelectItem>
                            <SelectItem value="enterprise">{{ t('isp.contracts.types.enterprise') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>
        </Card>

        <!-- Contracts Table -->
        <Card>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ t('isp.contracts.columns.number') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.customer') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.type') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.plan') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.value') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.period') }}</TableHead>
                            <TableHead>{{ t('isp.contracts.columns.status') }}</TableHead>
                            <TableHead class="text-right">{{ t('common.actions.title') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="contract in contracts" :key="contract.id">
                            <TableCell class="font-mono text-sm">{{ contract.contract_number }}</TableCell>
                            <TableCell>
                                <div class="font-medium">{{ contract.customer_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ contract.customer_email }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ t(`isp.contracts.types.${contract.type}`) }}</Badge>
                            </TableCell>
                            <TableCell>{{ contract.plan_name }}</TableCell>
                            <TableCell class="font-medium">{{ formatCurrency(contract.monthly_value) }}/{{ t('common.units.month') }}</TableCell>
                            <TableCell>
                                <div class="text-sm">{{ formatDate(contract.start_date) }}</div>
                                <div class="text-xs text-muted-foreground">{{ t('common.to') }} {{ formatDate(contract.end_date) }}</div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(contract.status)">
                                    {{ t(`isp.contracts.status.${contract.status}`) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm" @click="viewContract(contract)">
                                                <Eye class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>{{ t('common.actions.view') }}</TooltipContent>
                                    </Tooltip>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm" @click="editContract(contract)">
                                                <Pencil class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>{{ t('common.actions.edit') }}</TooltipContent>
                                    </Tooltip>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm" @click="downloadContract(contract)">
                                                <Download class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>{{ t('isp.contracts.actions.download') }}</TooltipContent>
                                    </Tooltip>
                                    <Tooltip v-if="canRenew(contract)">
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm" @click="renewContract(contract)">
                                                <RotateCw class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>{{ t('isp.contracts.actions.renew') }}</TooltipContent>
                                    </Tooltip>
                                    <Tooltip v-if="contract.status === 'active'">
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm" class="text-destructive" @click="terminateContract(contract)">
                                                <CircleX class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>{{ t('isp.contracts.actions.terminate') }}</TooltipContent>
                                    </Tooltip>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="contracts.length === 0">
                            <TableCell colspan="8" class="text-center py-8 text-muted-foreground">
                                {{ t('isp.contracts.no_data') }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div class="flex justify-center">
            <Pagination
                v-if="pagination.total > 0"
                :current-page="pagination.currentPage"
                :total-pages="pagination.totalPages"
                :items-per-page="pagination.perPage"
                :total-items="pagination.total"
                @page-change="handlePageChange"
            />
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showModal">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{{ editingContract ? t('isp.contracts.edit') : t('isp.contracts.new') }}</DialogTitle>
                    <DialogDescription>{{ t('isp.contracts.modal_subtitle') }}</DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.customer') }}</Label>
                            <Select v-model="form.customer_id">
                                <SelectTrigger>
                                    <SelectValue :placeholder="t('isp.contracts.select_customer')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="customer in customers" :key="customer.id" :value="String(customer.id)">
                                        {{ customer.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.type') }}</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="residential">{{ t('isp.contracts.types.residential') }}</SelectItem>
                                    <SelectItem value="business">{{ t('isp.contracts.types.business') }}</SelectItem>
                                    <SelectItem value="enterprise">{{ t('isp.contracts.types.enterprise') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.plan') }}</Label>
                            <Select v-model="form.plan_id">
                                <SelectTrigger>
                                    <SelectValue :placeholder="t('isp.contracts.select_plan')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="plan in plans" :key="plan.id" :value="String(plan.id)">
                                        {{ plan.name }} - {{ formatCurrency(plan.price) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.duration') }}</Label>
                            <Select v-model="form.duration_months">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="6">6 {{ t('common.units.months') }}</SelectItem>
                                    <SelectItem value="12">12 {{ t('common.units.months') }}</SelectItem>
                                    <SelectItem value="24">24 {{ t('common.units.months') }}</SelectItem>
                                    <SelectItem value="36">36 {{ t('common.units.months') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.start_date') }}</Label>
                            <Input v-model="form.start_date" type="date" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.contracts.fields.monthly_value') }}</Label>
                            <Input v-model="form.monthly_value" type="number" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('isp.contracts.fields.notes') }}</Label>
                        <Textarea v-model="form.notes" rows="3" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showModal = false">{{ t('common.actions.cancel') }}</Button>
                    <Button @click="saveContract" :disabled="saving">
                        {{ saving ? t('common.actions.saving') : t('common.actions.save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import RotateCw from 'lucide-vue-next/dist/esm/icons/rotate-cw.js';
import CircleX from 'lucide-vue-next/dist/esm/icons/circle-x.js';
import {
    Card, CardContent, CardHeader, CardDescription,
    Table, TableHeader, TableBody, TableRow, TableHead, TableCell,
    Button, Input, Badge, Pagination, Label, Textarea,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Tooltip, TooltipTrigger, TooltipContent
} from '@/components/ui';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const toastService = useToast();

interface Contract {
    id: number;
    contract_number: string;
    customer_id: number;
    customer_name: string;
    customer_email: string;
    type: 'residential' | 'business' | 'enterprise';
    plan_id: number;
    plan_name: string;
    monthly_value: number;
    start_date: string;
    end_date: string;
    status: 'active' | 'pending' | 'expired' | 'terminated';
}

interface Customer { id: number; name: string; }
interface Plan { id: number; name: string; price: number; }

const contracts = ref<Contract[]>([]);
const customers = ref<Customer[]>([]);
const plans = ref<Plan[]>([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const editingContract = ref<Contract | null>(null);

const stats = reactive({
    active: 0,
    expiringSoon: 0,
    expired: 0,
    totalValue: 0
});

const filters = reactive({
    search: '',
    status: 'all',
    type: 'all'
});

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    perPage: 15,
    total: 0
});

const form = reactive({
    customer_id: '',
    type: 'residential',
    plan_id: '',
    duration_months: '12',
    start_date: '',
    monthly_value: 0,
    notes: ''
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        active: 'default',
        pending: 'secondary',
        expired: 'destructive',
        terminated: 'outline'
    };
    return variants[status] || 'outline';
};

const canRenew = (contract: Contract) => {
    return contract.status === 'active' || contract.status === 'expired';
};

const loadContracts = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/isp/contracts', { params: { ...filters, page: pagination.currentPage } });
        if (response.data.success) {
            contracts.value = response.data.data.items || [];
            pagination.total = response.data.data.total || 0;
            pagination.totalPages = response.data.data.last_page || 1;
            Object.assign(stats, response.data.data.stats || {});
        }
    } catch (error) {
        console.error('Failed to load contracts:', error);
    } finally {
        loading.value = false;
    }
};

const loadFormData = async () => {
    try {
        const [customersRes, plansRes] = await Promise.all([
            api.get('/admin/isp/customers/list'),
            api.get('/admin/isp/subscription/profiles/list')
        ]);
        customers.value = customersRes.data.data || [];
        plans.value = plansRes.data.data || [];
    } catch (error) {
        console.error('Failed to load form data:', error);
    }
};

const handlePageChange = (page: number) => {
    pagination.currentPage = page;
    loadContracts();
};

const openCreateModal = () => {
    editingContract.value = null;
    Object.assign(form, { customer_id: '', type: 'residential', plan_id: '', duration_months: '12', start_date: '', monthly_value: 0, notes: '' });
    showModal.value = true;
};

const editContract = (contract: Contract) => {
    editingContract.value = contract;
    Object.assign(form, {
        customer_id: String(contract.customer_id),
        type: contract.type,
        plan_id: String(contract.plan_id),
        duration_months: '12',
        start_date: contract.start_date,
        monthly_value: contract.monthly_value,
        notes: ''
    });
    showModal.value = true;
};

const saveContract = async () => {
    saving.value = true;
    try {
        const url = editingContract.value ? `/admin/isp/contracts/${editingContract.value.id}` : '/admin/isp/contracts';
        const method = editingContract.value ? 'put' : 'post';
        await api[method](url, form);
        toastService.service.success(t('common.messages.saved'));
        showModal.value = false;
        loadContracts();
    } catch (_error) {
        toastService.service.error(t('common.messages.error'));
    } finally {
        saving.value = false;
    }
};

const viewContract = (contract: Contract) => {
    console.warn('View contract:', contract);
};

const downloadContract = (contract: Contract) => {
    window.open(`/api/admin/isp/contracts/${contract.id}/pdf`, '_blank');
};

const renewContract = async (contract: Contract) => {
    try {
        await api.post(`/admin/isp/contracts/${contract.id}/renew`);
        toastService.service.success(t('isp.contracts.messages.renewed'));
        loadContracts();
    } catch (_error) {
        toastService.service.error(t('common.messages.error'));
    }
};

const terminateContract = async (contract: Contract) => {
    if (!confirm(t('isp.contracts.messages.confirm_terminate'))) return;
    try {
        await api.post(`/admin/isp/contracts/${contract.id}/terminate`);
        toastService.service.success(t('isp.contracts.messages.terminated'));
        loadContracts();
    } catch (_error) {
        toastService.service.error(t('common.messages.error'));
    }
};

onMounted(() => {
    loadContracts();
    loadFormData();
});
</script>
