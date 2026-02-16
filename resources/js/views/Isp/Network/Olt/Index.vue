<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ $t('isp.infra.olt.title') }}</h2>
                <p class="text-muted-foreground">{{ $t('isp.infra.olt.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <Button @click="triggerDiscovery" variant="outline" :disabled="scanning" class="rounded-xl">
                    <Search class="w-4 h-4 mr-2" :class="{ 'animate-spin': scanning }" />
                    {{ $t('isp.infra.olt.discovery.scan') }}
                </Button>
                <Button @click="openCreateModal" class="rounded-xl">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('isp.infra.olt.add_olt') }}
                </Button>
            </div>
        </div>

        <Tabs :default-value="activeTab" @update:model-value="onTabChange" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="list" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <NetworkIcon class="w-4 h-4 mr-2" /> {{ $t('isp.olt.tabs.list', 'OLT List') }}
                    </TabsTrigger>
                    <TabsTrigger value="discovery" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <BoxIcon class="w-4 h-4 mr-2" /> {{ $t('isp.olt.tabs.discovery', 'ZTP Discovery') }}
                    </TabsTrigger>
                    <TabsTrigger value="logs" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <ScrollIcon class="w-4 h-4 mr-2" /> {{ $t('isp.olt.tabs.logs', 'Audit Logs') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="list" class="mt-6 space-y-6">

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card class="p-4 bg-primary/5 border border-border/40 rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground font-medium">{{ $t('isp.infra.nodes.stats.total') }}</p>
                        <p class="text-2xl font-bold">{{ stats.total }}</p>
                    </div>
                    <Network class="w-8 h-8 text-primary opacity-40" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-success bg-success/5 rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground font-medium">{{ $t('isp.infra.nodes.stats.active') }}</p>
                        <p class="text-2xl font-bold text-success">{{ stats.active }}</p>
                    </div>
                    <Activity class="w-8 h-8 text-success opacity-40" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-warning bg-warning/5 rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground font-medium">{{ $t('isp.infra.nodes.stats.maintenance') }}</p>
                        <p class="text-2xl font-bold text-warning">{{ stats.maintenance }}</p>
                    </div>
                    <Wrench class="w-8 h-8 text-warning opacity-40" />
                </div>
            </Card>
            <Card class="p-4 border-l-4 border border-border/40 border-l-destructive bg-destructive/5 rounded-xl shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground font-medium">{{ $t('isp.infra.nodes.stats.offline') }}</p>
                        <p class="text-2xl font-bold text-destructive">{{ stats.inactive }}</p>
                    </div>
                    <AlertTriangle class="w-8 h-8 text-destructive opacity-40" />
                </div>
            </Card>
        </div>

        <!-- OLT List -->
        <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
            <div class="p-4 border-b border-border/40 flex justify-between items-center">
                <div class="relative w-64">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" :placeholder="$t('isp.infra.nodes.fields.search_placeholder')" class="pl-9 rounded-xl" />
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <DataTable 
                    :table="table" 
                    :loading="loading" 
                />
            </div>
        </Card>

        <!-- OLT Form Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-[500px] rounded-2xl border-none shadow-2xl">
                <DialogHeader>
                    <DialogTitle>{{ editingId ? $t('isp.infra.modals.edit_title') : $t('isp.infra.modals.create_title') }}</DialogTitle>
                    <DialogDescription>{{ $t('isp.infra.olt.subtitle') }}</DialogDescription>
                </DialogHeader>
                
                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.name') }}</Label>
                            <Input v-model="form.name" placeholder="e.g. OLT-JKT-HQ" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.vendor_type') }}</Label>
                            <Select v-model="form.type">
                                <SelectTrigger class="rounded-xl">
                                    <SelectValue :placeholder="$t('common.labels.all')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="ZTE">ZTE</SelectItem>
                                    <SelectItem value="HUAWEI">HUAWEI</SelectItem>
                                    <SelectItem value="HSGQ">HSGQ</SelectItem>
                                    <SelectItem value="HIOSO">HIOSO</SelectItem>
                                    <SelectItem value="ZYMLINK">ZYMLINK</SelectItem>
                                    <SelectItem value="GLOBAL">GLOBAL</SelectItem>
                                    <SelectItem value="VSOL">VSOL</SelectItem>
                                    <SelectItem value="MOCK">MOCK (Testing)</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.ip_address') }}</Label>
                            <Input v-model="form.ip_address" placeholder="10.10.x.x" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.ssh_port') }}</Label>
                            <Input v-model="form.port" type="number" placeholder="22" class="rounded-xl" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.username') }}</Label>
                            <Input v-model="form.username" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ $t('isp.infra.olt.fields.password') }}</Label>
                            <Input v-model="form.password" type="password" class="rounded-xl" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>{{ $t('common.labels.status') }}</Label>
                        <Select v-model="form.status">
                            <SelectTrigger class="rounded-xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="maintenance">Maintenance</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter>
                    <div class="flex justify-between w-full">
                        <Button variant="ghost" @click="testConnection" :disabled="testingConn" class="rounded-xl">
                            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': testingConn }" />
                            {{ $t('isp.infra.modals.test_connection') }}
                        </Button>
                        <div class="flex gap-2">
                            <Button variant="outline" @click="showModal = false" class="rounded-xl">{{ $t('common.actions.cancel') }}</Button>
                            <Button @click="saveOlt" :disabled="saving" class="rounded-xl">{{ $t('isp.infra.modals.save') }}</Button>
                        </div>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
            </TabsContent>

            <TabsContent value="discovery" class="mt-6">
                <ZtpDiscovery v-if="loaded.discovery" />
            </TabsContent>

            <TabsContent value="logs" class="mt-6">
                <AuditLogs v-if="loaded.logs" />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, defineAsyncComponent, onMounted, h } from 'vue';
