<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('isp.infra.nodes.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <router-link :to="{ name: 'isp.infra.map' }">
                    <Button variant="outline">
                        <Map class="w-4 h-4 mr-2" />
                        {{ t('isp.infra.map_title') }}
                    </Button>
                </router-link>
                <Button @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.infra.nodes.add_node') }}
                </Button>
            </div>
        </div>


        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.total') }}</p>
                        <p class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                    <Server class="w-8 h-8 text-primary opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border-l-success">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.active') }}</p>
                        <p class="text-2xl font-bold text-success">{{ stats.active }}</p>
                    </div>
                    <Activity class="w-8 h-8 text-success opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border-l-warning">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.maintenance') }}</p>
                        <p class="text-2xl font-bold text-warning">{{ stats.maintenance }}</p>
                    </div>
                    <Wrench class="w-8 h-8 text-warning opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border-l-destructive">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.offline') }}</p>
                        <p class="text-2xl font-bold text-destructive">{{ stats.inactive }}</p>
                    </div>
                    <AlertTriangle class="w-8 h-8 text-destructive opacity-20" />
                </div>
            </Card>
        </div>

        <!-- Main List -->
        <Card>
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="$t('isp.infra.nodes.fields.search_placeholder')" class="pl-9" />
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="typeFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue :placeholder="$t('isp.infra.nodes.types.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('isp.infra.nodes.types.all') }}</SelectItem>
                            <SelectItem value="OLT">{{ $t('isp.infra.nodes.types.OLT') }}</SelectItem>
                            <SelectItem value="POP">{{ $t('isp.infra.nodes.types.POP') }}</SelectItem>
                            <SelectItem value="Router">{{ $t('isp.infra.nodes.types.Router') }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue :placeholder="$t('isp.infra.nodes.status.all')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">{{ $t('isp.infra.nodes.status.all') }}</SelectItem>
                            <SelectItem value="active">{{ $t('isp.infra.nodes.status.active') }}</SelectItem>
                            <SelectItem value="maintenance">{{ $t('isp.infra.nodes.status.maintenance') }}</SelectItem>
                            <SelectItem value="inactive">{{ $t('isp.infra.nodes.status.inactive') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

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
                        {{ selectedRowsCount }} {{ t('isp.infra.nodes.title') }} Selected
                    </span>
                    <div class="h-4 w-px bg-border mx-2"></div>
                    <div class="flex items-center gap-2">
                        <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                            <SelectTrigger class="w-[160px] h-8 text-xs rounded-lg">
                                <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active" class="text-success focus:text-success">Set Active</SelectItem>
                                <SelectItem value="maintenance" class="text-warning focus:text-warning">Set Maintenance</SelectItem>
                                <SelectItem value="inactive" class="text-destructive focus:text-destructive">Set Offline</SelectItem>
                                <SelectItem value="delete" class="text-destructive focus:text-destructive">{{ t('common.actions.delete') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button variant="ghost" size="sm" class="h-8" @click="table.resetRowSelection()">
                            {{ t('common.actions.cancel') }}
                        </Button>
                    </div>
                </div>
            </Transition>

            <div class="overflow-x-auto">
                <DataTable 
                    :table="table" 
                    :loading="loading" 
                    :empty-message="t('isp.infra.messages.no_nodes')"
                />
            </div>
            
            <div class="p-4 border-t border-border/40" v-if="pagination">
                <Pagination
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="pagination.per_page"
                    @page-change="handlePageChange"
                />
            </div>
        </Card>

        <!-- Node Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ editingId ? $t('isp.infra.modals.edit_title') : $t('isp.infra.modals.create_title') }}</DialogTitle>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="name">{{ $t('isp.infra.nodes.fields.name') }}</Label>
                        <Input id="name" v-model="form.name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="type">{{ $t('isp.infra.nodes.fields.type') }}</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="OLT">OLT</SelectItem>
                                    <SelectItem value="POP">POP</SelectItem>
                                    <SelectItem value="Router">Router</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid gap-2">
                            <Label for="status">{{ $t('isp.infra.nodes.fields.status') }}</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">{{ $t('isp.infra.nodes.status.active') }}</SelectItem>
                                    <SelectItem value="maintenance">{{ $t('isp.infra.nodes.status.maintenance') }}</SelectItem>
                                    <SelectItem value="inactive">{{ $t('isp.infra.nodes.status.inactive') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="ip">{{ $t('isp.infra.nodes.fields.ip') }}</Label>
                        <Input id="ip" v-model="form.ip_address" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="lat">{{ $t('isp.infra.nodes.fields.lat') }}</Label>
                            <Input id="lat" type="number" step="0.000001" v-model="form.location_lat" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="lng">{{ $t('isp.infra.nodes.fields.lng') }}</Label>
                            <Input id="lng" type="number" step="0.000001" v-model="form.location_lng" />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="submit" @click="saveNode" :loading="saving">
                        {{ $t('isp.infra.modals.save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- View Node Details Modal -->
        <Dialog v-model:open="showViewModal">
            <DialogContent class="sm:max-w-[450px] rounded-2xl">
                <DialogHeader>
                    <div class="flex justify-between items-start mr-6">
                        <div>
                            <DialogTitle>{{ selectedNode?.name }}</DialogTitle>
                            <DialogDescription>
                                {{ selectedNode?.ip_address }}
                            </DialogDescription>
                        </div>
                        <Badge :variant="selectedNode ? getStatusVariant(selectedNode.status) : 'default'">
                            {{ selectedNode ? $t(`isp.infra.nodes.status.${selectedNode.status}`) : '' }}
                        </Badge>
                    </div>
                </DialogHeader>
                <div class="space-y-6 py-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="space-y-1">
                            <span class="text-muted-foreground">{{ $t('isp.infra.nodes.fields.type') }}</span>
                            <p class="font-medium">{{ selectedNode?.type }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-muted-foreground">{{ $t('isp.infra.nodes.fields.location') }}</span>
                            <p class="font-medium">{{ selectedNode?.location_lat }}, {{ selectedNode?.location_lng }}</p>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-border/40 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Capabilities</h4>
                        <div class="flex flex-wrap gap-2">
                            <Badge variant="secondary" v-if="selectedNode?.type === 'OLT'">L2 Switching</Badge>
                            <Badge variant="secondary" v-if="selectedNode?.type === 'Router'">L3 Routing</Badge>
                            <Badge variant="secondary" v-if="selectedNode?.type === 'POP'">Aggregation</Badge>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showViewModal = false" class="w-full rounded-xl">
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
    Button, Card, Input, Badge, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Pagination,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, Label, DataTable, Checkbox
} from '@/components/ui';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import { h } from 'vue';
import { useI18n } from 'vue-i18n';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Map from 'lucide-vue-next/dist/esm/icons/map.js';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Wrench from 'lucide-vue-next/dist/esm/icons/wrench.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import ArrowUp from 'lucide-vue-next/dist/esm/icons/arrow-up.js';
import ArrowDown from 'lucide-vue-next/dist/esm/icons/arrow-down.js';
import ArrowUpDown from 'lucide-vue-next/dist/esm/icons/arrow-up-down.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse, type PaginationData } from '@/utils/responseParser';
import type { NetworkNode } from '@/types/isp';

const toast = useToast();
const { confirm } = useConfirm();
const { t } = useI18n();

const loading = ref(false);
const nodes = ref<NetworkNode[]>([]);
const stats = ref({
    total: 0,
    active: 0,
    maintenance: 0,
    inactive: 0
});
const pagination = ref<PaginationData | null>(null);

const search = ref('');
const typeFilter = ref('all');
const statusFilter = ref('all');
const showModal = ref(false);
const showViewModal = ref(false);
const saving = ref(false);
const editingId = ref<number | null>(null);
const selectedNode = ref<NetworkNode | null>(null);
const rowSelection = ref({});
const sorting = ref([{ id: 'name', desc: false }]);
const bulkActionSelection = ref('');

const form = ref({
    name: '',
    type: 'OLT',
    ip_address: '',
    status: 'active',
    location_lat: 0,
    location_lng: 0
});

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<NetworkNode>();

const renderSortIcon = (isSorted: string | boolean) => {
    if (isSorted === 'asc') return ArrowUp;
    if (isSorted === 'desc') return ArrowDown;
    return ArrowUpDown;
};

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
    columnHelper.accessor('name', {
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 font-bold',
        }, () => [
            t('isp.infra.nodes.fields.name'),
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: info => h('span', { class: 'font-medium' }, info.getValue()),
    }),
    columnHelper.accessor('type', {
        header: t('isp.infra.nodes.fields.type'),
        cell: info => h(Badge, { variant: 'outline' }, () => info.getValue()),
    }),
    columnHelper.accessor('ip_address', {
        header: t('isp.infra.nodes.fields.ip'),
        cell: info => h('span', { class: 'font-mono text-xs' }, info.getValue()),
    }),
    columnHelper.accessor('status', {
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            size: 'sm',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            class: '-ml-3 h-8 font-bold',
        }, () => [
            t('isp.infra.nodes.fields.status'),
            h(renderSortIcon(column.getIsSorted()), { class: 'ml-2 h-4 w-4' })
        ]),
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()) }, () => t(`isp.infra.nodes.status.${info.getValue()}`)),
    }),
    columnHelper.display({
        id: 'location',
        header: t('isp.infra.nodes.fields.location'),
        cell: info => {
            const node = info.row.original;
            return h('span', { class: 'text-xs text-muted-foreground' }, `${node.location_lat}, ${node.location_lng}`);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.infra.nodes.fields.actions')),
        cell: info => {
            const node = info.row.original;
            return h('div', { class: 'flex justify-end gap-1' }, [
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); viewNode(node); },
                    'aria-label': t('isp.infra.nodes.fields.actions')
                }, () => h(Eye, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); editNode(node); },
                    'aria-label': t('isp.infra.nodes.fields.actions')
                }, () => h(Pencil, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'text-destructive',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); deleteNode(node); },
                    'aria-label': t('isp.infra.nodes.fields.actions')
                }, () => h(Trash2, { class: 'w-4 h-4' }))
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return nodes.value },
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
        onRowClick: (node: NetworkNode) => {
            viewNode(node);
        }
    }
});

