<script setup lang="ts">
import { ref, h, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
} from '@tanstack/vue-table';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import History from 'lucide-vue-next/dist/esm/icons/history.js';
import Wifi from 'lucide-vue-next/dist/esm/icons/wifi.js';
import UserX from 'lucide-vue-next/dist/esm/icons/user-x.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import api from '@/services/api';
import { 
    Input,
    Button,
    DataTable,
    Card,
    CardHeader,
    CardContent,
    CardTitle,
    Switch,
    Tabs, TabsList, TabsTrigger, TabsContent,
    Badge,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
    Label
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse } from '@/utils/responseParser';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';

interface ServiceZone {
    id: number;
    name: string;
    ip_address: string;
    is_active: boolean;
}

interface RadiusSession {
    radacctid: number;
    acctsessionid: string;
    username: string;
    nasipaddress: string;
    nasportid: string;
    acctstarttime: string;
    acctupdatetime: string;
    callingstationid: string;
    framedipaddress: string;
}

interface RadiusLog {
    id: number;
    username: string;
    pass: string;
    reply: 'Access-Accept' | 'Access-Reject';
    authdate: string;
}

interface RadiusStats {
    active_sessions: number;
    total_nas: number;
    total_users: number;
    nodes: RadiusNodeStatus[];
}

interface RadiusNodeStatus {
    id: number;
    name: string;
    ip: string | null;
    role: string;
    is_active: boolean;
    health: {
        running: boolean;
        message: string;
    };
}

const { t } = useI18n();
const { success, error: toastError } = useToast();
const { confirm } = useConfirm();

const activeSubTab = ref('overview');
const isLoadingNodes = ref(false);
const isLoadingSessions = ref(false);
const isLoadingLogs = ref(false);
const isLoadingStatus = ref(false);

const serverList = ref<ServiceZone[]>([]);
const sessions = ref<RadiusSession[]>([]);
const authLogs = ref<RadiusLog[]>([]);
const radiusStats = ref<RadiusStats>({
    active_sessions: 0,
    total_nas: 0,
    total_users: 0,
    nodes: []
});

// --- Service Zone Logic ---
const enforceZoneLock = ref(false);

async function handleToggleEnforce(val: boolean) {
    const isConfirmed = await confirm({
        title: val ? t('isp.radius.zones.messages.enforce_enabled') : t('isp.radius.zones.messages.enforce_disabled'),
        message: val 
            ? t('isp.radius.zones.messages.enforce_enabled_desc') || 'This will RESTRICT users to their assigned Service Zone.'
            : t('isp.radius.zones.messages.enforce_disabled_desc') || 'This will ALLOW users to login from ANY Service Zone.',
        variant: val ? 'warning' : 'info'
    });

    if (!isConfirmed) return;

    try {
        await api.post('/admin/janet/settings/bulk-update', {
            settings: [
                {
                    key: 'radius_enforce_service_zone',
                    value: val,
                    type: 'boolean',
                    group: 'radius'
                }
            ]
        });
        enforceZoneLock.value = val;
        success.action(val ? t('isp.radius.zones.messages.enforce_enabled') : t('isp.radius.zones.messages.enforce_disabled'));
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
        enforceZoneLock.value = !val;
    }
}

// --- Service Zone Management ---
const newServerName = ref('');

const newServerIp = ref('');
const isAddModalOpen = ref(false);
const isEditing = ref<number | null>(null);
const editName = ref('');
const editIp = ref('');
const isSaving = ref(false);

async function fetchServers() {
    isLoadingNodes.value = true;
    try {
        const response = await api.get('/admin/janet/isp/service-zones');
        const { data } = parseResponse<ServiceZone>(response);
        serverList.value = data;
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoadingNodes.value = false;
    }
}

