<template>
    <div class="space-y-4">
        <!-- Header with Stats -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $t('features.analytics.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('features.analytics.subtitle') }}</p>
            </div>
            
            <!-- Date Range & Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 bg-card border border-border rounded-md px-1 py-0.5">
                    <Input 
                        v-model="dateFrom" 
                        type="date" 
                        class="h-9 border-0 bg-transparent focus-visible:ring-0 w-[140px]" 
                    />
                    <span class="text-muted-foreground text-xs uppercase font-medium">{{ $t('features.analytics.to') }}</span>
                    <Input 
                        v-model="dateTo" 
                        type="date" 
                        class="h-9 border-0 bg-transparent focus-visible:ring-0 w-[140px]" 
                    />
                </div>
                <Button @click="fetchAnalytics">{{ $t('features.analytics.apply') }}</Button>
                
                <!-- Export Dropdown -->
                <div class="relative" ref="exportDropdownRef">
                    <Button variant="outline" @click="toggleExportMenu" :disabled="exporting">
                        <svg v-if="!exporting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                        {{ exporting ? $t('features.analytics.export.exporting') : $t('features.analytics.export.button') }}
                    </Button>
                    <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-popover border border-border rounded-md shadow-lg z-50 py-1">
                        <button @click="exportData('visits')" class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors">
                            {{ $t('features.analytics.export.visits') }}
                        </button>
                        <button @click="exportData('events')" class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors">
                            {{ $t('features.analytics.export.events') }}
                        </button>
                        <button @click="exportData('sessions')" class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent transition-colors">
                            {{ $t('features.analytics.export.sessions') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ $t('features.analytics.loading') }}</p>
        </div>

        <template v-else>
            <!-- Overview Stats - Compact Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-indigo-500/10 rounded-xl">
                                <Eye class="h-5 w-5 text-indigo-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.analytics.overview.totalVisits') }}</p>
                                <p class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(overview.total_visits || 0) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-emerald-500/10 rounded-xl">
                                <Users class="h-5 w-5 text-emerald-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.analytics.overview.uniqueVisitors') }}</p>
                                <p class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(overview.unique_visitors || 0) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-blue-500/10 rounded-xl">
                                <BarChart3 class="h-5 w-5 text-blue-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.analytics.overview.totalSessions') }}</p>
                                <p class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(overview.total_sessions || 0) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-purple-500/10 rounded-xl">
                                <TrendingUp class="h-5 w-5 text-purple-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">{{ $t('features.analytics.overview.bounceRate') }}</p>
                                <p class="text-2xl font-bold tracking-tight text-foreground">{{ overview.bounce_rate || 0 }}%</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Real-time Activity - Compact -->
            <Card class="bg-gradient-to-r from-indigo-500/5 via-emerald-500/5 to-blue-500/5 border border-indigo-500/10 shadow-none">
                <CardContent class="p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <Badge variant="outline" class="bg-background/50 border-emerald-500/20 text-emerald-600 dark:text-emerald-400 gap-1.5 py-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                {{ $t('features.analytics.charts.realtime') }}
                            </Badge>
                        </div>
                        <div class="flex items-center gap-8">
                            <div class="text-center">
                                <span class="text-xl font-bold text-indigo-500">{{ realtime.active_sessions || 0 }}</span>
                                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('features.analytics.realtime.activeSessions') }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-xl font-bold text-emerald-500">{{ formatNumber(realtime.visits_last_hour || 0) }}</span>
                                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('features.analytics.realtime.visitsLastHour') }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-xl font-bold text-blue-500">{{ realtime.top_pages_now?.length || 0 }}</span>
                                <span class="text-xs font-medium text-muted-foreground ml-1.5 uppercase tracking-wider">{{ $t('features.analytics.realtime.activePages') }}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Traffic Section -->
            <Card>
                <CardHeader class="pb-3 border-b border-border/50">
                    <CardTitle class="text-base font-semibold flex items-center gap-2">
                        <TrendingUp class="w-4 h-4 text-primary" />
                        {{ $t('features.analytics.sections.traffic') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Visits Chart -->
                        <div class="lg:col-span-2">
                            <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.visitsOverTime') }}</h4>
                            <div class="h-[240px]">
                                <LineChart v-if="visits.length > 0" :data="visits" :label="$t('features.analytics.charts.visits')" />
                                <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground bg-muted/30 rounded-lg">
                                    <BarChart3 class="w-8 h-8 mb-2 opacity-20" />
                                    <p class="text-sm">{{ $t('features.analytics.noData') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Top Pages -->
                        <div>
                            <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.topPages') }}</h4>
                            <div class="space-y-3 max-h-[240px] overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(page, i) in topPages.slice(0, 10)" :key="i" class="flex items-center justify-between group">
                                    <span class="text-sm truncate flex-1 text-foreground/90 group-hover:text-foreground transition-colors">{{ formatUrl(page.url) }}</span>
                                    <Badge variant="secondary" class="ml-2 tabular-nums">{{ page.visits }}</Badge>
                                </div>
                                <div v-if="topPages.length === 0" class="h-full flex flex-col items-center justify-center py-12 text-muted-foreground bg-muted/30 rounded-lg">
                                    <p class="text-xs italic">{{ $t('features.analytics.noData') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Technology & Geography Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Technology -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Technology -->
                <Card>
                    <CardHeader class="pb-3 border-b border-border/50">
                        <CardTitle class="text-base font-semibold flex items-center gap-2">
                            <Monitor class="w-4 h-4 text-primary" />
                            {{ $t('features.analytics.sections.technology') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div class="grid grid-cols-2 gap-8">
                            <!-- Devices -->
                            <div>
                                <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.devices') }}</h4>
                                <div class="h-[160px]">
                                    <DoughnutChart v-if="devices.length > 0" :data="devices" label-key="device_type" value-key="count" />
                                    <div v-else class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs">{{ $t('features.analytics.noData') }}</div>
                                </div>
                            </div>
                            <!-- Browsers -->
                            <div>
                                <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.browsers') }}</h4>
                                <div class="h-[160px]">
                                    <DoughnutChart v-if="browsers.length > 0" :data="browsers" label-key="browser" value-key="count" />
                                    <div v-else class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs">{{ $t('features.analytics.noData') }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Geography -->
                <Card>
                    <CardHeader class="pb-3 border-b border-border/50">
                        <CardTitle class="text-base font-semibold flex items-center gap-2">
                            <Globe class="w-4 h-4 text-primary" />
                            {{ $t('features.analytics.sections.geography') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div class="grid grid-cols-2 gap-8">
                            <!-- Countries -->
                            <div>
                                <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.topCountries') }}</h4>
                                <div class="h-[160px]">
                                    <BarChart v-if="countries.length > 0" :data="countries.slice(0, 5)" label-key="country" value-key="count" :horizontal="true" />
                                    <div v-else class="h-full flex items-center justify-center text-muted-foreground bg-muted/30 rounded-lg text-xs">{{ $t('features.analytics.noData') }}</div>
                                </div>
                            </div>
                            <!-- Referrers -->
                            <div>
                                <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">{{ $t('features.analytics.charts.topReferrers') }}</h4>
                                <div class="space-y-2.5 max-h-[160px] overflow-y-auto pr-1 flex flex-col justify-center">
                                    <div v-for="(ref, i) in referrers.slice(0, 5)" :key="i" class="flex items-center justify-between group">
                                        <span class="text-xs truncate flex-1 text-foreground/80 group-hover:text-foreground transition-colors">{{ formatUrl(ref.referer) }}</span>
                                        <Badge variant="outline" class="ml-2 h-5 px-1.5 text-[10px] tabular-nums">{{ ref.count }}</Badge>
                                    </div>
                                    <div v-if="referrers.length === 0" class="flex flex-col items-center justify-center py-8 text-muted-foreground bg-muted/30 rounded-lg">
                                        <p class="text-[10px] italic">{{ $t('features.analytics.noData') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            </div>

            <!-- Content Section -->
            <Card>
                <CardHeader class="pb-3 border-b border-border/50">
                    <CardTitle class="text-base font-semibold flex items-center gap-2">
                        <FileText class="w-4 h-4 text-primary" />
                        {{ $t('features.analytics.sections.content') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                        <div v-for="content in topContent.slice(0, 10)" :key="content.id" class="flex flex-col justify-between p-4 rounded-xl border border-border bg-muted/20 hover:bg-muted/30 transition-colors group">
                            <div>
                                <p class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors truncate mb-1" :title="content.title">{{ content.title }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ content.author?.name || 'System' }}</p>
                            </div>
                            <div class="mt-4 flex items-end justify-between">
                                <span class="text-2xl font-bold tracking-tight text-foreground tabular-nums">{{ content.visits_count || 0 }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">{{ $t('features.analytics.labels.visits') }}</span>
                            </div>
                        </div>
                        <div v-if="topContent.length === 0" class="col-span-full flex flex-col items-center justify-center py-12 text-muted-foreground bg-muted/30 rounded-lg">
                            <FileText class="w-10 h-10 mb-2 opacity-10" />
                            <p class="text-sm italic">{{ $t('features.analytics.noData') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, parseSingleResponse, ensureArray } from '../../../utils/responseParser';
import LineChart from '../../../components/charts/LineChart.vue';
import DoughnutChart from '../../../components/charts/DoughnutChart.vue';
import BarChart from '../../../components/charts/BarChart.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Badge from '../../../components/ui/badge.vue';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import { 
    Eye, 
    Users, 
    BarChart3, 
    TrendingUp, 
    Loader2, 
    Monitor, 
    Globe, 
    FileText 
} from 'lucide-vue-next';

const { t } = useI18n();
const loading = ref(false);
const exporting = ref(false);
const showExportMenu = ref(false);
const exportDropdownRef = ref(null);
const dateFrom = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);

const overview = ref({});
const visits = ref([]);
const topPages = ref([]);
const topContent = ref([]);
const devices = ref([]);
const browsers = ref([]);
const countries = ref([]);
const referrers = ref([]);
const realtime = ref({});
let refreshInterval = null;

// Utility functions
const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const formatUrl = (url) => {
    if (!url) return '-';
    try {
        const parsed = new URL(url);
        return parsed.pathname === '/' ? parsed.hostname : parsed.pathname;
    } catch {
        return url.substring(0, 30);
    }
};

const fetchAnalytics = async () => {
    loading.value = true;
    try {
        const params = { date_from: dateFrom.value, date_to: dateTo.value };
        const [overviewRes, visitsRes, topPagesRes, topContentRes, devicesRes, browsersRes, countriesRes, referrersRes, realtimeRes] = await Promise.all([
            api.get('/admin/cms/analytics/overview', { params }),
            api.get('/admin/cms/analytics/visits', { params }),
            api.get('/admin/cms/analytics/top-pages', { params }),
            api.get('/admin/cms/analytics/top-content', { params }),
            api.get('/admin/cms/analytics/devices', { params }),
            api.get('/admin/cms/analytics/browsers', { params }),
            api.get('/admin/cms/analytics/countries', { params }),
            api.get('/admin/cms/analytics/referrers', { params }),
            api.get('/admin/cms/analytics/realtime'),
        ]);

        overview.value = parseSingleResponse(overviewRes) || {};
        visits.value = ensureArray(parseResponse(visitsRes).data);
        topPages.value = ensureArray(parseResponse(topPagesRes).data);
        topContent.value = ensureArray(parseResponse(topContentRes).data);
        devices.value = ensureArray(parseResponse(devicesRes).data);
        browsers.value = ensureArray(parseResponse(browsersRes).data);
        countries.value = ensureArray(parseResponse(countriesRes).data);
        referrers.value = ensureArray(parseResponse(referrersRes).data);
        realtime.value = parseSingleResponse(realtimeRes) || {};
    } catch (error) {
        console.error('Failed to fetch analytics:', error);
    } finally {
        loading.value = false;
    }
};

const toggleExportMenu = () => showExportMenu.value = !showExportMenu.value;

const handleClickOutside = (event) => {
    if (exportDropdownRef.value && !exportDropdownRef.value.contains(event.target)) {
        showExportMenu.value = false;
    }
};

const exportData = async (type) => {
    showExportMenu.value = false;
    exporting.value = true;
    try {
        const response = await api.get('/admin/cms/analytics/export', {
            params: { date_from: dateFrom.value, date_to: dateTo.value, type },
            responseType: 'blob'
        });
        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `analytics-${type}-${dateFrom.value}-to-${dateTo.value}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        toast.success.action(t('features.analytics.export.success') || 'Export started');
    } catch (error) {
        console.error('Failed to export:', error);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
    refreshInterval = setInterval(() => {
        api.get('/admin/cms/analytics/realtime').then(res => {
            realtime.value = parseSingleResponse(res) || {};
        }).catch(err => {
            console.error('Realtime fetch failed:', err);
            if (err.response?.status === 401 || err.response?.status === 419) {
                if (refreshInterval) clearInterval(refreshInterval);
            }
        });
    }, 30000);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    document.removeEventListener('click', handleClickOutside);
});
</script>