const selectedRowsCount = computed(() => Object.keys(rowSelection.value).length);

const handleBulkAction = async (action: string) => {
    if (!action) return;
    const selectedRows = table.getSelectedRowModel().rows;
    const ids = selectedRows.map(r => r.original.id);
    
    if (action === 'delete') {
        const isConfirmed = await confirm({
            title: t('common.actions.delete'),
            message: t('common.messages.confirm.bulkDelete', { count: ids.length }),
            variant: 'danger'
        });
        if (isConfirmed) {
            try {
                await Promise.all(ids.map(id => api.delete(`/admin/ja/isp/infra/${id}`)));
                toast.success.action(t('isp.infra.messages.success_delete'));
                table.resetRowSelection();
                fetchNodes();
                fetchStats();
            } catch (error) {
                toast.error.action(error as Record<string, unknown>);
            }
        }
    } else {
        // Status update
        try {
            await Promise.all(ids.map(id => api.patch(`/admin/ja/isp/infra/${id}`, { status: action })));
            toast.success.action(t('isp.infra.messages.success_update'));
            table.resetRowSelection();
            fetchNodes();
            fetchStats();
        } catch (error) {
            toast.error.action(error as Record<string, unknown>);
        }
    }
    bulkActionSelection.value = '';
};

const openCreateModal = async () => {
    // Default values
    let lat = -6.2;
    let lng = 106.8;

    try {
        const res = await api.get('/settings/groups/isp_network');
        if (res.data.success && res.data.data) {
            lat = Number(res.data.data.network_map_default_lat) || lat;
            lng = Number(res.data.data.network_map_default_lng) || lng;
        }
    } catch (e) {
        console.warn('Failed to load network settings, using defaults');
    }

    editingId.value = null;
    form.value = {
        name: '',
        type: 'OLT',
        ip_address: '',
        status: 'active',
        location_lat: lat,
        location_lng: lng
    };
    showModal.value = true;
};

