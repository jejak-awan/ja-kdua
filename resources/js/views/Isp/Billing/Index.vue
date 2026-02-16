<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.billing.title') }}</h2>
                <p class="text-muted-foreground">{{ $t('isp.billing.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="fetchData" class="rounded-xl">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                    {{ $t('isp.billing.actions.refresh') }}
                </Button>
                <Button @click="$router.push('/admin/isp/settings?tab=billing')" variant="outline" class="gap-2 rounded-xl">
                    <Settings class="w-4 h-4" />
                    {{ $t('isp.billing.settings.title') }}
                </Button>
                <Button @click="generateInvoices" :disabled="generating" variant="secondary" class="gap-2 rounded-xl">
                    <Zap class="w-4 h-4" v-if="!generating" />
                    <Loader2 class="w-4 h-4 animate-spin" v-else />
                    {{ generating ? $t('isp.billing.messages.generating') : $t('isp.billing.actions.generate') }}
                </Button>
                <Button @click="runSuspendCheck" :disabled="suspending" variant="destructive" class="gap-2 rounded-xl">
                    <UserX class="w-4 h-4" v-if="!suspending" />
                    <Loader2 class="w-4 h-4 animate-spin" v-else />
                    {{ suspending ? $t('isp.billing.actions.suspending') : $t('isp.billing.actions.suspend_check') }}
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card class="p-6 border-none shadow-sm bg-gradient-to-br from-blue-500/10 to-transparent">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-500/10 rounded-2xl">
                        <DollarSign class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-bold tracking-tight opacity-60">{{ $t('isp.billing.stats.revenue') }}</p>
                        <p class="text-2xl font-bold font-mono tracking-tighter">Rp {{ formatNumber(stats.total_revenue) }}</p>
                    </div>
                </div>
            </Card>
            <Card class="p-6 border-none shadow-sm bg-gradient-to-br from-amber-500/10 to-transparent">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-500/10 rounded-2xl">
                        <Clock class="w-6 h-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-bold tracking-tight opacity-60">{{ $t('isp.billing.stats.pending') }}</p>
                        <p class="text-2xl font-bold font-mono tracking-tighter text-amber-600">Rp {{ formatNumber(stats.pending_amount) }}</p>
                    </div>
                </div>
            </Card>
            <Card class="p-6 border-none shadow-sm bg-gradient-to-br from-green-500/10 to-transparent">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-500/10 rounded-2xl">
                        <CheckCircle class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-bold tracking-tight opacity-60">{{ $t('isp.billing.stats.paid') }}</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.paid_count }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <Tabs v-model="activeTab" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="invoices" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ $t('isp.billing.tabs.invoices') }}
                    </TabsTrigger>
                    <TabsTrigger value="plans" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ $t('isp.billing.tabs.plans') }}
                    </TabsTrigger>
                    <TabsTrigger value="requests" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        {{ $t('isp.billing.tabs.requests') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="invoices">
                <Card>
                    <div class="p-4 border-b border-border/40 flex justify-between items-center">
                        <div class="relative w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="search" :placeholder="$t('isp.billing.fields.search_placeholder')" class="pl-9 rounded-xl" />
                        </div>
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-[180px] rounded-xl">
                                <SelectValue :placeholder="$t('common.status.all')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('common.status.all') }}</SelectItem>
                                <SelectItem value="paid">{{ $t('common.status.success') }}</SelectItem>
                                <SelectItem value="unpaid">{{ $t('common.status.failed') }}</SelectItem>
                                <SelectItem value="cancelled">{{ $t('ispServiceRequests.status.Rejected') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="selectedRowsCount > 0" class="flex items-center gap-4 py-2 px-4 bg-primary/5 border border-primary/20 rounded-xl mb-4 animate-in slide-in-from-top-2">
                        <span class="text-sm font-medium">
                            {{ selectedRowsCount }} {{ $t('isp.billing.tabs.invoices') }}
                        </span>
                        <div class="h-4 w-px bg-border mx-2"></div>
                        <div class="flex items-center gap-1">
                            <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                                <SelectTrigger class="w-[140px] h-8 text-xs rounded-lg">
                                    <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="paid">{{ $t('isp.billing.actions.set_paid') }}</SelectItem>
                                    <SelectItem value="unpaid">{{ $t('isp.billing.actions.set_unpaid') }}</SelectItem>
                                    <SelectItem value="cancelled">{{ $t('isp.billing.actions.cancel_invoices') }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                                {{ t('common.actions.cancel') }}
                            </Button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <DataTable 
                            :table="table" 
                            :loading="loadingInvoices" 
                            :empty-message="t('isp.billing.messages.no_data')"
                        />
                    </div>
                </Card>
            </TabsContent>

            <TabsContent value="plans">
                <div class="flex justify-end mb-6">
                    <Button @click="openNewPlanModal" class="rounded-xl gap-2">
                        <Plus class="w-4 h-4" />
                        {{ $t('isp.billing.plans_manager.new') }}
                    </Button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card v-for="plan in plans" :key="plan.id" class="p-6 relative overflow-hidden group">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold">{{ plan.name }}</h3>
                                <p class="text-sm text-primary font-bold">{{ plan.speed_limit }}</p>
                            </div>
                            <Badge :variant="plan.type === 'fiber' ? 'success' : 'secondary'">
                                {{ plan.type }}
                            </Badge>
                        </div>
                        <div class="text-3xl font-bold mb-6">
                            Rp {{ formatNumber(plan.price) }} <span class="text-xs font-normal text-muted-foreground">/ month</span>
                        </div>
                        <ul class="space-y-2 mb-6">
                            <li v-for="feature in plan.features" :key="feature" class="text-sm flex items-center gap-2">
                                <CheckCircle class="w-4 h-4 text-success" />
                                {{ feature }}
                            </li>
                        </ul>
                        <Button variant="outline" class="w-full" @click="openEditPlanModal(plan)">{{ $t('isp.billing.actions.edit_plan') }}</Button>
                        <CreditCard class="absolute -right-4 -bottom-4 w-24 h-24 text-primary opacity-[0.03] group-hover:scale-110 transition-transform" />
                    </Card>
                </div>
            </TabsContent>

            <TabsContent value="requests">
                <ServiceRequests />
            </TabsContent>
        </Tabs>

        <!-- Plan Management Modal -->
        <Dialog v-model:open="isPlanModalOpen">
            <DialogContent class="sm:max-w-[500px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>{{ editPlanId ? $t('isp.billing.plans_manager.edit') : $t('isp.billing.plans_manager.new') }}</DialogTitle>
                    <DialogDescription>
                        Define pricing and service features for this subscription plan.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleSavePlan" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ $t('isp.billing.plans_manager.fields.name') }}</Label>
                        <Input v-model="planForm.name" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.billing.plans_manager.fields.type') }}</Label>
                            <Select v-model="planForm.type">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="fiber">Fiber</SelectItem>
                                    <SelectItem value="hotspot">Hotspot</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.billing.plans_manager.fields.price') }}</Label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-xs">Rp</span>
                                <Input type="number" v-model="planForm.price" class="pl-8" required />
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('isp.billing.plans_manager.fields.speed') }}</Label>
                        <Input v-model="planForm.speed_limit" placeholder="e.g. 50 Mbps" required />
                    </div>
                    <div class="space-y-2">
                        <Label>{{ $t('isp.billing.plans_manager.fields.features') }}</Label>
                        <Textarea v-model="planForm.features_text" rows="4" class="rounded-xl" :placeholder="$t('isp.billing.plans_manager.fields.features')" />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="savingPlan" class="w-full rounded-xl">
                            <Loader2 v-if="savingPlan" class="w-4 h-4 animate-spin mr-2" />
                            {{ $t('common.actions.save') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Invoice Detail Modal -->
        <Dialog v-model:open="isInvoiceModalOpen">
            <DialogContent class="sm:max-w-[450px] rounded-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start mr-6">
                        <div>
                            <DialogTitle>{{ $t('isp.billing.invoices') }} #{{ selectedInvoice?.id }}</DialogTitle>
                            <DialogDescription>
                                {{ selectedInvoice?.billing_period }}
                            </DialogDescription>
                        </div>
                        <Badge :variant="selectedInvoice ? getStatusVariant(selectedInvoice.status) : 'default'">
                            {{ selectedInvoice?.status }}
                        </Badge>
                    </div>
                </DialogHeader>
                <div class="space-y-6 py-4">
                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">{{ $t('isp.billing.fields.customer') }}</span>
                            <span class="font-medium text-right">{{ selectedInvoice?.user?.name }}<br /><span class="text-xs text-muted-foreground">{{ selectedInvoice?.user?.email }}</span></span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-border/40 pt-4">
                            <span class="text-muted-foreground">{{ $t('isp.billing.fields.amount') }}</span>
                            <span class="font-bold text-lg">Rp {{ selectedInvoice ? formatNumber(selectedInvoice.amount) : '0' }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-border/40 pt-4">
                            <span class="text-muted-foreground">{{ $t('isp.billing.fields.due_date') }}</span>
                            <span>{{ selectedInvoice ? formatDate(selectedInvoice.due_date) : '' }}</span>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isInvoiceModalOpen = false" class="w-full rounded-xl">
                        {{ t('common.actions.close') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { 
    Button, Card, Input, Badge, Tabs, TabsList, TabsTrigger, TabsContent, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, Label, Textarea,
    DataTable, Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import { h } from 'vue';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import DollarSign from 'lucide-vue-next/dist/esm/icons/dollar-sign.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import UserX from 'lucide-vue-next/dist/esm/icons/user-minus.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import ServiceRequests from '../Customer/ServiceRequests.vue';

import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';
import type { IspInvoice, IspPlan } from '@/types/isp';

const { t } = useI18n();

const toast = useToast();
const loading = ref(false);
const loadingInvoices = ref(false);
const generating = ref(false);
const suspending = ref(false);
const savingPlan = ref(false);
const isPlanModalOpen = ref(false);
const isInvoiceModalOpen = ref(false);

const editPlanId = ref<number | null>(null);
const selectedInvoice = ref<IspInvoice | null>(null);
const activeTab = ref('invoices');

const stats = ref({
    total_revenue: 0,
    pending_amount: 0,
    paid_count: 0,
    unpaid_count: 0
});

const invoices = ref<IspInvoice[]>([]);
const plans = ref<IspPlan[]>([]);
const search = ref('');
const statusFilter = ref('all');
const rowSelection = ref({});
const sorting = ref([{ id: 'due_date', desc: true }]);
const bulkActionSelection = ref('');
const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

const planForm = ref({
    name: '',
    type: 'fiber',
    price: 0,
    speed_limit: '',
    features_text: ''
});

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<IspInvoice>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
            onClick: (e: MouseEvent) => e.stopPropagation(),
        }),
        size: 50,
    }),
    columnHelper.display({
        id: 'customer',
        header: t('isp.billing.fields.customer'),
        cell: info => {
            const invoice = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-medium' }, invoice.user?.name),
                h('span', { class: 'text-xs text-muted-foreground' }, invoice.user?.email)
            ]);
        }
    }),
    columnHelper.accessor('billing_period', {
        header: t('isp.billing.fields.period'),
        cell: info => h('span', { class: 'font-mono text-xs' }, info.getValue()),
    }),
    columnHelper.accessor('amount', {
        header: t('isp.billing.fields.amount'),
        cell: info => h('span', { class: 'font-bold' }, `Rp ${formatNumber(info.getValue())}`),
    }),
    columnHelper.accessor('due_date', {
        header: t('isp.billing.fields.due_date'),
        cell: info => h('span', { class: 'text-xs' }, formatDate(info.getValue())),
    }),
    columnHelper.accessor('status', {
        header: t('isp.billing.fields.status'),
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()) }, () => info.getValue()),
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.billing.fields.actions')),
        cell: info => {
            const invoice = info.row.original;
            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'h-8 w-8 p-0 rounded-lg',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); viewInvoice(invoice); }
                }, () => h(Eye, { class: 'w-4 h-4' })),
                invoice.status === 'unpaid' ? h(Button, {
                    variant: 'outline',
                    size: 'sm',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); handlePay(invoice); }
                }, () => t('isp.billing.actions.pay')) : null
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return invoices.value },
    columns,
    state: {
        get rowSelection() { return rowSelection.value },
        get sorting() { return sorting.value },
    },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
    meta: {
        onRowClick: (invoice: IspInvoice) => {
            viewInvoice(invoice);
        }
    }
});

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    try {
        if (action === 'paid') {
            await Promise.all(ids.map(id => api.post(`/admin/janet/isp/billing/invoices/${id}/pay`)));
        } else {
            await Promise.all(ids.map(id => api.patch(`/admin/janet/isp/billing/invoices/${id}`, { status: action })));
        }
        toast.success.action(t('common.messages.success.action'));
        table.resetRowSelection();
        fetchData();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
    bulkActionSelection.value = '';
};

