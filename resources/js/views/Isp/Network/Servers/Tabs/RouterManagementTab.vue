<script setup lang="ts">
import { ref, h, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Edit from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import RefreshCcw from 'lucide-vue-next/dist/esm/icons/refresh-ccw.js';
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import api from '@/services/api';
import type { NetworkNode } from '@/types/isp';
import { 
    Input,
    Button,
    Badge,
    DataTable,
    Pagination,
    Tooltip, TooltipContent, TooltipProvider, TooltipTrigger,
    Checkbox,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
} from '@/components/ui';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import InfraFormModal from '../Modals/InfraFormModal.vue';
import StaticRoutingModal from '../Modals/StaticRoutingModal.vue';
import MikrotikScriptModal from '../Modals/MikrotikScriptModal.vue';

const { t } = useI18n();
const { success, error: toastError } = useToast();
const { confirm } = useConfirm();

// --- State ---
const routers = ref<NetworkNode[]>([]);
const totalRows = ref(0);
const isLoading = ref(false);
const search = ref('');
const page = ref(1);
const perPage = ref(15);
const showRouterFormModal = ref(false);
const showStaticRoutingModal = ref(false);
const showScriptModal = ref(false);
const selectedRouter = ref<NetworkNode | null>(null);
const modalInitialTab = ref('general');
const isDeleting = ref(false);
const isRestoring = ref(false);
const rowSelection = ref({});
const activeTab = ref('all');

// --- Fetch Logic ---
async function fetchRouters() {
    isLoading.value = true;
    try {
        let url = '/admin/janet/isp/infra';
        const params: Record<string, string | number> = {
            type: 'Router',
            page: page.value,
            per_page: perPage.value,
            search: search.value
        };

        if (activeTab.value === 'trash') {
            url = '/admin/janet/isp/routers/trash';
        } else if (activeTab.value !== 'all') {
            params.status = activeTab.value;
        }

        const response = await api.get(url, { params });
        const { data, pagination } = parseResponse<NetworkNode>(response);
        routers.value = data;
        totalRows.value = pagination?.total || 0;
        rowSelection.value = {}; // Reset selection on fetch
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<NetworkNode>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected(),
            'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            ariaLabel: 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (value: boolean) => row.toggleSelected(!!value),
            ariaLabel: 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    }),
    columnHelper.accessor('pretty_id', {
        header: t('isp.infra.router.fields.pretty_id'),
        cell: info => h('span', { class: 'font-mono text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-500/20' }, info.getValue())
    }),
    columnHelper.accessor('name', {
        header: t('isp.infra.nodes.fields.name'),
        cell: info => h('span', { class: 'font-medium' }, info.getValue())
    }),
    columnHelper.accessor('description', {
        header: t('common.labels.description'),
        cell: info => {
            const val = info.getValue();
            if (!val) return '-';
            const presets = ['Core Router', 'Router Gateway', 'Distribution Router'];
            const colors: Record<string, string> = {
                'Core Router': 'bg-indigo-100 text-indigo-700 border-indigo-200',
                'Router Gateway': 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'Distribution Router': 'bg-amber-100 text-amber-700 border-amber-200'
            };
            
            if (presets.includes(val)) {
                const presetKey = val.toLowerCase().split(' ')[0];
                return h(Badge, { variant: 'outline', class: `capitalize ${colors[val] || ''}` }, { default: () => t(`isp.infra.router.presets.${presetKey}`) });
            }
            return h('span', { class: 'text-xs text-slate-500 italic' }, val);
        }
    }),
    columnHelper.accessor('ip_address', {
        header: t('isp.infra.nodes.fields.ip'),
        cell: info => h('span', { class: 'font-mono text-xs' }, info.getValue())
    }),
    columnHelper.accessor('ip_type', {
        header: t('isp.infra.router.fields.ip_type'),
        cell: info => {
            const val = info.getValue();
            const colors: Record<string, string> = {
                'Public Static': 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                'Local': 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                'VPN': 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20'
            };
            return h(Badge, { variant: 'outline', class: `text-[10px] font-bold rounded-xl ${colors[val] || ''}` }, { default: () => val });
        }
    }),
    columnHelper.display({
        id: 'connectivity',
        header: t('isp.infra.router.fields.online'),
        cell: info => {
            const router = info.row.original;
            const isConnected = router.is_connected;
            return h('div', { class: 'flex items-center gap-2' }, [
                h('div', { class: `h-2 w-2 rounded-full ${isConnected ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]' : 'bg-red-500'}` }),
                h('span', { class: `text-[10px] font-bold ${isConnected ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}` }, isConnected ? t('isp.infra.router.status_online') : t('isp.infra.router.status_offline'))
            ]);
        }
    }),
    columnHelper.display({
        id: 'actions',
        cell: info => {
            const router = info.row.original;
            const isTrash = activeTab.value === 'trash';
            const isActive = router.status === 'active';

            const actions = [];

            if (isTrash) {
                // Restore Action
                actions.push(
                    h(Tooltip, {}, {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, {
                                default: () => h(Button, { 
                                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-green-600',
                                    onClick: (e: MouseEvent) => { e.stopPropagation(); restoreRouter(router); }
                                }, [h(RefreshCcw, { class: 'h-4 w-4' })])
                            }),
                            h(TooltipContent, {}, { default: () => t('isp.common.actions.restore') })
                        ]
                    })
                );
                // Force Delete Action
                actions.push(
                    h(Tooltip, {}, {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, {
                                default: () => h(Button, { 
                                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-red-600',
                                    onClick: (e: MouseEvent) => { e.stopPropagation(); deleteRouter(router); }
                                }, [h(Trash2, { class: 'h-4 w-4' })])
                            }),
                            h(TooltipContent, {}, { default: () => t('isp.common.actions.delete_permanently') })
                        ]
                    })
                );
            } else {
                // 1. Status Toggle (Switch Style)
                actions.push(
                    h('div', { 
                        class: 'flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-slate-50 cursor-pointer group transition-all mr-2',
                        onClick: (e: MouseEvent) => { e.stopPropagation(); toggleStatus(router); }
                    }, [
                        h('div', { 
                            class: `w-9 h-5 rounded-full relative transition-colors duration-200 flex items-center ${isActive ? 'bg-blue-600' : 'bg-slate-300'}`
                        }, [
                            h('div', { 
                                class: `w-3.5 h-3.5 bg-white rounded-full shadow-sm transition-all duration-200 transform ${isActive ? 'translate-x-[1.125rem]' : 'translate-x-[0.125rem]'}`
                            })
                        ]),
                        h('span', { class: `text-[10px] font-bold ${isActive ? 'text-blue-600' : 'text-slate-400'}` }, isActive ? t('isp.common.status.active') : t('isp.common.status.inactive'))
                    ])
                );

                // 2. Download Script
                actions.push(
                    h(Tooltip, {}, {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, {
                                default: () => h(Button, { 
                                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-indigo-600 hover:bg-indigo-50',
                                    onClick: (e: MouseEvent) => { e.stopPropagation(); downloadScript(router); }
                                }, [h(FileCode, { class: 'h-4 w-4' })])
                            }),
                            h(TooltipContent, {}, { default: () => t('isp.common.actions.download_config') })
                        ]
                    })
                );

                // 3. Delete (Conditional)
                actions.push(
                    h(Tooltip, {}, {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, {
                                default: () => h(Button, { 
                                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-red-500 hover:bg-red-50 disabled:opacity-20',
                                    disabled: isActive,
                                    onClick: (e: MouseEvent) => { e.stopPropagation(); deleteRouter(router); }
                                }, [h(Trash2, { class: 'h-4 w-4' })])
                            }),
                            h(TooltipContent, {}, { default: () => isActive ? t('isp.common.actions.deactivate') : t('common.actions.delete') })
                        ]
                    })
                );

                // 4. Edit (Fallback)
                actions.push(
                    h(Tooltip, {}, {
                        default: () => [
                            h(TooltipTrigger, { asChild: true }, {
                                default: () => h(Button, { 
                                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-slate-400 hover:text-blue-600 hover:bg-blue-50',
                                    onClick: (e: MouseEvent) => { e.stopPropagation(); editRouter(router); }
                                }, [h(Edit, { class: 'h-4 w-4' })])
                            }),
                            h(TooltipContent, {}, { default: () => t('common.actions.edit') })
                        ]
                    })
                );
            }

            return h('div', { class: 'flex items-center justify-end gap-0.5' }, [
                h(TooltipProvider, {}, actions)
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return routers.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
    enableRowSelection: true,
    onRowSelectionChange: updaterOrValue => {
        if (typeof updaterOrValue === 'function') {
            rowSelection.value = updaterOrValue(rowSelection.value);
        } else {
            rowSelection.value = updaterOrValue;
        }
    },
    state: {
        get rowSelection() { return rowSelection.value },
    },
});