const fetchNodes = async (page = 1) => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = { page, per_page: 10 };
        if (search.value) params.search = search.value;
        if (typeFilter.value !== 'all') params.type = typeFilter.value;
        if (statusFilter.value !== 'all') params.status = statusFilter.value;

        const response = await api.get('/admin/ja/isp/infra', { params });
        const parsed = parseResponse(response);
        nodes.value = parsed.data as NetworkNode[];
        pagination.value = parsed.pagination;
    } catch (error: unknown) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/ja/isp/infra/stats');
        stats.value = response.data.data;
    } catch (_error) {
        // Log Error
    }
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active': return 'success';
        case 'maintenance': return 'warning';
        case 'inactive': return 'destructive';
        default: return 'secondary';
    }
};

const handlePageChange = (page: number) => {
    fetchNodes(page);
};

const viewNode = (node: NetworkNode) => {
    selectedNode.value = node;
    showViewModal.value = true;
};

const editNode = (node: NetworkNode) => {
    editingId.value = node.id;
    form.value = {
        name: node.name,
        type: node.type,
        ip_address: node.ip_address,
        status: node.status,
        location_lat: node.location_lat,
        location_lng: node.location_lng
    };
    showModal.value = true;
};

const saveNode = async () => {
    saving.value = true;
    try {
        if (editingId.value) {
            await api.patch(`/admin/ja/isp/infra/${editingId.value}`, form.value);
            toast.success.action(t('isp.infra.messages.success_update'));
        } else {
            await api.post('/admin/ja/isp/infra', form.value);
            toast.success.action(t('isp.infra.messages.success_create'));
        }
        showModal.value = false;
        fetchNodes();
        fetchStats();
    } catch (error) {
        toast.error.action(error as Record<string, unknown>);
    } finally {
        saving.value = false;
    }
};

const deleteNode = async (node: NetworkNode) => {
    const isConfirmed = await confirm({
        title: t('isp.infra.nodes.fields.actions'),
        message: t('isp.infra.modals.delete_confirm', { name: node.name }),
        variant: 'danger'
    });

    if (isConfirmed) {
        try {
            await api.delete(`/admin/ja/isp/infra/${node.id}`);
            toast.success.action(t('isp.infra.messages.success_delete'));
            fetchNodes(pagination.value?.current_page || 1);
            fetchStats();
        } catch (error) {
            toast.error.action(error as Record<string, unknown>);
        }
    }
};

watch([search, typeFilter, statusFilter], () => {
    fetchNodes(1);
});

onMounted(() => {
    fetchNodes();
    fetchStats();
});
</script>