async function handleAdd() {
    const name = newServerName.value.trim();
    if (!name) return;
    isSaving.value = true;
    try {
        await api.post('/admin/janet/isp/service-zones', { 
            name, 
            ip_address: newServerIp.value.trim(),
            is_active: true
        });
        success.action(t('isp.radius.zones.messages.success_add'));
        newServerName.value = '';
        newServerIp.value = '';
        isAddModalOpen.value = false;
        await fetchServers();
        // Clear stats to force refresh
        radiusStats.value.nodes = [];
        await fetchStatus();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isSaving.value = false;
    }
}

async function handleUpdate(id: number, name: string) {
    const trimmedName = name.trim();
    if (!trimmedName) return;
    isSaving.value = true;
    try {
        await api.put(`/admin/janet/isp/service-zones/${id}`, { 
            name: trimmedName,
            ip_address: editIp.value
        });
        success.action(t('isp.radius.zones.messages.success_update'));
        isEditing.value = null;
        await fetchServers();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isSaving.value = false;
    }
}

async function handleDelete(id: number) {
    const isConfirmed = await confirm({
        title: t('isp.radius.zones.title'),
        message: t('common.messages.confirm.delete', { name: 'this Service Zone' }),
        variant: 'danger'
    });
    
    if (!isConfirmed) return;
    
    isLoadingNodes.value = true;
    try {
        await api.delete(`/admin/janet/isp/service-zones/${id}`);
        success.action(t('isp.radius.zones.messages.success_delete'));
        await fetchServers();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoadingNodes.value = false;
    }
}

async function handleToggleActive(server: ServiceZone) {
    isLoadingNodes.value = true;
    try {
        await api.patch(`/admin/janet/isp/service-zones/${server.id}/toggle-active`, {
            is_active: !server.is_active
        });
        success.action(t('isp.radius.zones.messages.toggle_active', { status: server.is_active ? 'deactivated' : 'activated' }));
        await fetchServers();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoadingNodes.value = false;
    }
}

// --- AAA Logic ---
async function fetchSessions() {
    isLoadingSessions.value = true;
    try {
        const response = await api.get('/admin/janet/isp/radius/sessions');
        const { data } = parseResponse<RadiusSession>(response);
        sessions.value = data;
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoadingSessions.value = false;
    }
}

async function fetchLogs() {
    isLoadingLogs.value = true;
    try {
        const response = await api.get('/admin/janet/isp/radius/logs');
        const { data } = parseResponse<RadiusLog>(response);
        authLogs.value = data;
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    } finally {
        isLoadingLogs.value = false;
    }
}

async function fetchStatus() {
    isLoadingStatus.value = true;
    try {
        const response = await api.get('/admin/janet/isp/radius/status');
        // The backend now returns { nodes: [], total_nas: int, total_users: int, active_sessions: int }
        // wrapped in standard API response { data: { ... } }
        if (response.data && response.data.data) {
            radiusStats.value = response.data.data;
        }
    } catch (_err) {
        // Silent error for status
        console.error(_err);
    } finally {
        isLoadingStatus.value = false;
    }
}

async function handleDisconnect(session: RadiusSession) {
    const isConfirmed = await confirm({
        title: t('isp.radius.actions.disconnect'),
        message: t('isp.radius.messages.confirm_disconnect', { name: session.username }),
        variant: 'danger'
    });

    if (!isConfirmed) return;

    try {
        await api.post('/admin/janet/isp/radius/disconnect', {
            username: session.username,
            nas_ip: session.nasipaddress
        });
        success.action(t('isp.radius.messages.success_disconnect'));
        await fetchSessions();
    } catch (_err) {
        toastError.action(_err as Record<string, unknown>);
    }
}