// --- Actions ---
async function handleBulkDelete() {
    const selectedIds = Object.keys(rowSelection.value).map(index => routers.value[parseInt(index)].id);
    if (!selectedIds.length) return;

    const isConfirmed = await confirm({
        title: t('common.actions.delete'),
        message: t('common.messages.confirm.bulkDelete', { count: selectedIds.length }),
        variant: 'danger'
    });

    if (!isConfirmed) return;

    isDeleting.value = true;
    try {
        await api.post('/admin/janet/isp/routers/bulk-destroy', { ids: selectedIds });
        success.action(t('common.messages.success.deleted', { item: selectedIds.length + ' routers' }));
        await fetchRouters();
        rowSelection.value = {};
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isDeleting.value = false;
    }
}
async function deleteRouter(router: NetworkNode) {
    // If in trash, this is force delete
    const isForce = activeTab.value === 'trash';
    const endpoint = isForce ? `/admin/janet/isp/routers/${router.id}/force` : `/admin/janet/isp/routers/${router.id}`;

    const isConfirmed = await confirm({
        title: isForce ? t('common.actions.delete_permanently') : t('common.actions.delete'),
        message: isForce 
            ? t('common.messages.confirm.delete_permanently', { item: router.name })
            : t('common.messages.confirm.delete', { item: router.name }),
        variant: 'danger'
    });

    if (!isConfirmed) return;
    
    isDeleting.value = true;
    try {
        await api.delete(endpoint);
        success.action(isForce ? t('isp.infra.messages.success_delete') : t('isp.infra.messages.success_delete'));
        await fetchRouters();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isDeleting.value = false;
    }
}

