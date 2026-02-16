<template>
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ $t('isp.monitor.traffic.title', 'Traffic Monitor') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.monitor.traffic.subtitle', 'Real-time interface bandwidth monitoring') }}</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <select v-model="selectedRouterId" class="border border-border/40 rounded-xl px-3 py-2 bg-background text-foreground min-w-[200px]">
                    <option :value="null" disabled>{{ $t('isp.monitor.traffic.select_router', 'Select Router') }}</option>
                    <option v-for="router in routers" :key="router.id" :value="router.id">
                        {{ router.name }} ({{ router.ip_address }})
                    </option>
                </select>

                <select v-model="selectedInterface" class="border border-border/40 rounded-xl px-3 py-2 bg-background text-foreground min-w-[180px]" :disabled="!selectedRouterId">
                    <option value="">{{ $t('isp.monitor.traffic.select_interface', 'Select Interface') }}</option>
                    <option v-for="iface in interfaces" :key="iface.name" :value="iface.name">
                        {{ iface.name }} ({{ iface.type }})
                    </option>
                </select>

                <Button @click="toggleMonitoring" :variant="isMonitoring ? 'destructive' : 'default'" :disabled="!selectedInterface" class="rounded-xl">
                    <component :is="isMonitoring ? PauseIcon : PlayIcon" class="w-4 h-4 mr-2" />
                    {{ isMonitoring ? $t('common.actions.stop', 'Stop') : $t('common.actions.start', 'Start') }}
                </Button>

                <Button variant="outline" @click="router.push({ name: 'isp.monitor.report' })" class="rounded-xl">
                    <FileText class="w-4 h-4 mr-2 text-blue-500" />
                    {{ $t('isp.monitor.traffic.view_report', 'Laporan') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Download Card -->
            <Card class="overflow-hidden border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-green-50 to-white dark:from-green-950/20 dark:to-background">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-green-600 dark:text-green-400 mb-1">{{ t('isp.monitor.traffic.download', 'Download (Rx)') }}</p>
                            <h3 class="text-2xl font-bold font-mono tracking-tight">{{ formatSpeed(currentRx) }}</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
                                <span class="flex items-center"><TrendingUp class="w-3 h-3 mr-1" /> {{ formatSpeed(peakRx) }} Peak</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-green-500/10 text-green-600 dark:text-green-400">
                            <ArrowDownLeft class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
                <div class="h-1 bg-green-500/20">
                    <div class="h-full bg-green-500 transition-all duration-500" :style="{ width: `${Math.min((currentRx / (peakRx || 1)) * 100, 100)}%` }"></div>
                </div>
            </Card>

            <!-- Upload Card -->
            <Card class="overflow-hidden border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/20 dark:to-background">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mb-1">{{ t('isp.monitor.traffic.upload', 'Upload (Tx)') }}</p>
                            <h3 class="text-2xl font-bold font-mono tracking-tight">{{ formatSpeed(currentTx) }}</h3>
                            <div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
                                <span class="flex items-center"><TrendingUp class="w-3 h-3 mr-1" /> {{ formatSpeed(peakTx) }} Peak</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <ArrowUpRight class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
                <div class="h-1 bg-blue-500/20">
                    <div class="h-full bg-blue-500 transition-all duration-500" :style="{ width: `${Math.min((currentTx / (peakTx || 1)) * 100, 100)}%` }"></div>
                </div>
            </Card>

            <!-- Average Speed Card -->
            <Card class="overflow-hidden border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/20 dark:to-background">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-purple-600 dark:text-purple-400 mb-1">{{ t('isp.monitor.traffic.average', 'Average Speed') }}</p>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-muted-foreground">RX:</span>
                                    <span class="text-sm font-bold font-mono">{{ formatSpeed(avgRx) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-muted-foreground">TX:</span>
                                    <span class="text-sm font-bold font-mono">{{ formatSpeed(avgTx) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                            <Activity class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Session Data Card -->
            <Card class="overflow-hidden border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-orange-50 to-white dark:from-orange-950/20 dark:to-background">
                <CardContent class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold text-orange-600 dark:text-orange-400 mb-1">{{ t('isp.monitor.traffic.session_total', 'Session Total') }}</p>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-muted-foreground">RX:</span>
                                    <span class="text-sm font-bold font-mono">{{ formatBytes(totalRxBytes) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs text-muted-foreground">TX:</span>
                                    <span class="text-sm font-bold font-mono">{{ formatBytes(totalTxBytes) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400">
                            <Database class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Chart -->
        <Card class="border border-border/40 shadow-sm rounded-xl">
            <CardHeader class="border-b border-border/40">
                <CardTitle>{{ $t('isp.monitor.traffic.realtime_chart', 'Real-time Bandwidth') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="!selectedInterface" class="text-center py-12 text-muted-foreground">
                    <Activity class="w-12 h-12 mx-auto mb-3 opacity-50" />
                    <p>{{ $t('isp.monitor.traffic.no_interface', 'Select an interface to start monitoring') }}</p>
                </div>
                <div v-else class="h-[350px]">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { Button, Card, CardContent, CardHeader, CardTitle } from '@/components/ui';
import Chart from 'chart.js/auto';
import ArrowDownLeft from 'lucide-vue-next/dist/esm/icons/arrow-down-left.js';
import ArrowUpRight from 'lucide-vue-next/dist/esm/icons/arrow-up-right.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import PlayIcon from 'lucide-vue-next/dist/esm/icons/play.js';
import PauseIcon from 'lucide-vue-next/dist/esm/icons/pause.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';

const router = useRouter();
const { t } = useI18n();
const toast = useToast();

interface RouterInterface {
    name: string;
    type: string;
    running: boolean;
    disabled: boolean;
}

interface Router {
    id: number;
    name: string;
    ip_address: string;
}

const routers = ref<Router[]>([]);
const selectedRouterId = ref<number | null>(null);
const interfaces = ref<RouterInterface[]>([]);
const selectedInterface = ref('');
const isMonitoring = ref(false);
const currentRx = ref(0);
const currentTx = ref(0);
const peakRx = ref(0);
const peakTx = ref(0);
const avgRx = ref(0);
const avgTx = ref(0);
const totalRxBytes = ref(0);
const totalTxBytes = ref(0);

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const MAX_DATA_POINTS = 30;
const trafficHistory = ref<{ time: string; rx: number; tx: number }[]>([]);

const formatSpeed = (bps: number): string => {
    if (bps === 0) return '0 bps';
    if (bps >= 1000000000) return `${(bps / 1000000000).toFixed(2)} Gbps`;
    if (bps >= 1000000) return `${(bps / 1000000).toFixed(2)} Mbps`;
    if (bps >= 1000) return `${(bps / 1000).toFixed(2)} Kbps`;
    return `${bps.toFixed(0)} bps`;
};

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

const fetchRouters = async () => {
    try {
        const res = await api.get('/admin/janet/isp/routers'); // Corrected endpoint: remove /network/
        
        // Validated structure from backend:
        // res.data = { success: true, message: "...", data: { current_page: 1, data: [ ... routers ... ], ... } }
        const payload = res.data;
        let items: Router[] = [];

        if (payload.data && payload.data.data && Array.isArray(payload.data.data)) {
            items = payload.data.data;
        } else if (payload.data && Array.isArray(payload.data)) {
            items = payload.data;
        } else if (Array.isArray(payload)) {
            items = payload;
        }

        routers.value = items;
        
        if (routers.value.length > 0 && !selectedRouterId.value) {
            selectedRouterId.value = routers.value[0].id;
        }
    } catch (error) {
        console.error('Failed to fetch routers', error);
        toast.error.load(error);
    }
};

const fetchInterfaces = async () => {
    if (!selectedRouterId.value) return;
    
    try {
        const res = await api.get('/admin/janet/isp/monitor/interfaces', {
            params: { router_id: selectedRouterId.value }
        });
        interfaces.value = res.data.data.filter((i: RouterInterface) => !i.disabled);
    } catch (error) {
        console.error('Failed to fetch interfaces', error);
        toast.error.load(error);
        interfaces.value = [];
    }
};

const fetchTraffic = async () => {
    if (!selectedInterface.value || !selectedRouterId.value) return;
    try {
        const res = await api.get('/admin/janet/isp/monitor/traffic', {
            params: { 
                interface: selectedInterface.value,
                router_id: selectedRouterId.value
            }
        });
        const { rx, tx } = res.data.data; // Backend returns bits-per-second
        currentRx.value = rx;
        currentTx.value = tx;
        if (rx > peakRx.value) peakRx.value = rx;
        if (tx > peakTx.value) peakTx.value = tx;

        // Accumulate total bytes (assuming 2s interval)
        // bps * 2s / 8 = Bytes consumed in 2 seconds
        totalRxBytes.value += (rx * 2) / 8;
        totalTxBytes.value += (tx * 2) / 8;

        trafficHistory.value.push({
            time: new Date().toLocaleTimeString('en-US', { hour12: false }),
            rx: rx / 1000000, // For chart (Mbps)
            tx: tx / 1000000  // For chart (Mbps)
        });

        // Calculate average from history
        if (trafficHistory.value.length > 0) {
            const sumRx = trafficHistory.value.reduce((acc, curr) => acc + curr.rx, 0);
            const sumTx = trafficHistory.value.reduce((acc, curr) => acc + curr.tx, 0);
            avgRx.value = sumRx / trafficHistory.value.length;
            avgTx.value = sumTx / trafficHistory.value.length;
        }

        if (trafficHistory.value.length > MAX_DATA_POINTS) {
            trafficHistory.value.shift();
        }
        updateChart();
    } catch (error) {
        console.error('Failed to fetch traffic', error);
    }
};

const toggleMonitoring = () => {
    if (isMonitoring.value) {
        stopMonitoring();
    } else {
        startMonitoring();
    }
};

const startMonitoring = () => {
    if (!selectedInterface.value) {
        toast.error.validation(t('isp.monitor.traffic.select_interface', 'Select an interface first'));
        return;
    }
    isMonitoring.value = true;
    trafficHistory.value = [];
    peakRx.value = 0;
    peakTx.value = 0;
    avgRx.value = 0;
    avgTx.value = 0;
    totalRxBytes.value = 0;
    totalTxBytes.value = 0;
    fetchTraffic();
    pollingInterval = setInterval(fetchTraffic, 2000);
};

const stopMonitoring = () => {
    isMonitoring.value = false;
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

const initChart = async () => {
    await nextTick();
    if (!chartCanvas.value) return;

    chartInstance = new Chart(chartCanvas.value, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Download (Mbps)',
                    data: [],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: 'Upload (Mbps)',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 300 },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Mbps' } },
                x: { title: { display: true, text: 'Time' } }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
};

const updateChart = () => {
    if (!chartInstance) return;
    chartInstance.data.labels = trafficHistory.value.map(h => h.time);
    chartInstance.data.datasets[0].data = trafficHistory.value.map(h => h.rx);
    chartInstance.data.datasets[1].data = trafficHistory.value.map(h => h.tx);
    chartInstance.update('none');
};

watch(selectedRouterId, async (val) => {
    stopMonitoring();
    selectedInterface.value = '';
    interfaces.value = [];
    if (val) {
        await fetchInterfaces();
    }
});

watch(selectedInterface, async (val) => {
    stopMonitoring();
    trafficHistory.value = [];
    if (val) {
        await nextTick();
        if (!chartInstance) initChart();
    }
});

onMounted(async () => {
    await fetchRouters();
    if (selectedRouterId.value) {
        await fetchInterfaces();
    }
});

onUnmounted(() => {
    stopMonitoring();
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>
