<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ t('isp.outages.title') }}</h2>
                <p class="text-sm text-muted-foreground mt-1">{{ t('isp.outages.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button class="gap-2 rounded-xl" @click="openCreateModal">
                    <AlertTriangle class="w-4 h-4" />
                    {{ t('isp.outages.new') }}
                </Button>
            </div>
        </div>

        <!-- Incidents Table -->
        <Card class="rounded-xl border border-border/40 shadow-sm overflow-hidden text-sm">
            <!-- Standardized Bulk Action Bar -->
            <Transition
                enter-active-class="transition-[opacity,transform] duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-[opacity,transform] duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="selectedRowsCount > 0" class="flex items-center gap-4 py-2 px-4 bg-primary/5 border-b border-primary/20 animate-in slide-in-from-top-2">
                    <span class="text-sm font-medium">
                        {{ selectedRowsCount }} {{ t('isp.outages.title') }} Selected
                    </span>
                    <div class="h-4 w-px bg-border mx-2"></div>
                    <div class="flex items-center gap-2">
                        <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                        <SelectTrigger class="w-[160px] h-8 text-xs rounded-xl">
                                <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Resolved" class="text-success focus:text-success">Mark Resolved</SelectItem>
                                <SelectItem value="Monitoring" class="text-blue-500 focus:text-blue-500">Set Monitoring</SelectItem>
                                <SelectItem value="delete" class="text-destructive focus:text-destructive">{{ t('common.actions.delete') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                            {{ t('common.actions.cancel') }}
                        </Button>
                    </div>
                </div>
            </Transition>

            <DataTable 
                :table="table" 
                :loading="loading" 
                :empty-message="t('common.messages.no_data')"
            />
        </Card>

        <!-- Outage Modal -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[500px] rounded-2xl">
                <DialogHeader>
                    <DialogTitle>{{ editId ? t('isp.outages.modals.edit_title') : t('isp.outages.modals.create_title') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('isp.outages.modals.desc') }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="saveOutage" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>{{ t('isp.outages.fields.title') }}</Label>
                        <Input v-model="form.title" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.outages.fields.type') }}</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Unscheduled">Unscheduled</SelectItem>
                                    <SelectItem value="Scheduled">Scheduled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.outages.fields.status') }}</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Investigating">{{ t('isp.outages.fields.investigating') }}</SelectItem>
                                    <SelectItem value="Identified">{{ t('isp.outages.fields.identified') }}</SelectItem>
                                    <SelectItem value="Monitoring">{{ t('isp.outages.fields.monitoring') }}</SelectItem>
                                    <SelectItem value="Resolved">{{ t('isp.outages.fields.resolved') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('isp.outages.fields.affected') }}</Label>
                        <Select v-model="form.node_id">
                            <SelectTrigger>
                                <SelectValue placeholder="All Nodes (Global)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="null">All Nodes (Global)</SelectItem>
                                <SelectItem v-for="node in nodes" :key="node.id" :value="node.id.toString()">
                                    {{ node.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('isp.outages.fields.description') }}</Label>
                        <Textarea v-model="form.description" rows="3" required />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="isSaving" class="w-full rounded-xl">
                            <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin mr-2" />
                            {{ editId ? t('isp.outages.actions.update') : t('isp.outages.actions.announce') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { ensureArray } from '@/utils/responseParser';
import { 
    Button, Input, Label, Badge, Textarea, Card,
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
    DataTable, Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper
} from '@tanstack/vue-table';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import dayjs from 'dayjs';
import type { Outage } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const outages = ref<Outage[]>([]);
const nodes = ref<{id: number, name: string}[]>([]);
const loading = ref(true);
const isDialogOpen = ref(false);
const isSaving = ref(false);
const editId = ref<number | null>(null);
const rowSelection = ref({});
const bulkActionSelection = ref('');

const form = ref({
    title: '',
    description: '',
    type: 'Unscheduled',
    status: 'Investigating',
    node_id: 'null',
    started_at: dayjs().format('YYYY-MM-DDTHH:mm')
});

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<Outage>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
        }),
        size: 50,
    }),
    columnHelper.accessor('title', {
        header: t('isp.outages.columns.incident'),
        cell: info => {
            const outage = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('div', { class: 'font-bold flex items-center gap-2' }, [
                    h('div', { class: `w-2 h-2 rounded-full ${getStatusColor(outage.status)}` }),
                    info.getValue()
                ]),
                h('div', { class: 'text-xs text-muted-foreground line-clamp-1 mt-0.5' }, outage.description)
            ]);
        }
    }),
    columnHelper.accessor('status', {
        header: t('isp.outages.columns.status'),
        cell: info => h('div', { class: 'flex flex-col' }, [
            h(Badge, { variant: 'outline', class: 'capitalize w-fit' }, () => info.getValue()),
            h('span', { class: 'text-[10px] text-muted-foreground mt-0.5' }, info.row.original.type)
        ]),
    }),
    columnHelper.accessor('node.name', {
        header: t('isp.outages.columns.affected'),
        cell: info => info.getValue() || 'Global',
    }),
    columnHelper.display({
        id: 'duration',
        header: t('isp.outages.columns.duration'),
        cell: info => {
            const outage = info.row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'text-xs font-medium' }, formatDuration(outage)),
                h('span', { class: 'text-[10px] text-muted-foreground mt-0.5' }, dayjs(outage.started_at).format('MMM DD, HH:mm'))
            ]);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.outages.columns.actions')),
        cell: info => h('div', { class: 'flex justify-end gap-1' }, [
            h(Button, { variant: 'ghost', size: 'icon', onClick: () => editOutage(info.row.original) }, () => h(Pencil, { class: 'w-4 h-4' })),
            h(Button, { variant: 'ghost', size: 'icon', class: 'text-destructive', onClick: () => deleteOutage(info.row.original) }, () => h(Trash2, { class: 'w-4 h-4' })),
        ])
    })
];

