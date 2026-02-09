<template>
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.monitor.traffic.title', 'Traffic Monitor') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.monitor.traffic.subtitle', 'Real-time interface bandwidth monitoring') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <select v-model="selectedInterface" class="border border-border rounded-lg px-3 py-2 bg-background text-foreground min-w-[180px]">
                    <option value="">{{ $t('isp.monitor.traffic.select_interface', 'Select Interface') }}</option>
                    <option v-for="iface in interfaces" :key="iface.name" :value="iface.name">
                        {{ iface.name }} ({{ iface.type }})
                    </option>
                </select>
                <Button @click="toggleMonitoring" :variant="isMonitoring ? 'destructive' : 'default'">
                    <component :is="isMonitoring ? PauseIcon : PlayIcon" class="w-4 h-4 mr-2" />
                    {{ isMonitoring ? $t('common.actions.stop', 'Stop') : $t('common.actions.start', 'Start') }}
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/50">
                            <ArrowDownLeft class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ $t('isp.monitor.traffic.download', 'Download') }}</p>
                            <p class="text-xl font-bold">{{ formatSpeed(currentRx) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <ArrowUpRight class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ $t('isp.monitor.traffic.upload', 'Upload') }}</p>
                            <p class="text-xl font-bold">{{ formatSpeed(currentTx) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/50">
                            <TrendingUp class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ $t('isp.monitor.traffic.peak_rx', 'Peak Download') }}</p>
                            <p class="text-xl font-bold">{{ formatSpeed(peakRx) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/50">
                            <TrendingUp class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">{{ $t('isp.monitor.traffic.peak_tx', 'Peak Upload') }}</p>
                            <p class="text-xl font-bold">{{ formatSpeed(peakTx) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Chart -->
        <Card>
            <CardHeader>
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

const { t } = useI18n();
const toast = useToast();

interface RouterInterface {
    name: string;
    type: string;
    running: boolean;
    disabled: boolean;
}

const interfaces = ref<RouterInterface[]>([]);
const selectedInterface = ref('');
const isMonitoring = ref(false);
const currentRx = ref(0);
const currentTx = ref(0);
const peakRx = ref(0);
const peakTx = ref(0);
const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const MAX_DATA_POINTS = 30;
const trafficHistory = ref<{ time: string; rx: number; tx: number }[]>([]);

const formatSpeed = (mbps: number): string => {
    if (mbps >= 1000) return `${(mbps / 1000).toFixed(2)} Gbps`;
    return `${mbps.toFixed(2)} Mbps`;
};

const fetchInterfaces = async () => {
    try {
        const res = await api.get('/admin/ja/isp/hotspot/interfaces');
        interfaces.value = res.data.data.filter((i: RouterInterface) => i.running && !i.disabled);
    } catch (error) {
        console.error('Failed to fetch interfaces', error);
        toast.error.load(error);
    }
};

const fetchTraffic = async () => {
    if (!selectedInterface.value) return;
    try {
        const res = await api.get('/admin/ja/isp/hotspot/interface-traffic', {
            params: { interface: selectedInterface.value }
        });
        const { rx, tx } = res.data.data;
        currentRx.value = rx;
        currentTx.value = tx;
        if (rx > peakRx.value) peakRx.value = rx;
        if (tx > peakTx.value) peakTx.value = tx;

        trafficHistory.value.push({
            time: new Date().toLocaleTimeString('en-US', { hour12: false }),
            rx,
            tx
        });
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

watch(selectedInterface, async (val) => {
    stopMonitoring();
    trafficHistory.value = [];
    if (val) {
        await nextTick();
        if (!chartInstance) initChart();
    }
});

onMounted(() => {
    fetchInterfaces();
});

onUnmounted(() => {
    stopMonitoring();
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>