import { useRoute, useRouter as useVueRouter } from 'vue-router';
import { 
    Button, Card, Input, Badge, DataTable,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';

// Lazy sub-tabs
const ZtpDiscovery = defineAsyncComponent(() => import('./ZtpDiscovery.vue'));
const AuditLogs = defineAsyncComponent(() => import('./AuditLogs.vue'));

import BoxIcon from 'lucide-vue-next/dist/esm/icons/box.js';
import ScrollIcon from 'lucide-vue-next/dist/esm/icons/scroll.js';
import NetworkIcon from 'lucide-vue-next/dist/esm/icons/network.js';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Network from 'lucide-vue-next/dist/esm/icons/network.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Wrench from 'lucide-vue-next/dist/esm/icons/wrench.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Olt } from '@/types/isp';
import { useI18n } from 'vue-i18n';

const toast = useToast();
const { confirm } = useConfirm();
const route = useRoute();
const router = useVueRouter();
const { t } = useI18n();

// Tab management
const activeTab = ref((route.query.tab as string) || 'list');
const loaded = reactive({ discovery: false, logs: false });
const onTabChange = (tab: string | number | boolean) => {
    const tabStr = String(tab);
    activeTab.value = tabStr;
    router.replace({ query: { ...route.query, tab: tabStr === 'list' ? undefined : tabStr } });
    if (tabStr === 'discovery') loaded.discovery = true;
    if (tabStr === 'logs') loaded.logs = true;
};
if (activeTab.value !== 'list') onTabChange(activeTab.value);

const loading = ref(false);
const scanning = ref(false);
const testingConn = ref(false);
const saving = ref(false);
const search = ref('');
const olts = ref<Olt[]>([]);
const editingId = ref<number | null>(null);
const showModal = ref(false);

interface OltStats {
    total: number;
    active: number;
    maintenance: number;
    inactive: number;
}

const stats = ref<OltStats>({
    total: 0,
    active: 0,
    maintenance: 0,
    inactive: 0
});

const form = ref<Partial<Olt>>({
    name: '',
    type: 'ZTE',
    ip_address: '',
    port: 22,
    username: '',
    password: '',
    status: 'active'
});

