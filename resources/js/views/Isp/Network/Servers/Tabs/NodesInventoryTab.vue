<template>
    <div>
        <div class="mb-6 flex justify-end items-center">
            <div class="flex gap-2">
                <router-link :to="{ name: 'isp.infra.map' }">
                    <Button variant="outline" class="rounded-xl">
                        <Map class="w-4 h-4 mr-2" />
                        {{ t('isp.infra.map_title') }}
                    </Button>
                </router-link>
                <Button @click="openCreateModal" class="rounded-xl">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.infra.nodes.add_node') }}
                </Button>
            </div>
        </div>


        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card class="p-4 border border-border/40 shadow-sm rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.total') }}</p>
                        <p class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                    <Server class="w-8 h-8 text-primary opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-success rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.active') }}</p>
                        <p class="text-2xl font-bold text-success">{{ stats.active }}</p>
                    </div>
                    <Activity class="w-8 h-8 text-success opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-warning rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('isp.infra.nodes.stats.maintenance') }}</p>
                        <p class="text-2xl font-bold text-warning">{{ stats.maintenance }}</p>
                    </div>
                    <Wrench class="w-8 h-8 text-warning opacity-20" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-destructive rounded-xl shadow-sm">
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
        <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
            <div class="p-4 border-b border-border/40 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="relative w-full md:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="$t('isp.infra.nodes.fields.search_placeholder')" class="pl-9 rounded-xl" />
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <Select v-model="typeFilter">
                        <SelectTrigger class="w-[140px] rounded-xl">
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
                        <SelectTrigger class="w-[140px] rounded-xl">
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
                        {{ t('isp.infra.selected', { count: selectedRowsCount }) }}
                    </span>
                    <div class="h-4 w-px bg-border mx-2"></div>
                    <div class="flex items-center gap-2">
                        <Select v-model="bulkActionSelection" @update:model-value="handleBulkAction">
                            <SelectTrigger class="w-[160px] h-8 text-xs rounded-lg">
                                <SelectValue :placeholder="t('features.content.list.bulkActions')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active" class="text-success focus:text-success">{{ t('isp.infra.bulk_actions.active') }}</SelectItem>
                                <SelectItem value="maintenance" class="text-warning focus:text-warning">{{ t('isp.infra.bulk_actions.maintenance') }}</SelectItem>
                                <SelectItem value="inactive" class="text-destructive focus:text-destructive">{{ t('isp.infra.nodes.status.inactive') }}</SelectItem>
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

        <!-- Unified Node Form Modal -->
        <InfraFormModal 
            :is-open="showModal" 
            :router="selectedNode"
            @close="showModal = false"
            @refresh="() => { fetchNodes(); fetchStats(); }"
        />

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
                        <Badge :variant="selectedNode ? getStatusVariant(selectedNode.status) : 'default'" class="rounded-xl">
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
                            <span class="text-muted-foreground">{{ $t('common.labels.description') }}</span>
                            <p class="font-medium">{{ selectedNode?.description || '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-border/40 space-y-3">
                        <h4 class="text-xs font-bold text-muted-foreground">{{ t('isp.infra.capabilities') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            <Badge variant="secondary" v-if="selectedNode?.type === 'OLT'" class="rounded-xl">L2 Switching</Badge>
                            <Badge variant="secondary" v-if="selectedNode?.type === 'Router'" class="rounded-xl">L3 Routing</Badge>
                            <Badge variant="secondary" v-if="selectedNode?.type === 'POP'" class="rounded-xl">Aggregation</Badge>
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
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DataTable, Checkbox
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
import InfraFormModal from '../Modals/InfraFormModal.vue';

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
const editingId = ref<number | null>(null);
const selectedNode = ref<NetworkNode | null>(null);
const rowSelection = ref({});
const sorting = ref([{ id: 'name', desc: false }]);
const bulkActionSelection = ref('');

interface NodeForm {
    name: string;
    description: string;
    type: 'Router' | 'OLT' | 'POP';
    ip_address: string;
    status: string;
    location_lat: number;
    location_lng: number;
    is_vpn_server: boolean;
}

const form = ref<NodeForm>({
    name: '',
    description: '',
    type: 'OLT',
    ip_address: '',
    status: 'active',
    location_lat: 0,
    location_lng: 0,
    is_vpn_server: false
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
        cell: info => h(Badge, { variant: 'outline', class: 'rounded-xl' }, () => info.getValue()),
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
        cell: info => h(Badge, { variant: getStatusVariant(info.getValue()), class: 'rounded-xl' }, () => t(`isp.infra.nodes.status.${info.getValue()}`)),
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
                    class: 'rounded-xl',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); viewNode(node); },
                    'aria-label': t('isp.infra.nodes.fields.actions')
                }, () => h(Eye, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'rounded-xl',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); editNode(node); },
                    'aria-label': t('isp.infra.nodes.fields.actions')
                }, () => h(Pencil, { class: 'w-4 h-4' })),
                h(Button, {
                    variant: 'ghost',
                    size: 'icon',
                    class: 'text-destructive rounded-xl',
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
                await Promise.all(ids.map(id => api.delete(`/admin/janet/isp/infra/${id}`)));
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
            await Promise.all(ids.map(id => api.patch(`/admin/janet/isp/infra/${id}`, { status: action })));
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
    } catch (_e) {
        console.warn('Failed to load network settings, using defaults');
    }

    editingId.value = null;
    form.value = {
        name: '',
        description: '',
        type: 'OLT',
        ip_address: '',
        status: 'active',
        location_lat: lat,
        location_lng: lng,
        is_vpn_server: false
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

        const response = await api.get('/admin/janet/isp/infra', { params });
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
        const response = await api.get('/admin/janet/isp/infra/stats');
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
    selectedNode.value = node;
    showModal.value = true;
};

// Local saveNode removed as it's now handled by RouterFormModal

const deleteNode = async (node: NetworkNode) => {
    const isConfirmed = await confirm({
        title: t('isp.infra.nodes.fields.actions'),
        message: t('isp.infra.modals.delete_confirm', { name: node.name }),
        variant: 'danger'
    });

    if (isConfirmed) {
        try {
            await api.delete(`/admin/janet/isp/infra/${node.id}`);
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
