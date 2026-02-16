<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Printer from 'lucide-vue-next/dist/esm/icons/printer.js';
import CalendarIcon from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import CircleArrowUp from 'lucide-vue-next/dist/esm/icons/circle-arrow-up.js';
import CircleArrowDown from 'lucide-vue-next/dist/esm/icons/circle-arrow-down.js';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui';
import { Button } from '@/components/ui';
import { Input } from '@/components/ui';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui';
import { Badge } from '@/components/ui';
import { 
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui';

const { t } = useI18n();

// State
const routers = ref<any[]>([]);
const selectedRouter = ref<string>('');
const interfaces = ref<any[]>([]);
const selectedInterface = ref<string>('');
const startDate = ref<string>(new Date().toISOString().split('T')[0]);
const endDate = ref<string>(new Date().toISOString().split('T')[0]);
const interval = ref<string>('1 hour');
const loading = ref(false);
const loadingRouters = ref(false);
const loadingInterfaces = ref(false);
const trafficData = ref<any[]>([]);
const slaData = ref<any[]>([]);
const reportGeneratedAt = ref<string>('');

// Helpers
const formatSpeed = (bps: number): string => {
    if (bps === 0) return '0 bps';
    if (bps >= 1000000000) return `${(bps / 1000000000).toFixed(2)} Gbps`;
    if (bps >= 1000000) return `${(bps / 1000000).toFixed(2)} Mbps`;
    if (bps >= 1000) return `${(bps / 1000).toFixed(2)} Kbps`;
    return `${bps.toFixed(0)} bps`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Actions
const fetchRouters = async () => {
    loadingRouters.value = true;
    try {
        const response = await api.get('/admin/janet/isp/routers', {
            params: { type: 'Router', per_page: 100 }
        });
        
        const payload = response.data;
        let items: any[] = [];

        if (payload.data && payload.data.data && Array.isArray(payload.data.data)) {
            items = payload.data.data;
        } else if (payload.data && Array.isArray(payload.data)) {
            items = payload.data;
        } else if (Array.isArray(payload)) {
            items = payload;
        }

        routers.value = items;

        // Auto-select if only 1 router
        if (items.length === 1 && !selectedRouter.value) {
            selectedRouter.value = String(items[0].id);
            await fetchInterfaces();
        }
    } catch (error) {
        console.error('Failed to fetch routers', error);
    } finally {
        loadingRouters.value = false;
    }
};

const fetchInterfaces = async () => {
    if (!selectedRouter.value) return;
    loadingInterfaces.value = true;
    try {
        const response = await api.get('/admin/janet/isp/monitor/interfaces', {
            params: { router_id: selectedRouter.value }
        });
        
        const payload = response.data;
        let items: any[] = [];

        if (payload.data && Array.isArray(payload.data)) {
            items = payload.data;
        } else if (Array.isArray(payload)) {
            items = payload;
        }

        interfaces.value = items;

        // Auto-select first interface if none selected
        if (items.length > 0 && !selectedInterface.value) {
            selectedInterface.value = items[0].name || items[0];
        }
    } catch (error) {
        console.error('Failed to fetch interfaces', error);
    } finally {
        loadingInterfaces.value = false;
    }
};

const generateReport = async () => {
    if (!selectedRouter.value || !selectedInterface.value) return;
    
    loading.value = true;
    reportGeneratedAt.value = new Date().toLocaleString();
    
    try {
        const [trafficRes, slaRes] = await Promise.all([
            api.get('/admin/janet/isp/monitor/reports/traffic', {
                params: {
                    router_id: selectedRouter.value,
                    interface: selectedInterface.value,
                    start_date: startDate.value + ' 00:00:00',
                    end_date: endDate.value + ' 23:59:59',
                    interval: interval.value
                }
            }),
            api.get('/admin/janet/isp/monitor/reports/sla', {
                params: {
                    node_id: selectedRouter.value,
                    start_date: startDate.value + ' 00:00:00',
                    end_date: endDate.value + ' 23:59:59'
                }
            })
        ]);
        
        trafficData.value = trafficRes.data.data;
        slaData.value = slaRes.data.data;
    } catch (error) {
        console.error('Failed to generate report', error);
    } finally {
        loading.value = false;
    }
};

const printReport = () => {
    window.print();
};

const getRouterName = (id: string) => {
    const r = routers.value.find(router => String(router.id) === id);
    return r ? r.name : 'Unknown';
};

// Summary Calculaions
const summary = computed(() => {
    if (trafficData.value.length === 0) return null;
    
    const rx = trafficData.value.map(d => parseFloat(d.avg_rx));
    const tx = trafficData.value.map(d => parseFloat(d.avg_tx));
    const max_rx = trafficData.value.map(d => parseFloat(d.max_rx));
    const max_tx = trafficData.value.map(d => parseFloat(d.max_tx));

    return {
        avgRx: rx.reduce((a, b) => a + b, 0) / rx.length,
        avgTx: tx.reduce((a, b) => a + b, 0) / tx.length,
        peakRx: Math.max(...max_rx),
        peakTx: Math.max(...max_tx)
    };
});

onMounted(() => {
    fetchRouters();
});
</script>

<template>
    <div class="p-6 space-y-6 min-h-screen bg-slate-50/50 print:bg-white print:p-0">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ t('isp.monitor.report_title', 'Network Traffic & SLA Report') }}</h1>
                <p class="text-slate-500 mt-1">{{ t('isp.monitor.report_desc', 'Analyze historical performance and service availability.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" @click="printReport" :disabled="trafficData.length === 0" class="rounded-xl">
                    <Printer class="w-4 h-4 mr-2" />
                    {{ t('common.print', 'Print') }}
                </Button>
            </div>
        </div>

        <!-- Filter Card -->
        <Card class="print:hidden border border-border/40 shadow-sm bg-white/80 backdrop-blur-sm rounded-xl">
            <CardContent class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <Server class="w-3 h-3" /> {{ t('isp.infra.router', 'Router') }}
                        </label>
                        <Select v-model="selectedRouter" @update:modelValue="fetchInterfaces">
                            <SelectTrigger class="bg-background rounded-xl">
                                <SelectValue :placeholder="loadingRouters ? 'Loading...' : t('isp.monitor.select_router', 'Pilih Router')" />
                            </SelectTrigger>
                            <SelectContent>
                                <div v-if="loadingRouters" class="p-2 text-center text-xs text-slate-400">
                                    <LoaderCircle class="w-4 h-4 animate-spin mx-auto mb-1" />
                                    Loading...
                                </div>
                                <SelectItem v-for="router in routers" :key="router.id" :value="String(router.id)">
                                    {{ router.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <Activity class="w-3 h-3" /> {{ t('isp.infra.interface', 'Interface') }}
                        </label>
                        <Select v-model="selectedInterface" :disabled="!selectedRouter || loadingInterfaces">
                            <SelectTrigger class="bg-background rounded-xl">
                                <SelectValue :placeholder="loadingInterfaces ? 'Loading...' : t('isp.monitor.select_interface', 'Pilih Interface')" />
                            </SelectTrigger>
                            <SelectContent>
                                <div v-if="loadingInterfaces" class="p-2 text-center text-xs text-slate-400">
                                    <LoaderCircle class="w-4 h-4 animate-spin mx-auto mb-1" />
                                    Loading...
                                </div>
                                <SelectItem v-for="iface in interfaces" :key="iface.name" :value="iface.name">
                                    {{ iface.name }} <span class="text-xs text-slate-400">({{ iface.type }})</span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <CalendarIcon class="w-3 h-3" /> {{ t('common.start_date', 'Dari Tanggal') }}
                        </label>
                        <Input type="date" v-model="startDate" class="bg-background rounded-xl" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 flex items-center gap-2">
                            <CalendarIcon class="w-3 h-3" /> {{ t('common.end_date', 'Sampai Tanggal') }}
                        </label>
                        <Input type="date" v-model="endDate" class="bg-background rounded-xl" />
                    </div>

                    <div class="flex items-end">
                        <Button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl" @click="generateReport" :disabled="loading || !selectedRouter || !selectedInterface">
                            <LoaderCircle v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                            <Search v-else class="w-4 h-4 mr-2" />
                            {{ t('common.generate', 'Generate') }}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Report Content -->
        <div v-if="trafficData.length > 0 || slaData.length > 0" class="space-y-6">
            
            <!-- Print Header Only -->
            <div class="hidden print:block text-center border-b pb-6 mb-8">
                <h1 class="text-3xl font-bold">{{ t('isp.monitor.report_title', 'Network Traffic & SLA Report') }}</h1>
                <p class="text-slate-600 mt-2">
                    {{ t('common.period', 'Periode') }}: {{ startDate }} - {{ endDate }}
                </p>
                <div class="mt-4 flex justify-between text-sm text-slate-500 italic">
                    <span>JANET Infrastructure Management System</span>
                    <span>{{ t('common.generated_at', 'Dibuat pada') }}: {{ reportGeneratedAt }}</span>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" v-if="summary">
                <Card class="border border-border/40 shadow-sm overflow-hidden bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500 mb-1">Avg. Download</p>
                                <h3 class="text-2xl font-bold text-blue-600">{{ formatSpeed(summary.avgRx) }}</h3>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <CircleArrowDown class="w-5 h-5" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border border-border/40 shadow-sm overflow-hidden bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500 mb-1">Avg. Upload</p>
                                <h3 class="text-2xl font-bold text-emerald-600">{{ formatSpeed(summary.avgTx) }}</h3>
                            </div>
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                                <CircleArrowUp class="w-5 h-5" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border border-border/40 shadow-sm overflow-hidden bg-card rounded-xl">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500 mb-1">Peak Download</p>
                                <h3 class="text-2xl font-bold text-indigo-600">{{ formatSpeed(summary.peakRx) }}</h3>
                            </div>
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 text-indigo-100">
                                <Activity class="w-5 h-5" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm overflow-hidden bg-card" v-for="node in slaData" :key="node.node_id">
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500 mb-1">Network Availability</p>
                                <h3 class="text-2xl font-bold text-amber-600">{{ node.availability_pct }}%</h3>
                            </div>
                            <div :class="`p-2 rounded-lg ${node.availability_pct > 99 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`">
                                <ShieldCheck class="w-5 h-5" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Details Table -->
            <Card class="border border-border/40 shadow-sm bg-card rounded-xl overflow-hidden">
                <CardHeader class="border-b bg-slate-50/50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-lg font-semibold flex items-center gap-2">
                             <FileText class="w-5 h-5 text-blue-500" />
                             {{ t('isp.monitor.traffic_details', 'Rincian Traffic') }}
                        </CardTitle>
                        <Badge variant="outline" class="font-normal">
                             {{ getRouterName(selectedRouter) }} - {{ selectedInterface }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[250px]">{{ t('common.time', 'Waktu') }}</TableHead>
                                <TableHead class="text-right">Avg. Rx (Download)</TableHead>
                                <TableHead class="text-right">Avg. Tx (Upload)</TableHead>
                                <TableHead class="text-right">Peak Rx</TableHead>
                                <TableHead class="text-right">Peak Tx</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="row in trafficData" :key="row.bucket">
                                <TableCell class="font-medium">{{ formatDate(row.bucket) }}</TableCell>
                                <TableCell class="text-right text-blue-600">{{ formatSpeed(parseFloat(row.avg_rx)) }}</TableCell>
                                <TableCell class="text-right text-emerald-600">{{ formatSpeed(parseFloat(row.avg_tx)) }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ formatSpeed(parseFloat(row.max_rx)) }}</TableCell>
                                <TableCell class="text-right font-semibold">{{ formatSpeed(parseFloat(row.max_tx)) }}</TableCell>
                            </TableRow>
                            <TableRow v-if="trafficData.length === 0">
                                <TableCell colspan="5" class="text-center py-12 text-slate-400 italic">
                                    {{ t('common.no_data', 'Tidak ada data pada periode ini.') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- SLA Table -->
            <Card v-if="slaData.length > 0" class="border border-border/40 shadow-sm bg-card rounded-xl overflow-hidden">
                <CardHeader class="border-b bg-slate-50/50 px-6 py-4">
                    <CardTitle class="text-lg font-semibold flex items-center gap-2">
                        <ShieldCheck class="w-5 h-5 text-amber-500" />
                        {{ t('isp.monitor.sla_health', 'SLA & Health Status') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead>{{ t('isp.infra.node', 'Node Name') }}</TableHead>
                                <TableHead class="text-center">{{ t('isp.monitor.availability', 'Availaibility') }}</TableHead>
                                <TableHead class="text-center">{{ t('isp.monitor.samples', 'Samples') }}</TableHead>
                                <TableHead class="text-center">{{ t('isp.monitor.online', 'Online') }}</TableHead>
                                <TableHead class="text-right">{{ t('isp.monitor.avg_latency', 'Avg. Latency') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="node in slaData" :key="node.node_id">
                                <TableCell class="font-medium">{{ node.node_name }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge :class="node.availability_pct > 99 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                        {{ node.availability_pct }}%
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center">{{ node.total_samples }}</TableCell>
                                <TableCell class="text-center text-green-600">{{ node.online_samples }}</TableCell>
                                <TableCell class="text-right">{{ node.avg_latency.toFixed(2) }} ms</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading" class="flex flex-col items-center justify-center py-24 px-4 bg-card rounded-xl border border-dashed border-border print:hidden">
            <div class="p-4 bg-slate-50 rounded-full mb-4">
                <FileText class="w-12 h-12 text-slate-300" />
            </div>
            <h3 class="text-xl font-semibold text-slate-900">{{ t('isp.monitor.no_report', 'Dapatkan Laporan Performa') }}</h3>
            <p class="text-slate-500 text-center max-w-sm mt-2">
                Pilih router, interface, dan rentang tanggal untuk generate laporan traffic dan ketersediaan layanan.
            </p>
        </div>

        <!-- Print Footer -->
        <div class="hidden print:block mt-12 border-t pt-8">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="font-bold mb-12">Disiapkan Oleh:</h4>
                    <div class="border-b w-64 mb-2"></div>
                    <p class="text-sm font-medium">Infrastructure Analyst</p>
                </div>
                <div class="text-right">
                    <h4 class="font-bold mb-12">Disetujui Oleh:</h4>
                    <div class="border-b w-64 mb-2 ml-auto"></div>
                    <p class="text-sm font-medium">Head of Network Operations</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none !important;
    }
    .print\:block {
        display: block !important;
    }
    .print\:bg-white {
        background-color: white !important;
    }
    .print\:p-0 {
        padding: 0 !important;
    }
    body {
        margin: 0;
        color: #000;
        background-color: #fff;
    }
    .Card {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
    }
}
</style>
