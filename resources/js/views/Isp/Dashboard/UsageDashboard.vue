<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.analytics.usage_title', 'Network Usage Dashboard') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.analytics.usage_subtitle', 'Live network performance and bandwidth consumption metrics') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Button variant="outline" size="sm" class="rounded-xl" @click="fetchData" :disabled="loading">
                    <RefreshCw :class="['w-4 h-4 mr-2', loading ? 'animate-spin' : '']" />
                    {{ $t('common.actions.refresh', 'Refresh Data') }}
                </Button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card v-for="stat in quickStats" :key="stat.label" class="border border-border/40 shadow-sm rounded-xl">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-muted-foreground">{{ stat.label }}</p>
                            <h3 class="text-2xl font-bold mt-1">{{ stat.value }}</h3>
                        </div>
                        <div :class="['p-2 rounded-xl', stat.color]">
                            <component :is="stat.icon" class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Traffic Chart -->
            <Card class="lg:col-span-2 border border-border/40 shadow-sm rounded-xl">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>{{ $t('isp.admin.analytics.traffic_chart', 'Bandwidth Trends') }}</CardTitle>
                        <CardDescription>{{ $t('isp.admin.analytics.traffic_chart_desc', 'Aggregated real-time inbound and outbound traffic') }}</CardDescription>
                    </div>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="h-[350px]">
                        <Line v-if="!loading" :data="chartData" :options="chartOptions" />
                        <div v-else class="h-full flex items-center justify-center">
                            <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Top Talkers -->
            <Card class="border border-border/40 shadow-sm rounded-xl">
                <CardHeader>
                    <CardTitle>{{ $t('isp.admin.analytics.top_talkers', 'Top Talkers') }}</CardTitle>
                    <CardDescription>{{ $t('isp.admin.analytics.top_talkers_desc', 'Highest bandwidth consumers this month') }}</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="divide-y divide-border/40">
                        <div v-for="customer in topTalkers" :key="customer.username" class="flex items-center justify-between p-4 hover:bg-muted/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold">
                                    {{ customer.name ? customer.name.charAt(0) : '?' }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ customer.name }}</p>
                                    <p class="text-[10px] text-muted-foreground">User: {{ customer.username }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold">{{ customer.usage_gb }} GB</p>
                                <Badge variant="outline" class="text-[9px] h-4 rounded-lg">{{ customer.status }}</Badge>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-border/40">
                        <router-link :to="{ name: 'isp-subscription-customers' }">
                            <Button variant="ghost" size="sm" class="w-full text-xs rounded-xl">
                                {{ $t('common.actions.view_all', 'View All Customers') }}
                            </Button>
                        </router-link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Card, CardHeader, CardTitle, CardDescription, CardContent, 
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
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const stats = ref<any>(null);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const usage = ref<any>(null);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const topTalkers = ref<any[]>([]);

const quickStats = computed(() => [
    { 
        label: t('isp.admin.analytics.traffic_in', 'Network In'), 
        value: stats.value?.network?.total_traffic_in + ' Mbps', 
        icon: ArrowDown, 
        color: 'bg-blue-500/10 text-blue-500' 
    },
    { 
        label: t('isp.admin.analytics.traffic_out', 'Network Out'), 
        value: stats.value?.network?.total_traffic_out + ' Mbps', 
        icon: ArrowUp, 
        color: 'bg-orange-500/10 text-orange-500' 
    },
    { 
        label: t('isp.admin.analytics.active_customers', 'Active Customers'), 
        value: stats.value?.customers?.active || '0', 
        icon: Users, 
        color: 'bg-green-500/10 text-green-500' 
    },
    { 
        label: t('isp.admin.analytics.node_health', 'Node Health'), 
        value: stats.value?.network?.uptime_percentage + '%', 
        icon: Database, 
        color: 'bg-purple-500/10 text-purple-500' 
    }
]);

const chartData = computed<ChartData<'line'>>(() => {
    if (!usage.value?.history) return { labels: [], datasets: [] };
    
    return {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        labels: usage.value.history.map((h: any) => h.time),
        datasets: [
            {
                label: t('common.labels.inbound', 'Inbound'),
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                data: usage.value.history.map((h: any) => h.up),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: t('common.labels.outbound', 'Outbound'),
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                data: usage.value.history.map((h: any) => h.down),
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
        legend: { position: 'top' as const }
    },
    scales: {
        y: { beginAtZero: true },
        x: { grid: { display: false } }
    }
};

const fetchData = async () => {
    loading.value = true;
    try {
        const [statsRes, usageRes, talkersRes] = await Promise.all([
            api.get('/admin/janet/isp/analytics'),
            api.get('/admin/janet/isp/analytics/usage'),
            api.get('/admin/janet/isp/analytics/top-talkers')
        ]);
        
        stats.value = statsRes.data.data;
        usage.value = usageRes.data.data;
        topTalkers.value = talkersRes.data.data;
    } catch (error) {
        console.error('Failed to fetch analytics data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchData);
</script>