const table = useVueTable({
    get data() { return outages.value },
    columns,
    state: { get rowSelection() { return rowSelection.value } },
    onRowSelectionChange: (updaterOrValue) => {
        rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
});

const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

const fetchOutages = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/outages');
        outages.value = ensureArray<Outage>(response.data.data);
    } catch (_error) {
        toast.error.default(t('common.messages.error_load'));
    } finally {
        loading.value = false;
    }
};

const fetchNodes = async () => {
    try {
        const response = await api.get('/admin/janet/isp/infra');
        nodes.value = response.data.data;
    } catch (_error) { /* ignored */ }
};

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    if (action === 'delete') {
        if (!confirm(t('isp.outages.modals.delete_confirm'))) return;
        try {
            await Promise.all(ids.map(id => api.delete(`/admin/janet/isp/outages/${id}`)));
            toast.success.default(t('isp.outages.messages.success_delete'));
            table.resetRowSelection();
            fetchOutages();
        } catch (_error) { toast.error.default(t('common.messages.error_delete')); }
    } else {
        try {
            await Promise.all(ids.map(id => api.patch(`/admin/janet/isp/outages/${id}`, { status: action })));
            toast.success.default(t('common.messages.success.action'));
            table.resetRowSelection();
            fetchOutages();
        } catch (_error) { toast.error.default(t('common.messages.error_update')); }
    }
    bulkActionSelection.value = '';
};

const openCreateModal = () => {
    editId.value = null;
    form.value = { title: '', description: '', type: 'Unscheduled', status: 'Investigating', node_id: 'null', started_at: dayjs().format('YYYY-MM-DDTHH:mm') };
    isDialogOpen.value = true;
};

const saveOutage = async () => {
    isSaving.value = true;
    try {
        const payload = { ...form.value, node_id: form.value.node_id === 'null' ? null : parseInt(form.value.node_id) };
        const response = editId.value ? await api.patch(`/admin/janet/isp/outages/${editId.value}`, payload) : await api.post('/admin/janet/isp/outages', payload);
        if (response.data.success) {
            toast.success.default(editId.value ? t('isp.outages.messages.success_update') : t('isp.outages.messages.success_report'));
            isDialogOpen.value = false;
            fetchOutages();
        }
    } catch (_error) { toast.error.default(t('isp.outages.messages.error_save')); } finally { isSaving.value = false; }
};

const editOutage = (outage: Outage) => {
    editId.value = outage.id;
    form.value = { title: outage.title, description: outage.description, type: outage.type, status: outage.status, node_id: outage.node_id ? outage.node_id.toString() : 'null', started_at: dayjs(outage.started_at).format('YYYY-MM-DDTHH:mm') };
    isDialogOpen.value = true;
};

const deleteOutage = async (outage: Outage) => {
    if (!confirm(t('isp.outages.modals.delete_confirm'))) return;
    try {
        await api.delete(`/admin/janet/isp/outages/${outage.id}`);
        toast.success.default(t('isp.outages.messages.success_delete'));
        fetchOutages();
    } catch (_error) { toast.error.default(t('common.messages.error_delete')); }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Resolved': return 'bg-success';
        case 'Monitoring': return 'bg-blue-500';
        case 'Identified': return 'bg-orange-500';
        default: return 'bg-destructive';
    }
};

const formatDuration = (outage: Outage) => {
    const end = outage.resolved_at ? dayjs(outage.resolved_at) : dayjs();
    const diff = end.diff(dayjs(outage.started_at), 'minute');
    if (diff < 60) return `${diff}m`;
    return `${Math.floor(diff / 60)}h ${diff % 60}m`;
};

onMounted(() => { fetchOutages(); fetchNodes(); });
</script>
