<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.monitor.traffic.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.monitor.traffic.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Badge variant="outline" class="animate-pulse">
                    <div class="w-2 h-2 rounded-full bg-success mr-2"></div>
                    Live
                </Badge>
                <Select v-model="selectedInterface">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="t('isp.monitor.traffic.select_interface')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="ether1-gateway">ether1-gateway</SelectItem>
                        <SelectItem value="ether2-master">ether2-master</SelectItem>
                        <SelectItem value="vlan-100">vlan-100-customers</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Card class="p-6">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">{{ t('isp.monitor.traffic.download_rate') }}</h3>
                    <span class="text-2xl font-bold font-mono">{{ currentIn }} <span class="text-sm font-normal text-muted-foreground">Mbps</span></span>
                </div>
                <!-- Mini sparkline or similar could go here -->
            </Card>
            <Card class="p-6">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-medium text-muted-foreground">{{ t('isp.monitor.traffic.upload_rate') }}</h3>
                    <span class="text-2xl font-bold font-mono">{{ currentOut }} <span class="text-sm font-normal text-muted-foreground">Mbps</span></span>
                </div>
            </Card>
        </div>

        <Card class="p-6">
            <div class="h-[400px] w-full">
                <LineChart 
                    v-if="chartData.length > 0"
                    :data="chartData"
                    :label="t('isp.monitor.traffic.download')"
                    :compare-data="compareData"
                    :compare-label="t('isp.monitor.traffic.upload')"
                />
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Badge, Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui';
import LineChart from '@/components/charts/LineChart.vue';

const { t } = useI18n();

const selectedInterface = ref('ether1-gateway');
const currentIn = ref(0);
const currentOut = ref(0);
const history = ref<{time: string, rx: number, tx: number}[]>([]);
let interval: ReturnType<typeof setInterval>;

// Initialize with some empty data
const initData = () => {
    const now = new Date();
    const data = [];
    for (let i = 20; i > 0; i--) {
        const time = new Date(now.getTime() - i * 1000);
        data.push({
            time: time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
            rx: 0,
            tx: 0
        });
    }
    history.value = data;
};

import api from '@/services/api';

const updateData = async () => {
    try {
        const response = await api.get('/isp/monitor/traffic', {
            params: { interface: selectedInterface.value }
        });
        
        const { rx, tx } = response.data.data; // Adjusted for BaseApiController response structure
        const now = new Date();

        currentIn.value = rx;
        currentOut.value = tx;

        history.value.push({
            time: now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
            rx: rx,
            tx: tx
        });

        if (history.value.length > 30) {
            history.value.shift();
        }
    } catch (error) {
        console.error('Failed to fetch traffic data', error);
    }
};

const chartData = computed(() => {
    return history.value.map(h => ({
        period: h.time,
        visits: h.rx,
        label: 'Rx'
    }));
});

const compareData = computed(() => {
    return history.value.map(h => ({
        period: h.time,
        visits: h.tx,
        label: 'Tx'
    }));
});

watch(selectedInterface, () => {
    // Reset data on interface change to simulate switch
    currentIn.value = Math.random() * 100;
    currentOut.value = Math.random() * 20;
    initData();
});

onMounted(() => {
    initData();
    interval = setInterval(updateData, 1000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>
