<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dispatch Center</h1>
                <p class="text-muted-foreground">Manage and track technician deployments in real-time.</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="gap-2 rounded-xl" @click="fetchDeployments">
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                    Refresh
                </Button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card v-for="stat in stats" :key="stat.label" class="border-border/40 shadow-sm overflow-hidden rounded-xl bg-card">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground opacity-60 mb-1">{{ stat.label }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stat.value }}</h3>
                        </div>
                        <div class="p-3 rounded-xl" :class="stat.bgClass">
                            <component :is="stat.icon" class="w-5 h-5" :class="stat.iconClass" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Main Card -->
        <Card class="rounded-xl border border-border/40 pb-0 shadow-sm overflow-hidden bg-card">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-muted/5">
                <div class="flex items-center gap-2 max-w-sm w-full">
                    <Search class="w-4 h-4 text-muted-foreground" />
                    <Input 
                        v-model="search" 
                        placeholder="Search deployments..." 
                        class="h-9 border-none focus-visible:ring-0 bg-transparent"
                    />
                </div>
            </div>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="'No deployments found.'"
                class="border-0"
            />
            
            <div class="p-4 border-t border-border/40 bg-muted/5" v-if="pagination">
                <Pagination
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="pagination.per_page"
                    @page-change="fetchDeployments"
                />
            </div>
        </Card>

        <!-- Status Update Modal -->
        <Dialog v-model:open="isStatusModalOpen">
            <DialogContent class="sm:max-w-[450px] rounded-xl">
                <DialogHeader>
                    <DialogTitle>Update Status</DialogTitle>
                    <DialogDescription>
                        Update the progress of this deployment.
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Current Status</Label>
                        <Select v-model="statusUpdate.status">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="scheduled">Scheduled</SelectItem>
                                <SelectItem value="in_progress">In Progress</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label>Progress Notes</Label>
                        <Textarea 
                            v-model="statusUpdate.notes" 
                            placeholder="Add mission updates or closure details..." 
                            class="rounded-xl min-h-[100px]"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isStatusModalOpen = false" class="rounded-xl">Cancel</Button>
                    <Button :disabled="submitting" @click="submitStatusUpdate" class="rounded-xl">
                        <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
                        Update Deployment
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h } from 'vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Button, Input, Badge, Card, CardContent, Select, SelectContent, SelectItem, 
    SelectTrigger, SelectValue, Dialog, DialogContent, DialogHeader, DialogTitle, 
    DialogDescription, DialogFooter, Label, DataTable, Textarea
} from '@/components/ui';
import { useVueTable, getCoreRowModel, createColumnHelper } from '@tanstack/vue-table';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Wrench from 'lucide-vue-next/dist/esm/icons/wrench.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Edit3 from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import dayjs from 'dayjs';
import type { TechnicianDeployment } from '@/types/isp';

const toast = useToast();
const deployments = ref<TechnicianDeployment[]>([]);
const loading = ref(true);
const search = ref('');
const pagination = ref<any>(null);
const isStatusModalOpen = ref(false);
const submitting = ref(false);
const selectedDeployment = ref<TechnicianDeployment | null>(null);

const statusUpdate = ref({
    status: 'scheduled',
    notes: ''
});

const columnHelper = createColumnHelper<TechnicianDeployment>();

const columns = [
    columnHelper.accessor('technician.name', {
        header: 'Technician',
        cell: info => h('div', { class: 'flex items-center gap-2' }, [
            h('div', { class: 'w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-[10px] font-bold' }, info.getValue()?.substring(0, 2)),
            h('span', { class: 'font-medium' }, info.getValue() || 'Unassigned')
        ])
    }),
    columnHelper.accessor('customer.name', {
        header: 'Customer',
        cell: info => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'font-medium' }, info.getValue()),
            h('span', { class: 'text-[10px] text-muted-foreground/60' }, info.row.original.type)
        ])
    }),
    columnHelper.accessor('scheduled_at', {
        header: 'Scheduled',
        cell: info => h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'text-sm' }, dayjs(info.getValue()).format('MMM D, YYYY')),
            h('span', { class: 'text-[10px] text-muted-foreground' }, dayjs(info.getValue()).format('HH:mm'))
        ])
    }),
    columnHelper.accessor('status', {
        header: 'Status',
        cell: info => {
            const status = info.getValue();
            const variants: Record<string, any> = {
                scheduled: 'secondary',
                in_progress: 'default',
                completed: 'success',
                cancelled: 'destructive'
            };
            return h(Badge, { variant: variants[status] || 'outline', class: 'capitalize rounded-xl' }, () => status.replace('_', ' '));
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: info => h('div', { class: 'flex justify-end' }, [
            h(Button, {
                variant: 'ghost',
                size: 'icon',
                onClick: () => openStatusModal(info.row.original)
            }, () => h(Edit3, { class: 'w-4 h-4' }))
        ])
    })
];

const table = useVueTable({
    get data() { return deployments.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const stats = computed(() => [
    { label: 'Total Deployments', value: deployments.value.length, icon: Calendar, bgClass: 'bg-primary/10', iconClass: 'text-primary' },
    { label: 'Active Tasks', value: deployments.value.filter(d => d.status === 'in_progress').length, icon: Wrench, bgClass: 'bg-blue-500/10', iconClass: 'text-blue-500' },
    { label: 'Pending', value: deployments.value.filter(d => d.status === 'scheduled').length, icon: Clock, bgClass: 'bg-amber-500/10', iconClass: 'text-amber-500' },
    { label: 'Completed Today', value: deployments.value.filter(d => d.status === 'completed' && dayjs(d.completed_at).isSame(dayjs(), 'day')).length, icon: CheckCircle, bgClass: 'bg-emerald-500/10', iconClass: 'text-emerald-500' }
]);

const fetchDeployments = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/operations/deployments', { params: { page } });
        deployments.value = response.data.data.data;
        pagination.value = response.data.data;
    } catch (_e) {
        toast.error.default('Failed to load deployments');
    } finally {
        loading.value = false;
    }
};

const openStatusModal = (deployment: TechnicianDeployment) => {
    selectedDeployment.value = deployment;
    statusUpdate.value = {
        status: deployment.status,
        notes: ''
    };
    isStatusModalOpen.value = true;
};

const submitStatusUpdate = async () => {
    if (!selectedDeployment.value) return;
    submitting.value = true;
    try {
        await api.patch(`/admin/janet/isp/operations/deployments/${selectedDeployment.value.id}/status`, statusUpdate.value);
        toast.success.default('Deployment status updated');
        isStatusModalOpen.value = false;
        fetchDeployments();
    } catch (_e) {
        toast.error.default('Failed to update status');
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchDeployments);
</script>