// --- Table Columns ---
const nodeColumnHelper = createColumnHelper<ServiceZone>();
const nodeColumns = [
    nodeColumnHelper.accessor('name', {
        header: t('isp.radius.zones.fields.name'),
        cell: info => {
            const server = info.row.original;
            if (isEditing.value === server.id) {
                return h('div', { class: 'space-y-2 py-2' }, [
                    h(Input, {
                        modelValue: editName.value,
                        'onUpdate:modelValue': (val: string | number) => editName.value = String(val),
                        class: 'h-8 text-sm rounded-xl'
                    }),
                    h(Input, {
                        modelValue: editIp.value,
                        'onUpdate:modelValue': (val: string | number) => editIp.value = String(val),
                        placeholder: 'Server IP (Optional)',
                        class: 'h-8 text-sm font-mono rounded-xl'
                    })
                ]);
            }
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-bold text-foreground' }, server.name),
                h('span', { class: 'text-[10px] text-muted-foreground font-mono mt-0.5' }, server.ip_address || '-')
            ]);
        }
    }),

    nodeColumnHelper.accessor('is_active', {
        header: t('isp.radius.zones.fields.status'),
        cell: info => {
            const server = info.row.original;
            return h('div', { class: 'flex items-center gap-3' }, [
                h(Switch, {
                    checked: server.is_active,
                    'onUpdate:checked': () => handleToggleActive(server)
                }),
                h('span', { class: `text-[10px] font-bold ${server.is_active ? 'text-green-600 dark:text-green-400' : 'text-muted-foreground'}` }, server.is_active ? 'Active' : 'Inactive')
            ]);
        }
    }),
    nodeColumnHelper.display({
        id: 'actions',
        cell: info => {
            const server = info.row.original;
            if (isEditing.value === server.id) {
                return h('div', { class: 'flex items-center justify-end gap-2' }, [
                    h(Button, { size: 'sm', class: 'h-8 px-3 rounded-xl', onClick: () => handleUpdate(server.id, editName.value) }, { default: () => t('common.actions.save') }),
                    h(Button, { size: 'sm', variant: 'ghost', class: 'h-8 px-3 rounded-xl', onClick: () => isEditing.value = null }, { default: () => t('common.actions.cancel') })
                ]);
            }
            return h('div', { class: 'flex items-center justify-end gap-2 text-right' }, [
                h(Button, { 
                    size: 'sm', variant: 'outline', class: 'h-8 text-blue-600 hover:bg-blue-50 rounded-xl border-border/40', 
                    onClick: () => { 
                        isEditing.value = server.id; editName.value = server.name; 
                        editIp.value = server.ip_address || ''; 
                    } 
                }, { default: () => t('common.actions.edit') }),
                h(Button, { size: 'sm', variant: 'outline', class: 'h-8 text-red-600 hover:bg-red-50 rounded-xl border-border/40', onClick: () => handleDelete(server.id) }, { default: () => t('common.actions.delete') })
            ]);
        }
    })
];

const sessionColumnHelper = createColumnHelper<RadiusSession>();
const sessionColumns = [
    sessionColumnHelper.accessor('username', {
        header: t('isp.radius.sessions.fields.username'),
        cell: info => h('span', { class: 'font-bold text-foreground' }, info.getValue())
    }),
    sessionColumnHelper.accessor('framedipaddress', {
        header: t('isp.radius.sessions.fields.client_ip'),
        cell: info => h('span', { class: 'font-mono text-xs text-blue-600 dark:text-blue-400' }, info.getValue() || '-')
    }),
    sessionColumnHelper.accessor('nasipaddress', {
        header: t('isp.radius.sessions.fields.nas_router'),
        cell: info => h('span', { class: 'text-xs text-muted-foreground' }, info.getValue())
    }),
    sessionColumnHelper.accessor('callingstationid', {
        header: t('isp.radius.sessions.fields.mac_address'),
        cell: info => h('span', { class: 'font-mono text-[10px] text-muted-foreground' }, info.getValue() || '-')
    }),
    sessionColumnHelper.accessor('acctstarttime', {
        header: t('isp.radius.sessions.fields.up_since'),
        cell: info => {
            const start = info.getValue();
            if (!start) return '-';
            return h('span', { class: 'text-xs' }, new Date(start).toLocaleString());
        }
    }),
    sessionColumnHelper.display({
        id: 'actions',
        cell: info => h(Button, {
            variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-red-500 hover:bg-red-50 rounded-xl',
            onClick: () => handleDisconnect(info.row.original)
        }, [h(UserX, { class: 'w-4 h-4' })])
    })
];

