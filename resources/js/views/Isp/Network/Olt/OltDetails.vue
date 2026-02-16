<template>
    <div class="space-y-6" v-if="olt">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$router.push({ name: 'isp-olts' })" class="rounded-xl">
                    <ChevronLeft class="w-4 h-4" />
                </Button>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-3xl font-bold tracking-tight">{{ olt.name }}</h2>
                        <Badge :variant="getStatusVariant(olt.status)" class="rounded-xl">{{ olt.status }}</Badge>
                    </div>
                    <p class="text-muted-foreground">{{ olt.type }} • {{ olt.ip_address }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="testConnection" :disabled="testing" class="rounded-xl">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': testing }" />
                    {{ $t('isp.infra.modals.test_connection') }}
                </Button>
                <Button @click="fetchData" class="rounded-xl">
                    <RefreshCw class="w-4 h-4 mr-2" />
                    {{ $t('isp.infra.olt.audit.refresh') }}
                </Button>
            </div>
        </div>

        <Tabs v-model="activeTab" class="w-full">
            <div class="mb-8 border-b flex items-center justify-between">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="overview" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">{{ $t('isp.infra.olt.details.tabs.overview') }}</TabsTrigger>
                    <TabsTrigger value="telemetry" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">{{ $t('isp.infra.olt.details.tabs.telemetry') }}</TabsTrigger>
                    <TabsTrigger value="audit" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">{{ $t('isp.infra.olt.details.tabs.audit') }}</TabsTrigger>
                    <TabsTrigger value="topology" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">{{ $t('isp.infra.olt.details.tabs.topology') }}</TabsTrigger>
                </TabsList>
            </div>

            <TabsContent value="overview" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Hardware Info -->
                    <Card class="md:col-span-2 border border-border/40 shadow-sm rounded-xl overflow-hidden">
                        <CardHeader class="border-b border-border/40">
                            <CardTitle>{{ $t('isp.infra.olt.details.overview.hardware') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <Label class="text-muted-foreground">{{ $t('isp.infra.olt.fields.vendor') }}</Label>
                                <p class="text-lg font-medium">{{ olt.type }} Series</p>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-muted-foreground">{{ $t('isp.infra.nodes.fields.ip') }}</Label>
                                <p class="text-lg font-medium font-mono">{{ olt.ip_address }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-muted-foreground">{{ $t('isp.infra.olt.fields.port') }}</Label>
                                <p class="text-lg font-medium">{{ olt.port || 22 }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label class="text-muted-foreground">{{ $t('common.labels.created_at') }}</Label>
                                <p class="text-lg font-medium">{{ formatDate(olt.created_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Connectivity Stats -->
                    <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
                        <CardHeader class="border-b border-border/40">
                            <CardTitle>{{ $t('isp.infra.olt.details.overview.system_health') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ $t('isp.infra.olt.details.overview.ssh_reachability') }}</span>
                                <Badge variant="success" v-if="olt.status === 'active'">{{ $t('isp.infra.olt.details.overview.reachable') }}</Badge>
                                <Badge variant="destructive" v-else>{{ $t('isp.infra.olt.details.overview.unreachable') }}</Badge>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-[10px] font-bold text-muted-foreground">
                                    <span>Signal Samples</span>
                                    <span>{{ signals.length }} Data Points</span>
                                </div>
                                <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                                    <div class="h-full bg-primary" :style="{ width: '85%' }"></div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </TabsContent>

            <TabsContent value="telemetry" class="space-y-6">
                <!-- Signal History Chart -->
                <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between border-b border-border/40">
                        <div>
                            <CardTitle>{{ $t('isp.infra.olt.details.telemetry.optical_power') }}</CardTitle>
                            <CardDescription>{{ $t('isp.infra.olt.details.telemetry.optical_description') }}</CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-200 dark:border-blue-900 rounded-xl font-bold">
                                {{ $t('isp.infra.olt.details.telemetry.mean') }}: {{ stats.avg_rx?.toFixed(2) || '0' }} dBm
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[400px]">
                            <canvas ref="chartCanvas"></canvas>
                        </div>
                    </CardContent>
                </Card>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card v-for="(stat, key) in signalSummary" :key="key" class="p-4 border border-border/40 shadow-sm rounded-xl">
                        <p class="text-sm text-muted-foreground mb-1 font-medium">{{ key }}</p>
                        <p class="text-2xl font-bold" :class="getSignalColor(stat)">{{ stat ? stat.toFixed(2) : '-.--' }} dBm</p>
                        <p class="text-xs text-muted-foreground mt-2">{{ $t('features.isp.olt.details.telemetry.threshold_monitoring') }}</p>
                    </Card>
                </div>
            </TabsContent>

            <TabsContent value="audit" class="space-y-6">
                 <Card class="border border-border/40 shadow-sm rounded-xl overflow-hidden">
                    <CardHeader class="border-b border-border/40">
                        <CardTitle>{{ $t('isp.infra.olt.details.audit.title') }}</CardTitle>
                        <CardDescription>{{ $t('isp.infra.olt.details.audit.subtitle') }}</CardDescription>
                    </CardHeader>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ $t('isp.infra.olt.details.audit.time') }}</TableHead>
                                    <TableHead>{{ $t('isp.infra.olt.details.audit.operator') }}</TableHead>
                                    <TableHead>{{ $t('isp.infra.olt.details.audit.command') }}</TableHead>
                                    <TableHead>{{ $t('isp.infra.nodes.fields.status') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="log in logs" :key="log.id">
                                    <TableCell class="whitespace-nowrap">{{ formatTime(log.created_at) }}</TableCell>
                                    <TableCell>{{ log.user?.name || 'System' }}</TableCell>
                                    <TableCell><code class="text-xs">{{ log.command.substring(0, 40) }}...</code></TableCell>
                                    <TableCell>
                                        <Badge :variant="log.is_success ? 'success' : 'destructive'" class="rounded-xl">
                                            {{ log.is_success ? 'OK' : 'ERR' }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="p-4 border-t text-center">
                        <Button variant="link" @click="$router.push({ name: 'isp-olt-logs', query: { olt_id: olt.id } })">
                            {{ $t('isp.infra.olt.details.audit.view_deep_audit') }}
                        </Button>
                    </div>
                </Card>
            </TabsContent>

            <TabsContent value="topology" class="space-y-6">
                <!-- ODP Aggregation -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                     <Card class="p-4 border-dashed border-2 border-border/40 flex flex-col items-center justify-center py-10 opacity-60 rounded-xl transition-colors hover:border-primary/40 hover:bg-muted/30">
                         <Plus class="w-8 h-8 mb-2" />
                         <p class="text-sm font-medium">{{ $t('isp.infra.olt.details.topology.link_new_odp') }}</p>
                     </Card>
                     <Card v-for="odp in olt.odps" :key="odp.id" class="p-4 border border-border/40 shadow-sm rounded-xl hover:border-primary/40 transition-colors cursor-pointer bg-card overflow-hidden">
                         <div class="flex items-start justify-between mb-3">
                             <div class="p-2 bg-muted rounded-xl">
                                 <Database class="w-5 h-5 font-mono text-primary opacity-70" />
                             </div>
                             <Badge variant="outline" class="rounded-xl">{{ odp.status }}</Badge>
                         </div>
                         <h4 class="font-bold">{{ odp.name }}</h4>
                         <p class="text-xs text-muted-foreground">Capacity: {{ odp.total_ports }} Ports</p>
                     </Card>
                </div>
            </TabsContent>
        </Tabs>
    </div>
    
    <div v-else class="flex items-center justify-center py-40">
        <RefreshCw class="w-10 h-10 animate-spin text-primary opacity-20" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { 
    Button, Card, CardHeader, CardTitle, CardContent, CardDescription,
    Badge, Tabs, TabsList, TabsTrigger, TabsContent, Label,
    Table, TableHeader, TableBody, TableHead, TableRow, TableCell
} from '@/components/ui';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Chart from 'chart.js/auto';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import dayjs from 'dayjs';
import type { Olt, OltSignal, OltSignalStats, OltCommandLog } from '@/types/isp';
import { useI18n } from 'vue-i18n';

const route = useRoute();
const toast = useToast();
const { t } = useI18n();

const olt = ref<Olt | null>(null);
const activeTab = ref('overview');
const loading = ref(false);
const testing = ref(false);
const signals = ref<OltSignal[]>([]);
const stats = ref<Partial<OltSignalStats>>({});
const logs = ref<OltCommandLog[]>([]);

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const fetchData = async () => {
    loading.value = true;
    try {
        const [oltRes, logRes, signalRes, statRes] = await Promise.all([
            api.get(`/admin/janet/isp/olts/${route.params.id}`),
            api.get('/admin/janet/isp/olts/logs', { params: { olt_id: route.params.id, per_page: 5 } }),
            api.get('/admin/janet/isp/monitor/olt/signals', { params: { olt_id: route.params.id, limit: 50 } }),
            api.get(`/admin/janet/isp/monitor/olt/${route.params.id}/signal-stats`)
        ]);
        
        olt.value = oltRes.data.data.olt;
        logs.value = logRes.data.data;
        signals.value = signalRes.data.data;
        stats.value = statRes.data.data;
        
        if (activeTab.value === 'telemetry') {
            await nextTick();
            renderChart();
        }
    } catch (_e) {
        toast.error.action('Failed to load OLT details');
    } finally {
        loading.value = false;
    }
};

const renderChart = () => {
    if (!chartCanvas.value) return;
    
    if (chartInstance) chartInstance.destroy();
    
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    // Sort signals by time
    const sortedSignals = [...signals.value].reverse();
    const labels = sortedSignals.map(s => dayjs(s.created_at).format('HH:mm'));
    const data = sortedSignals.map(s => s.rx_power);
    
    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Mean Optical Power (dBm)',
                data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 10 } }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { size: 10 } },
                    title: { display: true, text: 'dBm', color: textColor },
                    suggestedMin: -30,
                    suggestedMax: -10
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: isDark ? '#1e293b' : '#ffffff',
                    titleColor: isDark ? '#f8fafc' : '#1e293b',
                    bodyColor: isDark ? '#f8fafc' : '#1e293b',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1
                }
            }
        }
    });
};

const testConnection = async () => {
    if (!olt.value) return;
    testing.value = true;
    try {
        await api.get(`/admin/janet/isp/olts/${olt.value.id}/test-connection`);
        toast.success.action(t('features.isp.olt.messages.test_success'));
    } catch (_e) {
        toast.error.action(t('features.isp.olt.messages.test_failed'));
    } finally {
        testing.value = false;
    }
};

const getStatusVariant = (status: string | undefined): "success" | "warning" | "destructive" | "default" | "outline" | null | undefined => {
    if (status === 'active') return 'success';
    if (status === 'maintenance') return 'warning';
    return 'destructive';
};

const formatDate = (date: string | Date | undefined) => date ? dayjs(date).format('MMMM D, YYYY') : '-';
const formatTime = (date: string | Date | undefined) => date ? dayjs(date).format('HH:mm:ss') : '-';

const getSignalColor = (val: number | string | undefined | null) => {
    if (val === undefined || val === null) return 'text-muted-foreground';
    const num = typeof val === 'string' ? parseFloat(val) : val;
    if (num < -27) return 'text-destructive';
    if (num < -24) return 'text-warning';
    return 'text-success';
};

const signalSummary = computed(() => ({
    [t('features.isp.olt.details.telemetry.min_rx')]: stats.value.min_rx,
    [t('features.isp.olt.details.telemetry.avg_rx')]: stats.value.avg_rx,
    [t('features.isp.olt.details.telemetry.max_rx')]: stats.value.max_rx
}));

watch(activeTab, (val) => {
    if (val === 'telemetry') {
        nextTick(renderChart);
    }
});

onMounted(fetchData);
onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>
