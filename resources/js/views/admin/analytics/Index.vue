<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.analytics.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.analytics.subtitle') }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <input
                    v-model="dateFrom"
                    type="date"
                    class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                >
                <span class="text-muted-foreground">{{ $t('features.analytics.to') }}</span>
                <input
                    v-model="dateTo"
                    type="date"
                    class="px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                >
                <Button @click="fetchAnalytics">
                    {{ $t('features.analytics.apply') }}
                </Button>
                
                <!-- Export Dropdown -->
                <div class="relative" ref="exportDropdownRef">
                    <Button 
                        variant="outline" 
                        @click="toggleExportMenu"
                        :disabled="exporting"
                    >
                        <svg v-if="!exporting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ exporting ? $t('features.analytics.export.exporting') : $t('features.analytics.export.button') }}
                    </Button>
                    <div 
                        v-if="showExportMenu" 
                        class="absolute right-0 mt-2 w-48 bg-popover border border-border rounded-md shadow-lg z-50"
                    >
                        <button 
                            @click="exportData('visits')" 
                            class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent hover:text-accent-foreground first:rounded-t-md"
                        >
                            {{ $t('features.analytics.export.visits') }}
                        </button>
                        <button 
                            @click="exportData('events')" 
                            class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent hover:text-accent-foreground"
                        >
                            {{ $t('features.analytics.export.events') }}
                        </button>
                        <button 
                            @click="exportData('sessions')" 
                            class="w-full px-4 py-2 text-left text-sm text-popover-foreground hover:bg-accent hover:text-accent-foreground last:rounded-b-md"
                        >
                            {{ $t('features.analytics.export.sessions') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ $t('features.analytics.loading') }}</p>
        </div>

        <div v-else>
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <div class="bg-card overflow-hidden border border-border rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.analytics.overview.totalVisits') }}</dt>
                                    <dd class="text-lg font-semibold text-foreground">{{ overview.total_visits || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-card overflow-hidden border border-border rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.analytics.overview.uniqueVisitors') }}</dt>
                                    <dd class="text-lg font-semibold text-foreground">{{ overview.unique_visitors || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-card overflow-hidden border border-border rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.analytics.overview.totalSessions') }}</dt>
                                    <dd class="text-lg font-semibold text-foreground">{{ overview.total_sessions || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-card overflow-hidden border border-border rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.analytics.overview.bounceRate') }}</dt>
                                    <dd class="text-lg font-semibold text-foreground">{{ overview.bounce_rate || 0 }}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Visits Chart -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.visitsOverTime') }}</h2>
                    <div class="h-64">
                         <LineChart
                            v-if="visits.length > 0"
                            :data="visits"
                            :label="$t('features.analytics.charts.visits')"
                        />
                        <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                            {{ $t('features.analytics.noData') }}
                        </div>
                    </div>
                </div>

                <!-- Top Pages -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.topPages') }}</h2>
                    <div class="space-y-3">
                        <div
                            v-for="(page, index) in topPages"
                            :key="index"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-foreground truncate">{{ page.url }}</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <span class="text-sm text-foreground font-medium">{{ page.visits }}</span>
                                <span class="ml-2 text-xs text-muted-foreground">{{ $t('features.analytics.labels.visits') }}</span>
                            </div>
                        </div>
                        <p v-if="topPages.length === 0" class="text-sm text-muted-foreground text-center py-4">
                            {{ $t('features.analytics.noData') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Devices -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.devices') }}</h2>
                    <div class="h-64">
                        <DoughnutChart
                            v-if="devices.length > 0"
                            :data="devices"
                            label-key="device_type"
                            value-key="count"
                        />
                         <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                            {{ $t('features.analytics.noData') }}
                        </div>
                    </div>
                </div>

                <!-- Browsers -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.browsers') }}</h2>
                    <div class="h-64">
                         <DoughnutChart
                            v-if="browsers.length > 0"
                            :data="browsers"
                            label-key="browser"
                            value-key="count"
                        />
                         <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                            {{ $t('features.analytics.noData') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Content -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.topContent') }}</h2>
                    <div class="space-y-3">
                        <div
                            v-for="content in topContent"
                            :key="content.id"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-foreground truncate">{{ content.title }}</p>
                                <p class="text-xs text-muted-foreground">{{ content.author?.name }}</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <span class="text-sm text-foreground font-medium">{{ content.visits_count || 0 }}</span>
                                <span class="ml-2 text-xs text-muted-foreground">{{ $t('features.analytics.labels.visits') }}</span>
                            </div>
                        </div>
                        <p v-if="topContent.length === 0" class="text-sm text-muted-foreground text-center py-4">
                            {{ $t('features.analytics.noData') }}
                        </p>
                    </div>
                </div>

                <!-- Countries -->
                <div class="bg-card border border-border rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.topCountries') }}</h2>
                    <div class="h-64">
                         <BarChart
                            v-if="countries.length > 0"
                            :data="countries"
                            label-key="country"
                            value-key="count"
                            :horizontal="true"
                        />
                         <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                            {{ $t('features.analytics.noData') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referrers -->
            <div class="bg-card border border-border rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.topReferrers') }}</h2>
                <div class="space-y-3">
                    <div
                        v-for="referrer in referrers"
                        :key="referrer.referer"
                        class="flex items-center justify-between"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-foreground truncate">{{ referrer.referer }}</p>
                        </div>
                        <div class="ml-4 flex items-center">
                            <span class="text-sm text-foreground font-medium">{{ referrer.count }}</span>
                            <span class="ml-2 text-xs text-muted-foreground">{{ $t('features.analytics.labels.visits') }}</span>
                        </div>
                    </div>
                    <p v-if="referrers.length === 0" class="text-sm text-muted-foreground text-center py-4">
                        {{ $t('features.analytics.noData') }}
                    </p>
                </div>
            </div>

            <!-- Real-time Stats -->
            <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ $t('features.analytics.charts.realtime') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-indigo-500/10 rounded-lg">
                        <p class="text-2xl font-bold text-indigo-600">{{ realtime.active_sessions || 0 }}</p>
                        <p class="text-sm text-muted-foreground mt-1">{{ $t('features.analytics.realtime.activeSessions') }}</p>
                    </div>
                    <div class="text-center p-4 bg-green-500/10 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ realtime.visits_last_hour || 0 }}</p>
                        <p class="text-sm text-muted-foreground mt-1">{{ $t('features.analytics.realtime.visitsLastHour') }}</p>
                    </div>
                    <div class="text-center p-4 bg-blue-500/10 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ realtime.top_pages_now?.length || 0 }}</p>
                        <p class="text-sm text-muted-foreground mt-1">{{ $t('features.analytics.realtime.activePages') }}</p>
                    </div>
                </div>
            </div>
        </div>
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

const maxVisits = computed(() => {
    return Math.max(...visits.value.map(v => v.visits), 1);
});

const maxDeviceCount = computed(() => {
    return Math.max(...devices.value.map(d => d.count), 1);
});

const maxBrowserCount = computed(() => {
    return Math.max(...browsers.value.map(b => b.count), 1);
});

const maxCountryCount = computed(() => {
    return Math.max(...countries.value.map(c => c.count), 1);
});

const fetchAnalytics = async () => {
    loading.value = true;
    try {
        const params = {
            date_from: dateFrom.value,
            date_to: dateTo.value,
        };

        // Fetch all analytics data in parallel
        const [
            overviewRes,
            visitsRes,
            topPagesRes,
            topContentRes,
            devicesRes,
            browsersRes,
            countriesRes,
            referrersRes,
            realtimeRes,
        ] = await Promise.all([
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
        const visitsData = parseResponse(visitsRes);
        visits.value = ensureArray(visitsData.data);
        const topPagesData = parseResponse(topPagesRes);
        topPages.value = ensureArray(topPagesData.data);
        const topContentData = parseResponse(topContentRes);
        topContent.value = ensureArray(topContentData.data);
        const devicesData = parseResponse(devicesRes);
        devices.value = ensureArray(devicesData.data);
        const browsersData = parseResponse(browsersRes);
        browsers.value = ensureArray(browsersData.data);
        const countriesData = parseResponse(countriesRes);
        countries.value = ensureArray(countriesData.data);
        const referrersData = parseResponse(referrersRes);
        referrers.value = ensureArray(referrersData.data);
        realtime.value = parseSingleResponse(realtimeRes) || {};
    } catch (error) {
        console.error('Failed to fetch analytics:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAnalytics();
    // Refresh real-time data every 30 seconds
    setInterval(() => {
        api.get('/admin/cms/analytics/realtime').then(res => {
            realtime.value = parseSingleResponse(res) || {};
        }).catch(err => {
            console.error('Failed to fetch real-time data:', err);
        });
    }, 30000);
    
    // Click outside handler for export dropdown
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Export functionality
const toggleExportMenu = () => {
    showExportMenu.value = !showExportMenu.value;
};

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
            params: {
                date_from: dateFrom.value,
                date_to: dateTo.value,
                type: type
            },
            responseType: 'blob'
        });
        
        // Create download link
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
        console.error('Failed to export analytics:', error);
    } finally {
        exporting.value = false;
    }
};
</script>

