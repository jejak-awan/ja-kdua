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

            <div v-else-if="loading && sessions.length === 0" class="p-24 text-center">
                <Loader2 class="w-10 h-10 animate-spin mx-auto text-primary opacity-50" />
                <p class="mt-4 text-sm font-medium text-muted-foreground animate-pulse">{{ t('isp.monitor.connecting') }}</p>
            </div>

            <div v-else class="overflow-x-auto">
                <Table>
                    <TableHeader class="bg-muted/30">
                        <TableRow>
                            <TableHead class="w-[280px] py-4">{{ t('isp.monitor.active_sessions.table.customer') }}</TableHead>
                            <TableHead class="py-4">{{ t('isp.monitor.active_sessions.table.ip_address') }}</TableHead>
                            <TableHead class="py-4">{{ t('isp.monitor.active_sessions.table.uptime') }}</TableHead>
                            <TableHead class="text-center py-4">{{ t('isp.monitor.active_sessions.table.type') }}</TableHead>
                            <TableHead class="py-4">{{ t('isp.monitor.active_sessions.table.caller_id') }}</TableHead>
                            <TableHead class="text-right py-4 pr-6">{{ t('isp.monitor.active_sessions.table.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="session in sessions" :key="session.type + '-' + session.id" class="hover:bg-primary/5 transition-all duration-300 group">
                            <TableCell class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                        <User class="w-5 h-5 font-bold" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-foreground tracking-tight">{{ session.name }}</span>
                                        <span class="text-[10px] text-muted-foreground font-mono uppercase tracking-widest opacity-60">{{ session.service || 'Default' }}</span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="py-4">
                                <div class="flex items-center gap-2 font-mono text-[11px] text-foreground/80">
                                    <Globe class="w-3.5 h-3.5 text-primary/40" />
                                    {{ session.address }}
                                </div>
                            </TableCell>
                            <TableCell class="py-4">
                                <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                                    <Clock class="w-3.5 h-3.5 opacity-50" />
                                    {{ session.uptime }}
                                </div>
                            </TableCell>
                            <TableCell class="py-4 text-center">
                                <Badge 
                                    :variant="session.type === 'pppoe' ? 'default' : 'secondary'" 
                                    class="text-[9px] font-bold uppercase tracking-tighter px-2 py-0.5 rounded-md"
                                >
                                    {{ session.type }}
                                </Badge>
                            </TableCell>
                            <TableCell class="py-4">
                                <span class="font-mono text-[10px] text-muted-foreground/70 bg-muted/20 px-1.5 py-0.5 rounded">
                                    {{ session.caller_id || 'N/A' }}
                                </span>
                            </TableCell>
                            <TableCell class="py-4 text-right pr-6">
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    class="h-8 w-8 p-0 text-destructive/70 hover:text-destructive hover:bg-destructive/10 rounded-full transition-all group-hover:scale-110" 
                                    @click="disconnect(session)"
                                    :loading="disconnecting === session.id"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="sessions.length === 0 && !loading">
                            <TableCell colspan="6" class="p-24 text-center">
                                <div class="flex flex-col items-center justify-center gap-3 py-10">
                                    <div class="w-16 h-16 rounded-full bg-muted/20 flex items-center justify-center">
                                        <CircleAlert class="w-8 h-8 text-muted-foreground/30" />
                                    </div>
                                    <h4 class="text-sm font-medium text-muted-foreground">{{ t('isp.monitor.active_sessions.messages.no_sessions') }}</h4>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
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
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell
} from '@/components/ui';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import CircleAlert from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
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
