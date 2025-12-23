<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.dashboard.title') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</p>
        </div>

        <!-- Row 1: Statistics Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <!-- Contents Card -->
            <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500/10 p-3 rounded-md">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.dashboard.stats.totalContents') }}</dt>
                                <dd class="text-2xl font-bold text-foreground mt-1">{{ stats.contents?.total || 0 }}</dd>
                                <dd class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">{{ stats.contents?.published || 0 }} {{ $t('features.dashboard.stats.published') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Card -->
            <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500/10 p-3 rounded-md">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.dashboard.stats.mediaFiles') }}</dt>
                                <dd class="text-2xl font-bold text-foreground mt-1">{{ stats.media?.total || 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Card -->
            <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500/10 p-3 rounded-md">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.dashboard.stats.totalUsers') }}</dt>
                                <dd class="text-2xl font-bold text-foreground mt-1">{{ stats.users?.total || 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Card -->
            <div class="bg-card overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500/10 p-3 rounded-md">
                             <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-muted-foreground truncate">{{ $t('features.dashboard.stats.pendingContent') }}</dt>
                                <dd class="text-2xl font-bold text-foreground mt-1">{{ stats.contents?.pending || 0 }}</dd>
                                <dd class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">{{ $t('features.dashboard.stats.requiresReview') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Traffic Chart & System Health -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Traffic Chart (Main) -->
            <div class="lg:col-span-2 bg-card shadow rounded-lg p-6">
                 <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-foreground">{{ $t('features.dashboard.traffic.title') }}</h3>
                    <span class="text-sm text-muted-foreground">{{ $t('features.dashboard.traffic.last7Days') }}</span>
                </div>
                <div class="h-80">
                    <div v-if="loadingVisits" class="h-full flex items-center justify-center">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <LineChart
                        v-else-if="visits.length > 0"
                        :data="visits"
                        :label="$t('features.dashboard.traffic.visits')"
                        :gradient="true"
                    />
                     <div v-else class="h-full flex items-center justify-center text-muted-foreground">
                        {{ $t('features.dashboard.traffic.noData') }}
                    </div>
                </div>
            </div>

            <!-- System Health (Side) -->
            <div class="lg:col-span-1">
                <SystemHealthWidget class="h-full" />
            </div>
        </div>

        <!-- Row 3: Recent Activity & Quick Actions -->
         <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Recent Activity (Main) -->
            <div class="lg:col-span-2 h-96">
                <RecentActivityWidget />
            </div>

            <!-- Quick Actions (Side) -->
            <div class="lg:col-span-1">
                 <QuickActions :show-recent="false" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import { parseSingleResponse, parseResponse, ensureArray } from '../../utils/responseParser';

const { t } = useI18n();
import QuickActions from '@/components/admin/QuickActions.vue';
import SystemHealthWidget from '@/components/admin/SystemHealthWidget.vue';
import RecentActivityWidget from '@/components/admin/RecentActivityWidget.vue';
import LineChart from '@/components/charts/LineChart.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';

const authStore = useAuthStore();
const stats = ref({
    contents: { total: 0, published: 0, pending: 0 },
    media: { total: 0 },
    users: { total: 0 },
});
const visits = ref([]);
const loadingVisits = ref(false);

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/cms/system/statistics');
        const data = parseSingleResponse(response);
        
        if (data) {
            stats.value = {
                contents: {
                    total: data.contents?.total ?? 0,
                    published: data.contents?.published ?? 0,
                    pending: data.contents?.pending ?? 0,
                },
                media: {
                    total: data.media?.total ?? 0,
                },
                users: {
                    total: data.users?.total ?? 0,
                },
            };
        }
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
};

const fetchTraffic = async () => {
    loadingVisits.value = true;
    try {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - 6); // Last 7 days

        const params = {
            date_from: startDate.toISOString().split('T')[0],
            date_to: endDate.toISOString().split('T')[0],
        };

        const response = await api.get('/admin/cms/analytics/visits', { params });
        const data = parseResponse(response);
        visits.value = ensureArray(data.data);
    } catch (error) {
        console.error('Failed to fetch traffic:', error);
    } finally {
        loadingVisits.value = false;
    }
};

onMounted(() => {
    fetchStats();
    fetchTraffic();
});
</script>
