<template>
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('isp.admin.vouchers.sales_report', 'Sales Report') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.admin.vouchers.sales_report_subtitle', 'Voucher sales analytics and revenue trends') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="selectedYear" class="border border-border rounded-lg px-3 py-2 bg-background text-foreground">
                    <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                </select>
                <select v-model="selectedMonth" class="border border-border rounded-lg px-3 py-2 bg-background text-foreground">
                    <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                </select>
                <Button @click="fetchReport" :disabled="loading">
                    <RefreshCw v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    <Search v-else class="w-4 h-4 mr-2" />
                    {{ $t('common.actions.load', 'Load') }}
                </Button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-primary/10">
                            <CalendarDays class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase">{{ $t('isp.admin.vouchers.today', 'Today') }}</p>
                            <p class="text-xl font-bold">{{ summary.today.count }} vcr</p>
                            <p class="text-sm text-muted-foreground">Rp{{ formatCurrency(summary.today.revenue) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-green-500/10">
                            <Calendar class="w-5 h-5 text-green-500" />
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase">{{ $t('isp.admin.vouchers.this_month', 'This Month') }}</p>
                            <p class="text-xl font-bold">{{ summary.month.count }} vcr</p>
                            <p class="text-sm text-muted-foreground">Rp{{ formatCurrency(summary.month.revenue) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-orange-500/10">
                            <TrendingUp class="w-5 h-5 text-orange-500" />
                        </div>
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase">{{ $t('isp.admin.vouchers.selected_period', 'Selected Period') }}</p>
                            <p class="text-xl font-bold">{{ totalVouchers }} vcr</p>
                            <p class="text-sm text-muted-foreground">Rp{{ formatCurrency(totalRevenue) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Chart -->
        <Card>
            <CardContent class="p-4">
                <div v-if="loading" class="h-80 flex items-center justify-center">
                    <LoaderCircle class="w-8 h-8 animate-spin text-primary" />
                </div>
                <div v-else-if="reportData.length === 0" class="h-80 flex items-center justify-center text-muted-foreground">
                    {{ $t('isp.admin.vouchers.no_sales_data', 'No sales data for this period') }}
                </div>
                <div v-else class="h-80">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { Button, Card, CardContent } from '@/components/ui';
import Chart from 'chart.js/auto';
import CalendarDays from 'lucide-vue-next/dist/esm/icons/calendar-days.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();

interface SalesDay {
    date: string;
    count: number;
    revenue: number;
}

interface Summary {
    today: { count: number; revenue: number };
    month: { count: number; revenue: number };
}

const loading = ref(false);
const reportData = ref<SalesDay[]>([]);
const summary = ref<Summary>({ today: { count: 0, revenue: 0 }, month: { count: 0, revenue: 0 } });
const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const selectedMonth = ref(new Date().getMonth() + 1);

const availableYears = computed(() => {
    const years = [];
    for (let y = currentYear; y >= currentYear - 3; y--) {
        years.push(y);
    }
    return years;
});

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const totalVouchers = computed(() => reportData.value.reduce((sum, d) => sum + d.count, 0));
const totalRevenue = computed(() => reportData.value.reduce((sum, d) => sum + d.revenue, 0));

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const fetchReport = async () => {
    loading.value = true;
    try {
        const [reportRes, summaryRes] = await Promise.all([
            api.get('/admin/ja/isp/vouchers/sales-report', {
                params: { year: selectedYear.value, month: selectedMonth.value }
            }),
            api.get('/admin/ja/isp/vouchers/summary')
        ]);
        reportData.value = reportRes.data.data;
        summary.value = summaryRes.data.data;
        await nextTick();
        renderChart();
    } catch (error) {
        console.error('Failed to fetch sales report', error);
    } finally {
        loading.value = false;
    }
};

const renderChart = () => {
    if (!chartCanvas.value) return;
    if (chartInstance) chartInstance.destroy();

    const labels = reportData.value.map(d => d.date);
    const revenueData = reportData.value.map(d => d.revenue);
    const countData = reportData.value.map(d => d.count);

    chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: t('isp.admin.vouchers.revenue', 'Revenue'),
                    data: revenueData,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: t('isp.admin.vouchers.vouchers_sold', 'Vouchers Sold'),
                    data: countData,
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1,
                    yAxisID: 'y1',
                    type: 'line',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Revenue (Rp)' } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Count' } },
            },
        },
    });
};

watch([selectedYear, selectedMonth], () => {
    // Auto-refresh on date change (optional)
});

onMounted(() => {
    fetchReport();
});
</script>