async function restoreRouter(router: NetworkNode) {
    const isConfirmed = await confirm({
        title: 'Restore Router',
        message: `Are you sure you want to restore ${router.name}?`,
        variant: 'info'
    });

    if (!isConfirmed) return;

    isRestoring.value = true;
    try {
        await api.post(`/admin/janet/isp/routers/${router.id}/restore`);
        success.action(t('isp.common.messages.success_restore'));
        await fetchRouters();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isRestoring.value = false;
    }
}

async function toggleStatus(router: NetworkNode) {
    const newStatus = router.status === 'active' ? 'inactive' : 'active';
    try {
        await api.put(`/admin/janet/isp/routers/${router.id}`, { status: newStatus });
        success.action(t('common.messages.success.saved'));
        await fetchRouters();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    }
}

async function handleBulkRestore() {
    const selectedIds = Object.keys(rowSelection.value).map(index => routers.value[parseInt(index)].id);
    if (!selectedIds.length) return;

    const isConfirmed = await confirm({
        title: 'Bulk Restore',
        message: `Are you sure you want to restore ${selectedIds.length} routers?`,
        variant: 'info'
    });

    if (!isConfirmed) return;

    isRestoring.value = true;
    try {
        await api.post('/admin/janet/isp/routers/bulk-restore', { ids: selectedIds });
        success.action(t('isp.common.messages.success_restore'));
        await fetchRouters();
        rowSelection.value = {};
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isRestoring.value = false;
    }
}

async function handleBulkForceDelete() {
    const selectedIds = Object.keys(rowSelection.value).map(index => routers.value[parseInt(index)].id);
    if (!selectedIds.length) return;

    const isConfirmed = await confirm({
        title: 'Delete Permanently',
        message: `Are you sure you want to PERMANENTLY delete ${selectedIds.length} routers? This logic cannot be undone.`,
        variant: 'danger'
    });

    if (!isConfirmed) return;

    isDeleting.value = true;
    try {
        await api.post('/admin/janet/isp/routers/bulk-force-destroy', { ids: selectedIds });
        success.action(t('isp.infra.messages.success_delete'));
        await fetchRouters();
        rowSelection.value = {};
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isDeleting.value = false;
    }
}

