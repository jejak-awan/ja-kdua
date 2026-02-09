<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.member.usage.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.member.usage.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2 bg-card border border-border p-1 rounded-xl">
                <Button 
                    variant="ghost" 
                    size="sm" 
                    :class="viewType === 'daily' ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                    @click="viewType = 'daily'"
                >
                    {{ t('isp.member.usage.last_24h') }}
                </Button>
                <Button 
                    variant="ghost" 
                    size="sm" 
                    :class="viewType === 'monthly' ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''"
                    @click="viewType = 'monthly'"
                >
                    {{ t('isp.member.usage.last_30d') }}
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card class="rounded-2xl shadow-sm border-border bg-card/50 backdrop-blur-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('isp.member.usage.total_down') }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stats.totalDown }} GB</h3>
                        </div>
                        <div class="p-2 bg-blue-500/10 rounded-xl">
                            <ArrowDown class="w-5 h-5 text-blue-500" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-2xl shadow-sm border-border bg-card/50 backdrop-blur-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('isp.member.usage.total_up') }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stats.totalUp }} GB</h3>
                        </div>
                        <div class="p-2 bg-orange-500/10 rounded-xl">
                            <ArrowUp class="w-5 h-5 text-orange-500" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-2xl shadow-sm border-border bg-card/50 backdrop-blur-sm">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('isp.member.usage.peak_speed') }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stats.peakSpeed }} Mbps</h3>
                        </div>
                        <div class="p-2 bg-primary/10 rounded-xl">
                            <Zap class="w-5 h-5 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Charts -->
        <Card class="rounded-2xl border-border shadow-sm overflow-hidden">
            <CardHeader>
                <CardTitle class="text-lg flex items-center gap-2">
                    <Activity class="w-5 h-5 text-primary" />
                    {{ viewType === 'daily' ? t('isp.member.usage.chart_bandwidth') : t('isp.member.usage.chart_volume') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="p-6">
                <div v-if="loading" class="h-80 flex items-center justify-center">
                    <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                </div>
                <div v-else class="h-80">
                    <Line 
                        :data="chartData" 
                        :options="chartOptions"
                        class="w-full h-full"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Current Session Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card class="rounded-2xl border-border shadow-sm">
                <CardHeader>
                    <CardTitle class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">{{ t('isp.member.usage.realtime_conn') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl">
                        <div class="flex items-center gap-3">
                            <Globe class="w-5 h-5" :class="usageData?.connection?.status === 'connected' ? 'text-success' : 'text-muted-foreground'" />
                            <span class="font-medium">{{ t('isp.member.usage.status') }}</span>
                        </div>
                        <Badge :variant="usageData?.connection?.status === 'connected' ? 'success' : 'destructive'">
                            {{ usageData?.connection?.status === 'connected' ? t('isp.member.usage.connected') : t('isp.member.usage.offline') }}
                        </Badge>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl">
                        <div class="flex items-center gap-3">
                            <Activity class="w-5 h-5 text-primary" />
                            <span class="font-medium">{{ t('isp.member.usage.latency') }}</span>
                        </div>
                        <span class="font-mono text-sm">{{ usageData?.connection?.latency || '---' }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-2xl border-border shadow-sm">
                <CardHeader>
                    <CardTitle class="text-sm font-semibold uppercase tracking-wider text-muted-foreground text-destructive">{{ t('isp.member.usage.need_speed_title') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground mb-4">{{ t('isp.member.usage.need_speed_desc') }}</p>
                    <Button variant="outline" class="w-full rounded-xl gap-2 hover:bg-destructive hover:text-white border-destructive text-destructive transition-colors">
                        <ArrowUp class="w-4 h-4" />
                        {{ t('isp.member.usage.upgrade_button') }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { 
    Card, CardHeader, CardTitle, CardContent, 
    Button, Badge 
} from '@/components/ui';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    type ChartData,
    type ChartOptions
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

import ArrowDown from 'lucide-vue-next/dist/esm/icons/arrow-down.js';
import ArrowUp from 'lucide-vue-next/dist/esm/icons/arrow-up.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import type { IspUsageData, IspTrafficData } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();

const loading = ref(true);
const viewType = ref<'daily' | 'monthly'>('daily');
const usageData = ref<IspUsageData | null>(null);

const stats = computed(() => {
    if (!usageData.value?.usage) return { totalDown: '0', totalUp: '0', peakSpeed: '0' };
    
    if (viewType.value === 'daily') {
        const data = usageData.value.usage.daily;
        const totalDownRaw = data.reduce((acc, curr) => acc + curr.down, 0); // MB
        const totalUpRaw = data.reduce((acc, curr) => acc + curr.up, 0); // MB
        const peak = Math.max(...data.map(d => d.down));
        return { 
            totalDown: (totalDownRaw / 1024).toFixed(2), 
            totalUp: (totalUpRaw / 1024).toFixed(2), 
            peakSpeed: peak.toFixed(1) 
        };
    } else {
        const data = usageData.value.usage.monthly;
        const totalDown = data.reduce((acc, curr) => acc + curr.down, 0);
        const totalUp = data.reduce((acc, curr) => acc + curr.up, 0);
        const peak = Math.max(...data.map(d => d.down * 8)); // rough speed estimate
        return { 
            totalDown: totalDown.toFixed(1), 
            totalUp: totalUp.toFixed(1), 
            peakSpeed: peak.toFixed(0) 
        };
    }
});

const chartData = computed<ChartData<'line'>>(() => {
    if (!usageData.value?.usage) return { labels: [], datasets: [] };

    const data = viewType.value === 'daily' ? usageData.value.usage.daily : usageData.value.usage.monthly;
    const labels = data.map((d: IspTrafficData) => (viewType.value === 'daily' ? d.time : d.date) || '');

    return {
        labels,
        datasets: [
            {
                label: t('common.labels.download'),
                data: data.map(d => d.down),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: t('common.labels.upload'),
                data: data.map(d => d.up),
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    };
});

const chartOptions: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(156, 163, 175, 0.1)'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};

const fetchUsage = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/member/usage');
        if (response.data.success) {
            usageData.value = response.data.data;
        }
    } catch (_error) {
        toast.error.default(t('isp.member.usage.error_load'));
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchUsage();
});
</script>