const fetchData = async () => {
    loading.value = true;
    try {
        const statsRes = await api.get('/admin/janet/isp/billing/stats');
        stats.value = statsRes.data.data;

        const plansRes = await api.get('/admin/janet/isp/billing/plans');
        plans.value = plansRes.data.data;

        fetchInvoices();
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const fetchInvoices = async () => {
    loadingInvoices.value = true;
    try {
        const params: Record<string, string> = {};
        if (search.value) params.search = search.value;
        if (statusFilter.value !== 'all') params.status = statusFilter.value;

        const response = await api.get('/admin/janet/isp/billing/invoices', { params });
        invoices.value = response.data.data.data as IspInvoice[];
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_invoices'));
    } finally {
        loadingInvoices.value = false;
    }
};

const generateInvoices = async () => {
    if (!confirm(t('isp.billing.messages.confirm_generate'))) return;
    
    generating.value = true;
    try {
        const res = await api.post('/admin/janet/isp/billing/generate');
        toast.success.default(res.data.message || t('isp.billing.messages.success_generate'));
        fetchData();
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_generate'));
    } finally {
        generating.value = false;
    }
};

const runSuspendCheck = async () => {
    if (!confirm(t('isp.billing.messages.confirm_suspend'))) return;
    
    suspending.value = true;
    try {
        const res = await api.post('/admin/janet/isp/billing/suspend-check');
        toast.success.default(res.data.message);
        fetchData();
    } catch (_error) {
        toast.error.default(t('isp.billing.messages.error_suspend'));
    } finally {
        suspending.value = false;
    }
};

const handlePay = async (invoice: IspInvoice) => {
    try {
        await api.post(`/admin/janet/isp/billing/invoices/${invoice.id}/pay`);
        toast.success.action(t('isp.billing.messages.success_pay'));
        fetchData();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    }
};

const openNewPlanModal = () => {
    editPlanId.value = null;
    planForm.value = {
        name: '',
        type: 'fiber',
        price: 0,
        speed_limit: '',
        features_text: ''
    };
    isPlanModalOpen.value = true;
};

const openEditPlanModal = (plan: IspPlan) => {
    editPlanId.value = plan.id;
    planForm.value = {
        name: plan.name,
        type: plan.type,
        price: plan.price,
        speed_limit: plan.speed_limit || '',
        features_text: plan.features?.join('\n') || ''
    };
    isPlanModalOpen.value = true;
};

const handleSavePlan = async () => {
    savingPlan.value = true;
    try {
        const payload = {
            ...planForm.value,
            features: planForm.value.features_text.split('\n').filter(f => f.trim() !== '')
        };

        if (editPlanId.value) {
            await api.patch(`/admin/janet/isp/billing/plans/${editPlanId.value}`, payload);
        } else {
            await api.post('/admin/janet/isp/billing/plans', payload);
        }

        toast.success.action(t('isp.billing.messages.success_plan'));
        isPlanModalOpen.value = false;
        fetchData();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        savingPlan.value = false;
    }
};

const viewInvoice = (invoice: IspInvoice) => {
    selectedInvoice.value = invoice;
    isInvoiceModalOpen.value = true;
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('id-ID').format(num);
};

const formatDate = (date: string) => {
    return dayjs(date).format('DD MMM YYYY');
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'paid': return 'success';
        case 'unpaid': return 'warning';
        case 'cancelled': return 'destructive';
        default: return 'secondary';
    }
};

watch([search, statusFilter], () => {
    fetchInvoices();
});

onMounted(() => {
    fetchData();
});
</script>
