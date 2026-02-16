<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.dashboard.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" @click="refreshDashboard" :disabled="loadingVisits" class="bg-muted/40 border border-border/40 hover:bg-muted/60">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loadingVisits }" />
                    {{ $t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Contents Card -->
            <Card class="border-border/40 bg-card">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalContents') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-primary font-medium">
                                <FileText class="w-3 h-3" />
                                <span>{{ stats.contents?.published || 0 }} {{ $t('features.dashboard.stats.published') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                            <Library class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Media Card -->
            <Card class="border-border/40 bg-card">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.mediaFiles') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.media?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-success font-medium">
                                <Image class="w-3 h-3" />
                                <span>{{ $t('common.status.online') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-success/10 text-success">
                            <FolderOpen class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Card -->
            <Card class="border-border/40 bg-card" v-if="authStore.hasPermission('manage users')">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalUsers') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.users?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-primary font-medium">
                                <Users class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.activeUsers') || 'Active now' }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                            <UserCheck class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Card -->
            <Card class="border-border/40 bg-card" v-if="authStore.hasPermission('approve content')">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.pendingContent') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.pending || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-warning font-medium">
                                <AlertCircle class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.requiresReview') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-warning/10 text-warning">
                            <Clock3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Row 2: Traffic Chart (Full Width) -->
        <div class="w-full" v-if="authStore.hasPermission('view analytics')">
            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            {{ $t('features.dashboard.traffic.title') }}
                        </CardTitle>
                        <CardDescription>{{ $t('features.dashboard.traffic.overview') }}</CardDescription>
                    </div>
                    <!-- Time Range Filter -->
                    <div class="w-[180px]">
                        <Select v-model="timeRange">
                            <SelectTrigger class="w-full">
                                <SelectValue :placeholder="$t('features.dashboard.traffic.filters.last7Days')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="7">{{ $t('features.dashboard.traffic.filters.last7Days') }}</SelectItem>
                                <SelectItem value="30">{{ $t('features.dashboard.traffic.filters.last30Days') }}</SelectItem>
                                <SelectItem value="90">{{ $t('features.dashboard.traffic.filters.last90Days') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="h-[250px] mt-4">
                        <div v-if="loadingVisits" class="h-full flex items-center justify-center">
                            <Loader2 class="h-8 w-8 text-primary animate-spin" />
                        </div>
                        <LineChart
                            v-else-if="visitsDesktop.length > 0"
                            :data="visitsDesktop"
                            label="Desktop"
                            :compare-data="visitsMobile"
                            compare-label="Mobile"
                        />
                         <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-2">
                            <AreaChart class="w-10 h-10 opacity-20" />
                            <p>{{ $t('features.dashboard.traffic.noData') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Row 3: Widgets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- Recent Activity -->
            <div class="col-span-1" v-if="authStore.hasPermission('view users')">
                <RecentActivityWidget ref="recentActivityWidget" />
            </div>

            <!-- Email Status -->
            <div class="col-span-1" v-if="authStore.hasPermission('manage settings')">
                <EmailStatusWidget />
            </div>

            <!-- System Health -->
            <div class="col-span-1" v-if="authStore.hasPermission('manage system')">
                <SystemHealthWidget class="h-full" />
            </div>

            <!-- Quick Actions -->
            <div class="col-span-1">
                 <QuickActions :show-recent="false" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import { parseSingleResponse, ensureArray } from '@/utils/responseParser';
import type { SystemStats, TrafficItem, TrafficDataPoint, DashboardData } from '@/types/dashboard';

import QuickActions from '@/components/admin/QuickActions.vue';
import SystemHealthWidget from '@/components/admin/SystemHealthWidget.vue';
import RecentActivityWidget from '@/components/admin/RecentActivityWidget.vue';
import EmailStatusWidget from '@/components/admin/EmailStatusWidget.vue';
import LineChart from '@/components/charts/LineChart.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import AreaChart from 'lucide-vue-next/dist/esm/icons/chart-area.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Library from 'lucide-vue-next/dist/esm/icons/library.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import UserCheck from 'lucide-vue-next/dist/esm/icons/user-check.js';
import Clock3 from 'lucide-vue-next/dist/esm/icons/clock-3.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const authStore = useAuthStore();

const stats = ref<SystemStats>({
    contents: { total: 0, published: 0, pending: 0 },
    media: { total: 0 },
    users: { total: 0 },
});

const visitsDesktop = ref<TrafficDataPoint[]>([]); 
const visitsMobile = ref<TrafficDataPoint[]>([]);
const loadingVisits = ref(false);
const timeRange = ref('7'); 
const recentActivityWidget = ref<InstanceType<typeof RecentActivityWidget> | null>(null);

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID').format(val || 0);
};


const refreshDashboard = async () => {
    if (loadingVisits.value) return;
    loadingVisits.value = true;
    try {
        await Promise.allSettled([
            fetchDashboardData(true),
            recentActivityWidget.value?.fetchActivities() ?? Promise.resolve()
        ]);
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to refresh dashboard:', error);
            }
        } else {
            logger.error('Failed to refresh dashboard:', error);
        }
    } finally {
        loadingVisits.value = false;
    }
};

const fetchDashboardData = async (skipLoading = false) => {
    if (!skipLoading) loadingVisits.value = true;
    try {
        let endpoint = '/dashboard/viewer';
        if (authStore.hasPermission('manage users') || authStore.hasPermission('manage settings')) {
            endpoint = '/dashboard/admin';
        } else if (authStore.hasPermission('create content') || authStore.hasPermission('edit content')) {
            endpoint = '/dashboard/creator';
        }

        const response = await api.get(endpoint, {
            params: { days: timeRange.value }
        });
        const rawData = parseSingleResponse<Record<string, unknown>>(response);

        // Handle potential double wrapping from Laravel Resources + BaseController
        const data = (rawData?.data as DashboardData) || (rawData as DashboardData);

        if (data) {
            // Update stats
            if (data.stats) {
                stats.value = {
                    contents: {
                        total: data.stats.contents?.total ?? data.stats.myContents?.total ?? 0,
                        published: data.stats.contents?.published ?? data.stats.myContents?.published ?? 0,
                        pending: data.stats.contents?.pending ?? data.stats.myContents?.pending ?? 0,
                    },
                    media: {
                        total: data.stats.media?.total ?? data.stats.myMedia?.total ?? 0,
                    },
                    users: {
                        total: data.stats.users?.total ?? 0,
                    },
                };
            }

            // Update traffic/charts
            if (data.charts?.contentTraffic || data.charts?.userActivity) {
                const trafficRaw = (data.charts.contentTraffic || data.charts.userActivity) as (TrafficItem | { date: string; count: number })[];
                const traffic = ensureArray<TrafficItem | { date: string; count: number }>(trafficRaw);
                
                visitsDesktop.value = traffic.map(item => {
                    const period = 'period' in item ? item.period : item.date;
                    const visits = 'visits' in item ? item.visits : item.count;
                    return {
                        period: period || '',
                        visits: Math.ceil((Number(visits) || 0) * 0.4)
                    };
                });

                visitsMobile.value = traffic.map(item => {
                    const period = 'period' in item ? item.period : item.date;
                    const visits = 'visits' in item ? item.visits : item.count;
                    return {
                        period: period || '',
                        visits: Math.ceil((Number(visits) || 0) * 0.6)
                    };
                });
            }
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to fetch dashboard data:', error);
            }
        } else {
            logger.error('Failed to fetch dashboard data:', error);
        }
    } finally {
        if (!skipLoading) loadingVisits.value = false;
    }
};

watch(timeRange, () => {
    fetchDashboardData();
});

onMounted(() => {
    fetchDashboardData();
});
</script>