const logColumnHelper = createColumnHelper<RadiusLog>();
const logColumns = [
    logColumnHelper.accessor('username', {
        header: t('isp.radius.logs.fields.username'),
        cell: info => h('span', { class: 'font-bold text-foreground' }, info.getValue())
    }),
    logColumnHelper.accessor('reply', {
        header: t('isp.radius.logs.fields.result'),
        cell: info => {
            const val = info.getValue();
            const success = val === 'Access-Accept';
            return h(Badge, { 
                variant: 'outline', 
                class: `text-[10px] font-extrabold rounded-xl ${success ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-red-500/10 text-red-600 border-red-500/20'}` 
            }, { default: () => success ? t('isp.radius.logs.results.accepted') : t('isp.radius.logs.results.rejected') });
        }
    }),
    logColumnHelper.accessor('authdate', {
        header: t('isp.radius.logs.fields.date_time'),
        cell: info => h('span', { class: 'text-xs text-muted-foreground' }, new Date(info.getValue()).toLocaleString())
    })
];

const nodesTable = useVueTable({ get data() { return serverList.value }, columns: nodeColumns, getCoreRowModel: getCoreRowModel() });
const sessionsTable = useVueTable({ get data() { return sessions.value }, columns: sessionColumns, getCoreRowModel: getCoreRowModel() });
const logsTable = useVueTable({ get data() { return authLogs.value }, columns: logColumns, getCoreRowModel: getCoreRowModel() });

onMounted(async () => {
    await Promise.all([fetchServers(), fetchStatus()]);
});

watch(activeSubTab, (newVal) => {
    if (newVal === 'sessions') fetchSessions();
    if (newVal === 'logs') fetchLogs();
});

</script>

