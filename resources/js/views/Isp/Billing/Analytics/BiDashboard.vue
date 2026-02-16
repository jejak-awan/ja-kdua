<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.analytics.bi_title', 'Business Intelligence') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.analytics.bi_subtitle', 'Executive overview of key financial metrics and growth indicators') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <Button variant="outline" size="sm" @click="fetchAll" :disabled="loading">
                    <RefreshCw :class="['w-4 h-4 mr-2', loading ? 'animate-spin' : '']" />
                    {{ $t('common.actions.refresh', 'Refresh') }}
                </Button>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card class="border-border/40 shadow-sm bg-gradient-to-br from-blue-500/10 to-transparent">
                <CardContent class="p-6">
                    <p class="text-xs font-bold text-muted-foreground tracking-tight opacity-60">{{ $t('isp.admin.analytics.mrr', 'Monthly Recurring Revenue') }}</p>
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold">Rp{{ formatCurrency(bi.mrr || 0) }}</h3>
                    </div>
                    <div class="flex items-center gap-1 mt-3 text-xs text-blue-600">
                        <TrendingUp class="w-3 h-3" />
                        <span>MRR</span>
                    </div>
                </CardContent>
            </Card>
            <Card class="border-border/40 shadow-sm bg-gradient-to-br from-emerald-500/10 to-transparent">
                <CardContent class="p-6">
                    <p class="text-xs font-bold text-muted-foreground tracking-tight opacity-60">{{ $t('isp.admin.analytics.arpu', 'Average Revenue Per User') }}</p>
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold">Rp{{ formatCurrency(bi.arpu || 0) }}</h3>
                    </div>
                    <div class="flex items-center gap-1 mt-3 text-xs text-emerald-600">
                        <Users class="w-3 h-3" />
                        <span>ARPU</span>
                    </div>
                </CardContent>
            </Card>
            <Card class="border-border/40 shadow-sm bg-gradient-to-br from-purple-500/10 to-transparent">
                <CardContent class="p-6">
                    <p class="text-xs font-bold text-muted-foreground tracking-tight opacity-60">{{ $t('isp.admin.analytics.clv', 'Customer Lifetime Value') }}</p>
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold">Rp{{ formatCurrency(bi.clv || 0) }}</h3>
                    </div>
                    <div class="flex items-center gap-1 mt-3 text-xs text-purple-600">
                        <Gem class="w-3 h-3" />
                        <span>CLV</span>
                    </div>
                </CardContent>
            </Card>
            <Card class="border-border/40 shadow-sm bg-gradient-to-br from-amber-500/10 to-transparent">
                <CardContent class="p-6">
                    <p class="text-xs font-bold text-muted-foreground tracking-tight opacity-60">{{ $t('isp.admin.analytics.churn_risk', 'Churn Risk') }}</p>
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold" :class="(bi.churn_risk_count || 0) > 5 ? 'text-destructive' : ''">{{ bi.churn_risk_count || 0 }}</h3>
                    </div>
                    <div class="flex items-center gap-1 mt-3 text-xs text-amber-600">
                        <TriangleAlert class="w-3 h-3" />
                        <span>{{ $t('isp.admin.analytics.at_risk_customers', 'At-risk customers') }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Projection Chart -->
            <Card class="border-border/40 shadow-sm">
                <CardHeader>
                    <CardTitle>{{ $t('isp.admin.analytics.projections_title', '12-Month Revenue Projection') }}</CardTitle>
                    <CardDescription>{{ $t('isp.admin.analytics.projections_desc', 'Historical MRR and projected growth trajectory') }}</CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="h-[300px]">
                        <Line v-if="!loading && projectionChartData.labels?.length" :data="projectionChartData" :options="projectionChartOptions" />
                        <div v-else class="h-full flex items-center justify-center">
                            <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Customer Growth Chart -->
            <Card class="border-border/40 shadow-sm">
                <CardHeader>
                    <CardTitle>{{ $t('isp.admin.analytics.growth_title', 'Customer Growth Trends') }}</CardTitle>
                    <CardDescription>{{ $t('isp.admin.analytics.growth_desc', 'Net subscriber growth over the last 6 months') }}</CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="h-[300px]">
                        <Bar v-if="!loading && growthChartData.labels?.length" :data="growthChartData" :options="growthChartOptions" />
                        <div v-else class="h-full flex items-center justify-center">
                            <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Churn Risk Table -->
        <Card class="border-border/40 shadow-sm">
            <CardHeader>
                <CardTitle>{{ $t('isp.admin.analytics.churn_table_title', 'Churn Risk Analysis') }}</CardTitle>
                <CardDescription>{{ $t('isp.admin.analytics.churn_table_desc', 'Customers most likely to churn based on payment history and usage patterns') }}</CardDescription>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground bg-muted/30">
                            <tr>
                                <th class="px-6 py-3 font-medium">{{ $t('isp.billing.customers_manager.fields.name', 'Customer') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('isp.customers.fields.plan', 'Plan') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('isp.admin.analytics.risk_score', 'Risk Score') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('isp.admin.analytics.risk_reason', 'Risk Reason') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="customer in churnRisk" :key="customer.id || customer.customer_id" class="hover:bg-muted/20 transition-colors">
                                <td class="px-6 py-4 font-medium">{{ customer.name || customer.customer_name || '-' }}</td>
                                <td class="px-6 py-4 text-muted-foreground">{{ customer.plan || customer.plan_name || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Badge :variant="getRiskVariant(customer.risk_score || customer.score || 0)">
                                        {{ customer.risk_score || customer.score || 0 }}%
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-muted-foreground text-xs">{{ customer.reason || customer.risk_reason || '-' }}</td>
                            </tr>
                            <tr v-if="!churnRisk.length && !loading">
                                <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                                    {{ $t('isp.billing.messages.no_data', 'No data available') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
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
import { Line, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
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
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Gem from 'lucide-vue-next/dist/esm/icons/gem.js';
import TriangleAlert from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const loading = ref(true);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const bi = ref<any>({});
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const projections = ref<any>({});
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const churnRisk = ref<any[]>([]);

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const getRiskVariant = (score: number) => {
    if (score >= 70) return 'destructive';
    if (score >= 40) return 'secondary';
    return 'outline';
};

const projectionChartData = computed<ChartData<'line'>>(() => {
    if (!projections.value?.projections) return { labels: [], datasets: [] };
    
    const data = projections.value.projections;
    return {
        labels: data.map((d: { month: string }) => d.month),
        datasets: [
            {
                label: t('isp.admin.analytics.projected_mrr', 'Projected MRR'),
                data: data.map((d: { mrr: number }) => d.mrr),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderDash: [5, 5]
            }
        ]
    };
});

const growthChartData = computed<ChartData<'bar'>>(() => {
    if (!projections.value?.customer_growth) return { labels: [], datasets: [] };
    
    const data = projections.value.customer_growth;
    return {
        labels: data.map((d: { month: string }) => d.month),
        datasets: [
            {
                label: t('isp.admin.analytics.new_subs', 'New Subscribers'),
                data: data.map((d: { new_subscribers: number }) => d.new_subscribers || 0),
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderRadius: 6,
            },
            {
                label: t('isp.admin.analytics.churned', 'Churned'),
                data: data.map((d: { churned: number }) => -(d.churned || 0)),
                backgroundColor: 'rgba(239, 68, 68, 0.6)',
                borderRadius: 6,
            }
        ]
    };
});

const projectionChartOptions: ChartOptions<'line'> = {
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

const growthChartOptions: ChartOptions<'bar'> = {
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

const fetchAll = async () => {
    loading.value = true;
    try {
        const [biRes, projRes, churnRes] = await Promise.all([
            api.get('/admin/janet/isp/billing/analytics/bi'),
            api.get('/admin/janet/isp/billing/analytics/projections'),
            api.get('/admin/janet/isp/billing/analytics/churn-risk'),
        ]);
        bi.value = biRes.data.data || biRes.data;
        projections.value = projRes.data.data || projRes.data;
        churnRisk.value = churnRes.data.data || churnRes.data || [];
    } catch (error) {
        console.error('Failed to fetch BI data', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchAll);
</script>