// --- Table Setup ---
const columnHelper = createColumnHelper<Olt>();
const columns = [
    columnHelper.accessor('name', {
        header: t('isp.infra.nodes.fields.name'),
        cell: info => h('div', { class: 'font-bold flex items-center gap-2' }, [
            h(Network, { class: 'w-4 h-4 text-primary opacity-70' }),
            info.getValue()
        ])
    }),
    columnHelper.accessor('type', {
        header: t('isp.infra.olt.fields.vendor'),
        cell: info => h(Badge, { variant: 'outline' }, () => info.getValue())
    }),
    columnHelper.accessor('ip_address', {
        header: t('isp.infra.nodes.fields.ip'),
        cell: info => h('code', { class: 'text-xs bg-muted p-1 rounded font-mono dark:bg-slate-800' }, info.getValue())
    }),
    columnHelper.accessor('status', {
        header: t('isp.infra.nodes.fields.status'),
        cell: info => {
            const status = info.getValue() as string;
            const variant = status === 'active' ? 'success' : (status === 'maintenance' ? 'warning' : 'destructive');
            return h(Badge, { variant, class: 'rounded-xl' }, () => status);
        }
    }),
    columnHelper.display({
        id: 'actions',
        header: t('isp.infra.nodes.fields.actions'),
        cell: info => {
            const olt = info.row.original;
            return h('div', { class: 'flex gap-1 justify-end' }, [
                h(Button, { variant: 'ghost', size: 'icon', class: 'rounded-xl', onClick: () => router.push({ name: 'isp-olts-show', params: { id: olt.id } }) }, () => h(Eye, { class: 'w-4 h-4' })),
                h(Button, { variant: 'ghost', size: 'icon', class: 'rounded-xl', onClick: () => editOlt(olt) }, () => h(Pencil, { class: 'w-4 h-4' })),
                h(Button, { variant: 'ghost', size: 'icon', class: 'rounded-xl text-destructive', onClick: () => deleteOlt(olt) }, () => h(Trash2, { class: 'w-4 h-4' }))
            ]);
        }
    })
];

const table = useVueTable({
    get data() { return olts.value as Olt[] },
    columns,
    getCoreRowModel: getCoreRowModel(),
});

const fetchOlts = async () => {
    loading.value = true;
    try {
        const res = await api.get('/admin/janet/isp/olts');
        olts.value = res.data.data;
        updateStats();
    } catch (_e) {
        toast.error.action('Failed to fetch OLTs');
    } finally {
        loading.value = false;
    }
};

const updateStats = () => {
    stats.value = {
        total: olts.value.length,
        active: olts.value.filter(o => o.status === 'active').length,
        maintenance: olts.value.filter(o => o.status === 'maintenance').length,
        inactive: olts.value.filter(o => o.status === 'inactive').length
    };
};

const openCreateModal = () => {
    editingId.value = null;
    form.value = { name: '', type: 'ZTE', ip_address: '', port: 22, username: '', password: '', status: 'active' };
    showModal.value = true;
};

const editOlt = (olt: Olt) => {
    editingId.value = olt.id;
    form.value = { ...olt };
    showModal.value = true;
};

const saveOlt = async () => {
    saving.value = true;
    try {
        if (editingId.value) {
            await api.put(`/admin/janet/isp/olts/${editingId.value}`, form.value);
            toast.success.action(t('isp.infra.messages.success_update'));
        } else {
            await api.post('/admin/janet/isp/olts', form.value);
            toast.success.action(t('isp.infra.messages.success_create'));
        }
        showModal.value = false;
        fetchOlts();
    } catch (_e) {
        toast.error.action(_e as string);
    } finally {
        saving.value = false;
    }
};

const testConnection = async () => {
    if (!editingId.value) {
        toast.info(t('common.messages.toast.info'), t('features.isp.olt.messages.save_first'));
        return;
    }
    testingConn.value = true;
    try {
        await api.get(`/admin/janet/isp/olts/${editingId.value}/test-connection`);
        toast.success.action(t('isp.infra.messages.test_success'));
    } catch (_e) {
        toast.error.action(t('isp.infra.messages.test_failed'));
    } finally {
        testingConn.value = false;
    }
};

const triggerDiscovery = async () => {
    scanning.value = true;
    try {
        await api.get('/admin/janet/isp/olts/discover');
        toast.success.action(t('isp.infra.messages.test_success'));
    } catch (_e) {
        toast.error.action('Discovery trigger failed');
    } finally {
        scanning.value = false;
    }
};

const deleteOlt = async (olt: Olt) => {
    const isConfirmed = await confirm({
        title: t('features.isp.olt.modals.delete_title'),
        message: t('features.isp.olt.modals.delete_confirm', { name: olt.name }),
        variant: 'danger'
    });
    if (isConfirmed) {
        try {
            await api.delete(`/admin/janet/isp/olts/${olt.id}`);
            toast.success.action(t('isp.infra.messages.success_delete'));
            fetchOlts();
        } catch (_e) {
            toast.error.action('Failed to delete OLT');
        }
    }
};

onMounted(fetchOlts);
</script>
