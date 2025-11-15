<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
                <p class="mt-1 text-sm text-gray-500">Track and analyze your website performance</p>
            </div>
            <div class="flex items-center space-x-3">
                <input
                    v-model="dateFrom"
                    type="date"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
                <span class="text-gray-500">to</span>
                <input
                    v-model="dateTo"
                    type="date"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                />
                <button
                    @click="fetchAnalytics"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Apply
                </button>
            </div>
        </div>

        <!-- Overview Stats -->
        <div v-if="loading" class="text-center py-12">
            <p class="text-gray-500">Loading analytics...</p>
        </div>

        <div v-else>
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Visits</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ overview.total_visits || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Unique Visitors</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ overview.unique_visitors || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Sessions</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ overview.total_sessions || 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Bounce Rate</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ overview.bounce_rate || 0 }}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Visits Chart -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Visits Over Time</h2>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-2">Simple bar chart (install chart library for better visualization)</p>
                            <div class="space-y-2">
                                <div
                                    v-for="(visit, index) in visits.slice(0, 10)"
                                    :key="index"
                                    class="flex items-center"
                                >
                                    <span class="text-xs text-gray-600 w-24">{{ visit.period }}</span>
                                    <div class="flex-1 bg-gray-200 rounded h-6 relative">
                                        <div
                                            class="bg-indigo-600 h-6 rounded"
                                            :style="{ width: (visit.visits / maxVisits * 100) + '%' }"
                                        ></div>
                                        <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-medium">
                                            {{ visit.visits }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Pages -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Pages</h2>
                    <div class="space-y-3">
                        <div
                            v-for="(page, index) in topPages"
                            :key="index"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ page.url }}</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <span class="text-sm text-gray-900 font-medium">{{ page.visits }}</span>
                                <span class="ml-2 text-xs text-gray-500">visits</span>
                            </div>
                        </div>
                        <p v-if="topPages.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No data available
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Devices -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Devices</h2>
                    <div class="space-y-3">
                        <div
                            v-for="device in devices"
                            :key="device.device_type"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-gray-900 capitalize">{{ device.device_type || 'Unknown' }}</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div
                                        class="bg-indigo-600 h-2 rounded-full"
                                        :style="{ width: (device.count / maxDeviceCount * 100) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ device.count }}</span>
                            </div>
                        </div>
                        <p v-if="devices.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No data available
                        </p>
                    </div>
                </div>

                <!-- Browsers -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Browsers</h2>
                    <div class="space-y-3">
                        <div
                            v-for="browser in browsers"
                            :key="browser.browser"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-gray-900">{{ browser.browser || 'Unknown' }}</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div
                                        class="bg-green-600 h-2 rounded-full"
                                        :style="{ width: (browser.count / maxBrowserCount * 100) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ browser.count }}</span>
                            </div>
                        </div>
                        <p v-if="browsers.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No data available
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Content -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Content</h2>
                    <div class="space-y-3">
                        <div
                            v-for="content in topContent"
                            :key="content.id"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ content.title }}</p>
                                <p class="text-xs text-gray-500">{{ content.author?.name }}</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <span class="text-sm text-gray-900 font-medium">{{ content.visits_count || 0 }}</span>
                                <span class="ml-2 text-xs text-gray-500">visits</span>
                            </div>
                        </div>
                        <p v-if="topContent.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No data available
                        </p>
                    </div>
                </div>

                <!-- Countries -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Countries</h2>
                    <div class="space-y-3">
                        <div
                            v-for="country in countries"
                            :key="country.country"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-gray-900">{{ country.country || 'Unknown' }}</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div
                                        class="bg-blue-600 h-2 rounded-full"
                                        :style="{ width: (country.count / maxCountryCount * 100) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ country.count }}</span>
                            </div>
                        </div>
                        <p v-if="countries.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No data available
                        </p>
                    </div>
                </div>
            </div>

            <!-- Referrers -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Referrers</h2>
                <div class="space-y-3">
                    <div
                        v-for="referrer in referrers"
                        :key="referrer.referer"
                        class="flex items-center justify-between"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ referrer.referer }}</p>
                        </div>
                        <div class="ml-4 flex items-center">
                            <span class="text-sm text-gray-900 font-medium">{{ referrer.count }}</span>
                            <span class="ml-2 text-xs text-gray-500">visits</span>
                        </div>
                    </div>
                    <p v-if="referrers.length === 0" class="text-sm text-gray-500 text-center py-4">
                        No data available
                    </p>
                </div>
            </div>

            <!-- Real-time Stats -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Real-time Activity</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-indigo-50 rounded-lg">
                        <p class="text-2xl font-bold text-indigo-600">{{ realtime.active_sessions || 0 }}</p>
                        <p class="text-sm text-gray-600 mt-1">Active Sessions</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ realtime.visits_last_hour || 0 }}</p>
                        <p class="text-sm text-gray-600 mt-1">Visits (Last Hour)</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">{{ realtime.top_pages_now?.length || 0 }}</p>
                        <p class="text-sm text-gray-600 mt-1">Active Pages</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';

const loading = ref(false);
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

        overview.value = overviewRes.data;
        visits.value = visitsRes.data.data || visitsRes.data || [];
        topPages.value = topPagesRes.data.data || topPagesRes.data || [];
        topContent.value = topContentRes.data.data || topContentRes.data || [];
        devices.value = devicesRes.data.data || devicesRes.data || [];
        browsers.value = browsersRes.data.data || browsersRes.data || [];
        countries.value = countriesRes.data.data || countriesRes.data || [];
        referrers.value = referrersRes.data.data || referrersRes.data || [];
        realtime.value = realtimeRes.data;
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
            realtime.value = res.data;
        }).catch(err => {
            console.error('Failed to fetch real-time data:', err);
        });
    }, 30000);
});
</script>

