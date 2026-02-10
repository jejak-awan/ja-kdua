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
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Edit from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Network from 'lucide-vue-next/dist/esm/icons/network.js';
import api from '@/services/api';
import type { NetworkNode } from '@/types/isp';
import { 
    Card, CardHeader, CardContent,
    Input,
    Button,
    Badge,
    DataTable,
    Pagination
} from '@/components/ui';
import { ensureArray } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import DataServerModal from '../DataServerModal.vue';
import RouterFormModal from '../RouterFormModal.vue';
import StaticRoutingModal from '../StaticRoutingModal.vue';
import MikrotikScriptModal from '../MikrotikScriptModal.vue';

const { t } = useI18n();
const { success, error: toastError } = useToast();

// --- State ---
const routers = ref<NetworkNode[]>([]);
const totalRows = ref(0);
const isLoading = ref(false);
const search = ref('');
const page = ref(1);
const perPage = ref(15);
const showDataServerModal = ref(false);
const showRouterFormModal = ref(false);
const showStaticRoutingModal = ref(false);
const showScriptModal = ref(false);
const selectedRouter = ref<NetworkNode | null>(null);
const isDeleting = ref(false);

// --- Fetch Logic ---
async function fetchRouters() {
    isLoading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/routers', { 
            params: { 
                page: page.value, 
                per_page: perPage.value,
                search: search.value 
            } 
        });
        routers.value = ensureArray(response.data.data);
        totalRows.value = response.data.total;
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoading.value = false;
    }
}

// --- TanStack Table Setup ---
const columnHelper = createColumnHelper<NetworkNode>();

const columns = [
    columnHelper.accessor('name', {
        header: t('isp.network.router.fields.name'),
        cell: info => h('span', { class: 'font-medium' }, info.getValue())
    }),
    columnHelper.accessor('ip_address', {
        header: t('isp.network.router.fields.ip'),
        cell: info => h('span', { class: 'font-mono text-xs' }, info.getValue())
    }),
    columnHelper.accessor('secret', {
        header: t('isp.network.router.fields.secret'),
        cell: info => info.getValue() || '-'
    }),
    columnHelper.accessor('connection_type', {
        header: t('isp.network.router.fields.connection_type'),
        cell: info => h(Badge, { variant: 'outline' }, { default: () => info.getValue() })
    }),
    columnHelper.accessor('last_active_count', {
        header: t('isp.network.router.fields.online'),
        cell: info => {
            const count = info.getValue() || 0;
            const router = info.row.original;
            const isOnline = router.status === 'active';
            return h('div', { class: 'flex items-center gap-2' }, [
                h('div', { class: `h-2 w-2 rounded-full ${isOnline ? 'bg-green-500' : 'bg-red-500'}` }),
                h('span', { class: 'font-bold text-green-600' }, count)
            ]);
        }
    }),
    columnHelper.display({
        id: 'snmp_status',
        header: t('isp.network.data_server.connectivity.title'),
        cell: info => {
            const router = info.row.original;
            const isConnected = router.is_connected;
            return h(Badge, { 
                class: isConnected 
                    ? 'bg-green-600 hover:bg-green-700 text-white border-none px-3 py-1 rounded-md text-[10px] font-bold' 
                    : 'bg-red-500 hover:bg-red-600 text-white border-none px-3 py-1 rounded-md text-[10px] font-bold'
                }, 
                { default: () => isConnected ? t('isp.network.data_server.connectivity.connected') : t('isp.network.data_server.connectivity.disconnected') }
            );
        }
    }),
    columnHelper.display({
        id: 'actions',
        cell: info => {
            const router = info.row.original;
            return h('div', { class: 'flex items-center justify-end gap-1' }, [
                h(Button, { 
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); editRouter(router); }
                }, { default: () => h(Edit, { class: 'h-4 w-4' }) }),
                h(Button, { 
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); downloadScript(router); }
                }, { default: () => h(Download, { class: 'h-4 w-4' }) }),
                h(Button, { 
                    variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-red-500 hover:text-red-600',
                    onClick: (e: MouseEvent) => { e.stopPropagation(); deleteRouter(router.id); }
                }, { default: () => h(Trash2, { class: 'h-4 w-4' }) })
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return routers.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

// --- Actions ---
async function deleteRouter(id: number) {
    if (!confirm(t('common.messages.confirm_delete'))) return;
    
    isDeleting.value = true;
    try {
        await api.delete(`/admin/ja/isp/routers/${id}`);
        success.action(t('isp.network.router.messages.success_delete'));
        await fetchRouters();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isDeleting.value = false;
    }
}

function editRouter(router: NetworkNode) {
    selectedRouter.value = router;
    showRouterFormModal.value = true;
}

function downloadScript(router: NetworkNode) {
    selectedRouter.value = router;
    showScriptModal.value = true;
}

function openAddModal() {
    selectedRouter.value = null;
    showRouterFormModal.value = true;
}

const handlePageChange = (p: number) => {
    page.value = p;
};

onMounted(fetchRouters);

watch([page, search], fetchRouters);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-50 uppercase">
                    {{ t('isp.network.router.title') }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400">
                    {{ t('isp.network.router.subtitle') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="showDataServerModal = true">
                    <Server class="mr-2 h-4 w-4" />
                    {{ t('isp.network.router.server') }}
                </Button>
                <Button variant="outline" size="sm" class="bg-indigo-50 text-indigo-700 border-indigo-200" @click="showStaticRoutingModal = true">
                    <Network class="mr-2 h-4 w-4" />
                    {{ t('isp.network.router.routing') }}
                </Button>
                <Button size="sm" @click="openAddModal" class="bg-primary hover:bg-primary/90 rounded-xl">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('isp.network.router.add') }}
                </Button>
            </div>
        </div>

        <Card class="border-none shadow-sm overflow-hidden rounded-2xl">
            <CardHeader class="pb-3 border-b border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative max-w-sm w-full">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <Input 
                            v-model="search" 
                            :placeholder="t('isp.network.router.search_placeholder')" 
                            class="pl-10 bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-700 rounded-xl"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <DataTable 
                        :table="table" 
                        :loading="isLoading"
                    />
                </div>
            </CardContent>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                <Pagination
                    :current-page="page"
                    :per-page="perPage"
                    :total-items="totalRows"
                    @page-change="handlePageChange"
                />
            </div>
        </Card>

        <DataServerModal 
            v-if="showDataServerModal" 
            :is-open="showDataServerModal" 
            @close="showDataServerModal = false" 
            @refresh="fetchRouters"
        />

        <RouterFormModal 
            v-if="showRouterFormModal"
            :is-open="showRouterFormModal"
            :router="selectedRouter"
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