<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Status & Overview Header -->
        <!-- Status & Overview Header -->
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- 1. Active Sessions -->
                <Card class="border border-border/40 shadow-sm bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                <Activity class="w-6 h-6" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-muted-foreground">{{ t('isp.radius.stats.active_sessions') }}</p>
                                <h3 class="text-2xl font-bold text-foreground">{{ radiusStats.active_sessions }}</h3>
                                <p class="text-xs text-muted-foreground font-medium">{{ t('isp.radius.stats.active_sessions_desc') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- 2. Total Routers (NAS) -->
                <Card class="border border-border/40 shadow-sm bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                <Wifi class="w-6 h-6" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-muted-foreground">{{ t('isp.radius.stats.total_routers') }}</p>
                                <h3 class="text-2xl font-bold text-foreground">{{ radiusStats.total_nas }}</h3>
                                <p class="text-xs text-muted-foreground font-medium">{{ t('isp.radius.stats.total_routers_desc') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- 3. Total Users -->
                <Card class="border border-border/40 shadow-sm bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <UserX class="w-6 h-6" /> <!-- Using UserX temporarily, implies Users -->
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-muted-foreground">{{ t('isp.radius.stats.total_users') }}</p>
                                <h3 class="text-2xl font-bold text-foreground">{{ radiusStats.total_users }}</h3>
                                <p class="text-xs text-muted-foreground font-medium">{{ t('isp.radius.stats.total_users_desc') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- 4. Node Status (Combined or Primary) -->
                <!-- Just show the first node's status or a summary -->
                <Card class="border border-border/40 shadow-sm bg-card overflow-hidden rounded-xl relative">
                    <div v-if="radiusStats.nodes.length > 0 && radiusStats.nodes[0].health.running" class="absolute top-0 right-0 w-24 h-24 bg-green-500/5 rounded-full -mr-12 -mt-12"></div>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div :class="`p-3 rounded-xl ${radiusStats.nodes.length > 0 && radiusStats.nodes[0].health.running ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-500/10 text-red-600 dark:text-red-400'}`">
                                <Server v-if="radiusStats.nodes.length > 0" class="w-6 h-6" />
                                <AlertCircle v-else class="w-6 h-6" />
                            </div>
                            <div class="space-y-1">
                                <p class="text-[10px] font-bold text-muted-foreground">{{ t('isp.radius.stats.service_status') }}</p>
                                <h3 class="text-xl font-bold text-foreground truncate">
                                    {{ radiusStats.nodes.length > 0 && radiusStats.nodes[0].health.running ? t('isp.radius.stats.operational') : t('isp.radius.stats.offline') }}
                                </h3>
                                <p class="text-xs text-muted-foreground font-mono truncate">
                                    {{ radiusStats.nodes.length > 0 ? radiusStats.nodes[0].ip : t('isp.radius.stats.no_nodes_desc') }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <div class="flex justify-end">
                <Button @click="fetchStatus" :disabled="isLoadingStatus" variant="outline" size="sm" class="h-8 rounded-xl border-border/40 text-xs">
                    <RefreshCw :class="`w-3.5 h-3.5 mr-2 ${isLoadingStatus ? 'animate-spin' : ''}`" />
                    {{ t('isp.radius.stats.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <Tabs v-model="activeSubTab" class="w-full space-y-6">
            <TabsList class="p-1 bg-muted rounded-xl w-fit">
                <TabsTrigger value="overview" class="rounded-xl px-6 flex items-center gap-2">
                    <Server class="w-4 h-4" />
                    {{ t('isp.radius.tabs.nodes') }}
                </TabsTrigger>
                <TabsTrigger value="zones" class="rounded-xl px-6 flex items-center gap-2">
                    <MapPin class="w-4 h-4" /> <!-- Import MapPin -->
                    {{ t('isp.radius.tabs.zones') }}
                </TabsTrigger>
                <TabsTrigger value="sessions" class="rounded-xl px-6 flex items-center gap-2">
                    <Activity class="w-4 h-4" />
                    {{ t('isp.radius.tabs.sessions') }}
                </TabsTrigger>
                <TabsTrigger value="logs" class="rounded-xl px-6 flex items-center gap-2">
                    <History class="w-4 h-4" />
                    {{ t('isp.radius.tabs.logs') }}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="overview" class="mt-0">
                <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden bg-card">
                    <CardHeader class="border-b border-border/40 p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-bold text-foreground">{{ t('isp.radius.nodes.title') }}</CardTitle>
                                <p class="text-xs text-muted-foreground mt-1">{{ t('isp.radius.nodes.subtitle') }}</p>
                            </div>
                            <!-- ADD NODE button removed -->
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                         <!-- Simplified Node Status for Single Server -->
                         <div class="flex items-center justify-between p-4 border border-border/40 rounded-xl bg-muted/30">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-card rounded-lg border border-border/40 shadow-sm text-blue-600 dark:text-blue-400">
                                    <Server class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-foreground">{{ t('isp.radius.nodes.local_node') }}</h4>
                                    <p class="text-xs text-muted-foreground font-mono">127.0.0.1 (localhost)</p>
                                </div>
                            </div>
                            <div v-if="radiusStats.nodes.length > 0" class="flex items-center gap-2">
                                <span :class="`inline-flex w-3 h-3 rounded-full ${radiusStats.nodes[0].health.running ? 'bg-green-500' : 'bg-red-500'}`"></span>
                                <span class="text-sm font-medium text-muted-foreground">{{ radiusStats.nodes[0].health.message }}</span>
                            </div>
                         </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="zones" class="mt-0">
                <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden bg-card">
                    <CardHeader class="border-b border-border/40 p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <CardTitle class="text-lg font-bold text-foreground">{{ t('isp.radius.zones.title') }}</CardTitle>
                                <p class="text-xs text-muted-foreground mt-1">{{ t('isp.radius.zones.subtitle') }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 bg-muted/50 px-3 py-1.5 rounded-xl border border-border/40">
                                    <Label class="text-xs font-medium text-muted-foreground cursor-pointer" for="enforce-zone">{{ t('isp.radius.zones.enforce_locking') }}</Label>
                                    <Switch id="enforce-zone" :checked="enforceZoneLock" @update:checked="handleToggleEnforce" />
                                </div>
                                <Button @click="isAddModalOpen = true" class="h-9 bg-primary hover:bg-primary/90 text-primary-foreground rounded-xl px-4 font-bold text-xs shadow-sm">
                                    <MapPin class="w-3.5 h-3.5 mr-2" />
                                    {{ t('isp.radius.zones.add_zone') }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <DataTable :table="nodesTable" :loading="isLoadingNodes" />
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Add Service Zone Modal -->
            <Dialog :open="isAddModalOpen" @update:open="val => isAddModalOpen = val">
                <DialogContent class="sm:max-w-md bg-card border border-border/40 shadow-2xl rounded-xl">
                    <DialogHeader>
                        <DialogTitle>{{ t('isp.radius.zones.add_zone') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('isp.radius.zones.add_zone_desc') }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.radius.zones.fields.name') }}</Label>
                            <Input v-model="newServerName" placeholder="e.g. Hotspot-Utama" class="bg-muted/50 border-border/40 rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.radius.zones.fields.ip') }}</Label>
                            <Input v-model="newServerIp" placeholder="e.g. 192.168.88.1" class="bg-muted/50 border-border/40 rounded-xl" />
                            <p class="text-[10px] text-muted-foreground">{{ t('isp.radius.zones.fields.ip_desc') }}</p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="isAddModalOpen = false" class="border-border/40 rounded-xl">
                            {{ t('common.actions.cancel') }}
                        </Button>
                        <Button @click="handleAdd" :disabled="isSaving || !newServerName" class="bg-primary text-primary-foreground shadow-sm rounded-xl">
                            <Loader2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                            {{ t('common.actions.save') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <TabsContent value="sessions" class="mt-0">
                <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden bg-card">
                    <CardHeader class="border-b border-border/40 p-6 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg font-bold text-foreground">{{ t('isp.radius.sessions.title') }}</CardTitle>
                            <p class="text-xs text-muted-foreground mt-1">{{ t('isp.radius.sessions.subtitle') }}</p>
                        </div>
                        <Button @click="fetchSessions" variant="outline" size="sm" class="h-9 rounded-xl border-border/40">
                            <RefreshCw :class="`w-4 h-4 mr-2 ${isLoadingSessions ? 'animate-spin' : ''}`" />
                            {{ t('isp.radius.sessions.sync') }}
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <DataTable :table="sessionsTable" :loading="isLoadingSessions" />
                    </CardContent>
                </Card>
            </TabsContent>

            <TabsContent value="logs" class="mt-0">
                <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden bg-card">
                    <CardHeader class="border-b border-border/40 p-6 flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-lg font-bold text-foreground">{{ t('isp.radius.logs.title') }}</CardTitle>
                            <p class="text-xs text-muted-foreground mt-1">{{ t('isp.radius.logs.subtitle') }}</p>
                        </div>
                        <Button @click="fetchLogs" variant="outline" size="sm" class="h-9 rounded-xl border-border/40">
                            <RefreshCw :class="`w-4 h-4 mr-2 ${isLoadingLogs ? 'animate-spin' : ''}`" />
                            {{ t('isp.radius.actions.refresh') }}
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <DataTable :table="logsTable" :loading="isLoadingLogs" />
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>

