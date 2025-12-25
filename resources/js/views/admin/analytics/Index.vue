<template>
    <div class="space-y-4">
        <!-- Header with Stats -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.analytics.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ $t('features.analytics.subtitle') }}</p>
            </div>
            
            <!-- Date Range & Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 bg-card border border-border rounded-md px-2">
                    <input v-model="dateFrom" type="date" class="px-2 py-1.5 bg-transparent text-foreground text-sm focus:outline-none" />
                    <span class="text-muted-foreground text-sm">{{ $t('features.analytics.to') }}</span>
                    <input v-model="dateTo" type="date" class="px-2 py-1.5 bg-transparent text-foreground text-sm focus:outline-none" />
                </div>
                <Button size="sm" @click="fetchAnalytics">{{ $t('features.analytics.apply') }}</Button>
                
                <!-- Export Dropdown -->
                <div class="relative" ref="exportDropdownRef">
                    <Button variant="outline" size="sm" @click="toggleExportMenu" :disabled="exporting">
                        <svg v-if="!exporting" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ exporting ? $t('features.analytics.export.exporting') : $t('features.analytics.export.button') }}
                    </Button>
                    <div v-if="showExportMenu" class="absolute right-0 mt-1 w-40 bg-popover border border-border rounded-md shadow-lg z-50">
                        <button @click="exportData('visits')" class="w-full px-3 py-2 text-left text-sm text-popover-foreground hover:bg-accent first:rounded-t-md">
                            {{ $t('features.analytics.export.visits') }}
                        </button>
                        <button @click="exportData('events')" class="w-full px-3 py-2 text-left text-sm text-popover-foreground hover:bg-accent">
                            {{ $t('features.analytics.export.events') }}
                        </button>
                        <button @click="exportData('sessions')" class="w-full px-3 py-2 text-left text-sm text-popover-foreground hover:bg-accent last:rounded-b-md">
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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-500/10 rounded-lg">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">{{ $t('features.analytics.overview.totalVisits') }}</p>
                            <p class="text-xl font-bold text-foreground">{{ formatNumber(overview.total_visits || 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-500/10 rounded-lg">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">{{ $t('features.analytics.overview.uniqueVisitors') }}</p>
                            <p class="text-xl font-bold text-foreground">{{ formatNumber(overview.unique_visitors || 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-500/10 rounded-lg">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">{{ $t('features.analytics.overview.totalSessions') }}</p>
                            <p class="text-xl font-bold text-foreground">{{ formatNumber(overview.total_sessions || 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-500/10 rounded-lg">
                            <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">{{ $t('features.analytics.overview.bounceRate') }}</p>
                            <p class="text-xl font-bold text-foreground">{{ overview.bounce_rate || 0 }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Activity - Compact -->
            <div class="bg-gradient-to-r from-indigo-500/5 via-green-500/5 to-blue-500/5 border border-border rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-foreground flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        {{ $t('features.analytics.charts.realtime') }}
                    </h3>
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <span class="text-lg font-bold text-indigo-500">{{ realtime.active_sessions || 0 }}</span>
                            <span class="text-xs text-muted-foreground ml-1">{{ $t('features.analytics.realtime.activeSessions') }}</span>
                        </div>
                        <div class="text-center">
                            <span class="text-lg font-bold text-green-500">{{ formatNumber(realtime.visits_last_hour || 0) }}</span>
                            <span class="text-xs text-muted-foreground ml-1">{{ $t('features.analytics.realtime.visitsLastHour') }}</span>
                        </div>
                        <div class="text-center">
                            <span class="text-lg font-bold text-blue-500">{{ realtime.top_pages_now?.length || 0 }}</span>
                            <span class="text-xs text-muted-foreground ml-1">{{ $t('features.analytics.realtime.activePages') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traffic Section -->
            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-medium text-foreground mb-3">📈 {{ $t('features.analytics.sections.traffic') }}</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Visits Chart -->
                    <div class="lg:col-span-2">
                        <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.visitsOverTime') }}</h4>
                        <div class="h-48">
                            <LineChart v-if="visits.length > 0" :data="visits" :label="$t('features.analytics.charts.visits')" />
                            <div v-else class="h-full flex items-center justify-center text-muted-foreground text-sm">{{ $t('features.analytics.noData') }}</div>
                        </div>
                    </div>
                    <!-- Top Pages -->
                    <div>
                        <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.topPages') }}</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <div v-for="(page, i) in topPages.slice(0, 5)" :key="i" class="flex items-center justify-between text-sm">
                                <span class="truncate flex-1 text-foreground">{{ formatUrl(page.url) }}</span>
                                <span class="text-muted-foreground ml-2">{{ page.visits }}</span>
                            </div>
                            <p v-if="topPages.length === 0" class="text-sm text-muted-foreground text-center py-2">{{ $t('features.analytics.noData') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technology & Geography Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Technology -->
                <div class="bg-card border border-border rounded-lg p-4">
                    <h3 class="text-sm font-medium text-foreground mb-3">🖥️ {{ $t('features.analytics.sections.technology') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Devices -->
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.devices') }}</h4>
                            <div class="h-32">
                                <DoughnutChart v-if="devices.length > 0" :data="devices" label-key="device_type" value-key="count" />
                                <div v-else class="h-full flex items-center justify-center text-muted-foreground text-sm">{{ $t('features.analytics.noData') }}</div>
                            </div>
                        </div>
                        <!-- Browsers -->
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.browsers') }}</h4>
                            <div class="h-32">
                                <DoughnutChart v-if="browsers.length > 0" :data="browsers" label-key="browser" value-key="count" />
                                <div v-else class="h-full flex items-center justify-center text-muted-foreground text-sm">{{ $t('features.analytics.noData') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geography -->
                <div class="bg-card border border-border rounded-lg p-4">
                    <h3 class="text-sm font-medium text-foreground mb-3">🌍 {{ $t('features.analytics.sections.geography') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Countries -->
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.topCountries') }}</h4>
                            <div class="h-32">
                                <BarChart v-if="countries.length > 0" :data="countries.slice(0, 5)" label-key="country" value-key="count" :horizontal="true" />
                                <div v-else class="h-full flex items-center justify-center text-muted-foreground text-sm">{{ $t('features.analytics.noData') }}</div>
                            </div>
                        </div>
                        <!-- Referrers -->
                        <div>
                            <h4 class="text-xs font-medium text-muted-foreground mb-2">{{ $t('features.analytics.charts.topReferrers') }}</h4>
                            <div class="space-y-1.5 max-h-32 overflow-y-auto">
                                <div v-for="(ref, i) in referrers.slice(0, 5)" :key="i" class="flex items-center justify-between text-xs">
                                    <span class="truncate flex-1 text-foreground">{{ formatUrl(ref.referer) }}</span>
                                    <span class="text-muted-foreground ml-2">{{ ref.count }}</span>
                                </div>
                                <p v-if="referrers.length === 0" class="text-xs text-muted-foreground text-center py-2">{{ $t('features.analytics.noData') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="bg-card border border-border rounded-lg p-4">
                <h3 class="text-sm font-medium text-foreground mb-3">📄 {{ $t('features.analytics.sections.content') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                    <div v-for="content in topContent.slice(0, 10)" :key="content.id" class="bg-muted/30 rounded-lg p-3">
                        <p class="text-sm font-medium text-foreground truncate">{{ content.title }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ content.author?.name }}</p>
                        <p class="text-lg font-bold text-primary mt-1">{{ content.visits_count || 0 }} <span class="text-xs font-normal text-muted-foreground">{{ $t('features.analytics.labels.visits') }}</span></p>
                    </div>
                    <p v-if="topContent.length === 0" class="text-sm text-muted-foreground text-center py-4 col-span-full">{{ $t('features.analytics.noData') }}</p>
                </div>
            </div>
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
    } catch (error) {
        console.error('Failed to export:', error);
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
