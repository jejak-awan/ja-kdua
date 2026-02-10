<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('isp.monitor.active_sessions.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('isp.monitor.active_sessions.description') }}</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <Select v-model="selectedRouterId">
                    <SelectTrigger class="w-full md:w-[280px] bg-background/50 backdrop-blur-sm">
                        <SelectValue :placeholder="t('isp.monitor.active_sessions.select_router')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="router in routers" :key="router.id" :value="String(router.id)">
                            {{ router.name }} ({{ router.ip_address }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button 
                    variant="outline" 
                    size="icon" 
                    @click="fetchSessions" 
                    :disabled="!selectedRouterId || loading"
                    class="shrink-0"
                >
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <Card class="overflow-hidden border-none shadow-lg bg-card/40 backdrop-blur-md">
            <div v-if="!selectedRouterId" class="p-24 text-center space-y-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-primary/5 border border-primary/10 shadow-inner">
                    <Activity class="w-10 h-10 text-primary/30" />
                </div>
                <div class="max-w-[300px] mx-auto">
                    <h3 class="text-lg font-medium text-foreground/80 mb-1">Pilih Router</h3>
                    <p class="text-sm text-muted-foreground">{{ t('isp.monitor.active_sessions.messages.select_router_first') }}</p>
                </div>
            </div>

            <div v-else class="p-0">
                <DataTable
                    :table="table"
                    :loading="loading"
                    :empty-message="t('isp.monitor.active_sessions.messages.no_sessions')"
                />
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    Card, 
    Badge, 
    Button, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue,
    DataTable
} from '@/components/ui';

import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState
} from '@tanstack/vue-table';

import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const toast = useToast();

interface Router {
    id: number;
    name: string;
    ip_address: string;
}

interface Session {
    id: string;
    name: string;
    address: string;
    uptime: string;
    type: string;
    service?: string;
    caller_id?: string;
}

const routers = ref<Router[]>([]);
const selectedRouterId = ref<string>('');
const sessions = ref<Session[]>([]);
const loading = ref(false);
const disconnecting = ref<string | null>(null);

const columnHelper = createColumnHelper<Session>();

const columns = [
    columnHelper.accessor('name', {
        header: t('isp.monitor.active_sessions.table.customer'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-3' }, [
            h('div', { class: 'w-10 h-10 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform' }, [
                h(User, { class: 'w-5 h-5 font-bold' })
            ]),
            h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-semibold text-foreground tracking-tight' }, row.original.name),
                h('span', { class: 'text-[10px] text-muted-foreground font-mono uppercase tracking-widest opacity-60' }, row.original.service || 'Default')
            ])
        ])
    }),
    columnHelper.accessor('address', {
        header: t('isp.monitor.active_sessions.table.ip_address'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-mono text-[11px] text-foreground/80' }, [
            h(Globe, { class: 'w-3.5 h-3.5 text-primary/40' }),
            row.original.address
        ])
    }),
    columnHelper.accessor('uptime', {
        header: t('isp.monitor.active_sessions.table.uptime'),
        cell: ({ row }) => h('div', { class: 'flex items-center gap-2 text-xs font-medium text-muted-foreground' }, [
            h(Clock, { class: 'w-3.5 h-3.5 opacity-50' }),
            row.original.uptime
        ])
    }),
    columnHelper.accessor('type', {
        header: t('isp.monitor.active_sessions.table.type'),
        cell: ({ row }) => h(Badge, {
            variant: row.original.type === 'pppoe' ? 'default' : 'secondary',
            class: 'text-[9px] font-bold uppercase tracking-tighter px-2 py-0.5 rounded-md'
        }, row.original.type)
    }),
    columnHelper.accessor('caller_id', {
        header: t('isp.monitor.active_sessions.table.caller_id'),
        cell: ({ row }) => h('span', { class: 'font-mono text-[10px] text-muted-foreground/70 bg-muted/20 px-1.5 py-0.5 rounded' }, row.original.caller_id || 'N/A')
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('isp.monitor.active_sessions.table.actions')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Button, {
                variant: 'ghost',
                size: 'sm',
                class: 'h-8 w-8 p-0 text-destructive/70 hover:text-destructive hover:bg-destructive/10 rounded-full transition-all group-hover:scale-110',
                onClick: () => disconnect(row.original),
                loading: disconnecting.value === row.original.id
            }, [
                h(Trash2, { class: 'w-4 h-4' })
            ])
        ])
    })
];

const sorting = ref<SortingState>([]);

const table = useVueTable({
    get data() { return sessions.value },
    columns,
    state: {
        get sorting() { return sorting.value },
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => row.id,
});

const fetchRouters = async () => {
    try {
        const res = await api.get('/admin/ja/isp/routers');
        routers.value = res.data.data || [];
    } catch (_e) {
        toast.error.default('Failed to load routers');
    }
};

const fetchSessions = async () => {
    if (!selectedRouterId.value) return;
    
    loading.value = true;
    try {
        const res = await api.get(`/admin/ja/isp/monitor/sessions?router_id=${selectedRouterId.value}`);
        sessions.value = res.data.data || [];
    } catch (_e) {
        toast.error.default('Failed to fetch active sessions');
    } finally {
        loading.value = false;
    }
};

const disconnect = async (session: Session) => {
    if (!confirm(t('isp.monitor.active_sessions.messages.disconnect_confirm'))) return;

    disconnecting.value = session.id;
    try {
        await api.post('/admin/ja/isp/monitor/disconnect', {
            router_id: Number(selectedRouterId.value),
            type: session.type,
            id: session.id
        });
        toast.success.default(t('isp.monitor.active_sessions.messages.success_disconnect'));
        fetchSessions();
    } catch (_e) {
        toast.error.default(t('isp.monitor.active_sessions.messages.error_disconnect'));
    } finally {
        disconnecting.value = null;
    }
};

watch(selectedRouterId, () => {
    sessions.value = [];
    fetchSessions();
});

onMounted(() => {
    fetchRouters();
});
</script>