async function handleBulkStatus(status: 'active' | 'inactive') {
    const selectedIds = Object.keys(rowSelection.value).map(index => routers.value[parseInt(index)].id);
    if (!selectedIds.length) return;

    const isConfirmed = await confirm({
        title: status === 'active' ? 'Activate Routers' : 'Deactivate Routers',
        message: `Are you sure you want to set ${selectedIds.length} routers to ${status}?`,
        variant: status === 'active' ? 'info' : 'warning'
    });

    if (!isConfirmed) return;

    isLoading.value = true;
    try {
        await api.post('/admin/janet/isp/routers/bulk-status', { ids: selectedIds, status });
        success.action(t('common.messages.success.saved'));
        await fetchRouters();
        rowSelection.value = {};
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

function editRouter(router: NetworkNode, tab: string = 'general') {
    selectedRouter.value = router;
    modalInitialTab.value = tab;
    showRouterFormModal.value = true;
}

function downloadScript(router: NetworkNode) {
    selectedRouter.value = router;
    showScriptModal.value = true;
}

function openAddModal() {
    selectedRouter.value = null;
    modalInitialTab.value = 'general';
    showRouterFormModal.value = true;
}

const handlePageChange = (p: number) => {
    page.value = p;
};

onMounted(fetchRouters);

// Watchers
watch(page, fetchRouters);
watch([activeTab, search], () => {
    if (page.value === 1) {
        fetchRouters();
    } else {
        page.value = 1;
    }
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <Select v-model="activeTab">
                    <SelectTrigger class="w-[180px] h-10 rounded-xl bg-muted border-none shadow-none focus:ring-0">
                        <SelectValue :placeholder="t('isp.infra.nodes.fields.status')" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl border-none shadow-xl">
                        <SelectItem value="all">{{ t('common.filters.all') }}</SelectItem>
                        <SelectItem value="active">{{ t('isp.infra.nodes.status.active') }}</SelectItem>
                        <SelectItem value="inactive">{{ t('isp.infra.nodes.status.inactive') }}</SelectItem>
                        <SelectItem value="trash">{{ t('isp.common.status.trash') }}</SelectItem>
                    </SelectContent>
                </Select>

                <div class="relative w-full md:w-72 group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground group-focus-within:text-blue-500 transition-colors" />
                    <Input 
                        v-model="search" 
                        :placeholder="t('common.actions.search')" 
                        class="pl-10 h-10 rounded-xl bg-muted border-none focus:ring-1 focus:ring-blue-500 transition-all text-sm"
                    />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div v-if="Object.keys(rowSelection).length > 0" class="flex items-center gap-2 mr-2 animate-in fade-in slide-in-from-right-4">
                    <template v-if="activeTab === 'trash'">
                        <Button size="sm" variant="outline" @click="handleBulkRestore" :disabled="isRestoring" class="h-10 rounded-xl bg-green-50 text-green-600 border-green-100 hover:bg-green-100">
                            <RefreshCcw class="h-4 w-4 mr-2" />
                            {{ t('isp.common.actions.restore') }}
                        </Button>
                        <Button size="sm" variant="destructive" @click="handleBulkForceDelete" :disabled="isDeleting" class="h-10 rounded-xl shadow-sm">
                            <Trash2 class="h-4 w-4 mr-2" />
                            {{ t('isp.common.actions.delete_permanently') }}
                        </Button>
                    </template>
                    <template v-else>
                        <Button size="sm" variant="outline" @click="handleBulkStatus('active')" class="h-10 rounded-xl bg-green-50 text-green-600 border-green-100 hover:bg-green-100">
                            <CheckCircle class="h-4 w-4 mr-2" />
                            {{ t('isp.common.actions.activate') }}
                        </Button>
                        <Button size="sm" variant="outline" @click="handleBulkStatus('inactive')" class="h-10 rounded-xl bg-amber-50 text-amber-600 border-amber-100 hover:bg-amber-100">
                            <Ban class="h-4 w-4 mr-2" />
                            {{ t('isp.common.actions.deactivate') }}
                        </Button>
                        <Button size="sm" variant="destructive" @click="handleBulkDelete" :disabled="isDeleting" class="h-10 rounded-xl shadow-sm">
                            <Trash2 class="h-4 w-4 mr-2" />
                            {{ t('isp.common.actions.to_trash') }}
                        </Button>
                    </template>
                </div>

                
                <Button @click="openAddModal" class="h-10 bg-primary hover:bg-primary/90 text-primary-foreground rounded-xl px-4 shadow-sm transition-all font-bold tracking-wide">
                    <Plus class="h-4 w-4 mr-2" />
                    {{ t('isp.infra.router.add') }}
                </Button>
            </div>
        </div>

        <div class="border border-border/40 rounded-xl overflow-hidden bg-card shadow-sm">
            <DataTable 
                :table="table" 
                :loading="isLoading"
                class="border-none"
            />
        </div>

        <div class="p-4 border border-border/40 rounded-xl bg-card shadow-sm">
            <Pagination
                :current-page="page"
                :per-page="perPage"
                :total-items="totalRows"
                @page-change="handlePageChange"
            />
        </div>

        <InfraFormModal 
            v-if="showRouterFormModal"
            :is-open="showRouterFormModal"
            :router="selectedRouter"
            :initial-tab="modalInitialTab"
            @close="showRouterFormModal = false"
            @refresh="fetchRouters"
        />

        <StaticRoutingModal
            v-if="showStaticRoutingModal"
            :is-open="showStaticRoutingModal"
            @close="showStaticRoutingModal = false"
        />

        <MikrotikScriptModal
            v-if="showScriptModal"
            :is-open="showScriptModal"
            :router="selectedRouter"
            @close="showScriptModal = false"
        />
    </div>
</template>
