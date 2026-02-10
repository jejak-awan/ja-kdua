<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.analytics.revenue_title', 'Revenue & Billing Reports') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.analytics.revenue_subtitle', 'Financial performance and payment collection insights') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Button variant="outline" size="sm" @click="fetchRevenue" :disabled="loading">
                    <Download class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.export', 'Export Report') }}
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card class="border-border/40 shadow-sm bg-primary/5">
                <CardContent class="p-6">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('isp.admin.analytics.collection_rate', 'Collection Rate') }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-3xl font-bold">{{ revenueData?.collection_rate || 0 }}%</h3>
                        <div class="text-[10px] text-primary flex items-center gap-1 mb-1">
                            <TrendingUp class="w-3 h-3" />
                            +2.4% vs last month
                        </div>
                    </div>
                    <div class="w-full bg-muted h-1.5 rounded-full mt-4 overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-1000" :style="{ width: (revenueData?.collection_rate || 0) + '%' }"></div>
                    </div>
                </CardContent>
            </Card>

            <Card class="border-border/40 shadow-sm">
                <CardContent class="p-6">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('isp.admin.analytics.total_outstanding', 'Total Outstanding') }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-3xl font-bold text-destructive">Rp{{ formatCurrency(revenueData?.total_outstanding || 0) }}</h3>
                        <span class="text-[10px] text-muted-foreground mb-1">Across all pending invoices</span>
                    </div>
                    <Button variant="link" size="sm" class="h-auto p-0 mt-4 text-xs" @click="viewUnpaid">
                        {{ $t('isp.admin.analytics.view_unpaid', 'Identify Delinquent Customers') }}
                    </Button>
                </CardContent>
            </Card>

            <Card class="border-border/40 shadow-sm">
                <CardContent class="p-6">
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $t('isp.admin.analytics.projected_revenue', 'Growth Target') }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-3xl font-bold text-success">115.0%</h3>
                        <div class="text-[10px] text-success flex items-center gap-1 mb-1">
                            <Zap class="w-3 h-3" />
                            On track
                        </div>
                    </div>
                    <p class="text-[10px] text-muted-foreground mt-4">Revenue is trending upward with 12 new active subscriptions this week.</p>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Revenue Trend -->
            <Card class="border-border/40 shadow-sm">
                <CardHeader>
                    <CardTitle>{{ $t('isp.admin.analytics.monthly_revenue', 'Monthly Revenue Trend') }}</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="h-[300px]">
                        <Line v-if="!loading" :data="revenueChartData" :options="chartOptions" />
                        <div v-else class="h-full flex items-center justify-center">
                            <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payment Status Breakdown -->
            <Card class="border-border/40 shadow-sm">
                <CardHeader>
                    <CardTitle>{{ $t('isp.admin.analytics.payment_summary', 'Payment Collection Summary') }}</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-muted-foreground uppercase bg-muted/30">
                                <tr>
                                    <th class="px-6 py-3 font-medium">{{ $t('common.labels.month', 'Month') }}</th>
                                    <th class="px-6 py-3 font-medium text-right">{{ $t('isp.admin.analytics.total_billed', 'Billed') }}</th>
                                    <th class="px-6 py-3 font-medium text-right text-success">{{ $t('isp.admin.analytics.total_collected', 'Collected') }}</th>
                                    <th class="px-6 py-3 font-medium text-right text-destructive">{{ $t('isp.admin.analytics.total_unpaid', 'Unpaid') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="month in revenueData?.monthly" :key="month.month" class="hover:bg-muted/20 transition-colors">
                                    <td class="px-6 py-4 font-medium">{{ month.month }}</td>
                                    <td class="px-6 py-4 text-right">Rp{{ formatCurrency(month.total) }}</td>
                                    <td class="px-6 py-4 text-right text-success">Rp{{ formatCurrency(month.collected) }}</td>
                                    <td class="px-6 py-4 text-right text-destructive">Rp{{ formatCurrency(month.outstanding) }}</td>
                                </tr>
                            </tbody>
                        </table>
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
    Card, CardHeader, CardTitle, CardContent, 
    Button
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

import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { useRouter } from 'vue-router';

const { t } = useI18n();
const router = useRouter();
const loading = ref(true);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const revenueData = ref<any>(null);

const revenueChartData = computed<ChartData<'line'>>(() => {
    if (!revenueData.value?.monthly) return { labels: [], datasets: [] };
    
    // Sort chronological for chart
    const data = [...revenueData.value.monthly].reverse();
    
    return {
        labels: data.map(m => m.month),
        datasets: [
            {
                label: t('isp.admin.analytics.total_billed', 'Total Billed'),
                data: data.map(m => m.total),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: t('isp.admin.analytics.total_collected', 'Collected'),
                data: data.map(m => m.collected),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
        y: { 
            beginAtZero: true,
            ticks: {
                callback: (value) => 'Rp' + formatCurrency(Number(value))
            }
        },
        x: { grid: { display: false } }
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const fetchRevenue = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/isp/analytics/revenue');
        revenueData.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch revenue data', error);
    } finally {
        loading.value = false;
    }
};

const viewUnpaid = () => {
    router.push({ name: 'isp-billing', query: { status: 'unpaid' } });
};

onMounted(fetchRevenue);
</script>
