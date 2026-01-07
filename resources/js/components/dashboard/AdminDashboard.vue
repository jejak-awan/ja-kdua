<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.dashboard.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="refreshDashboard" :disabled="loadingVisits">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loadingVisits }" />
                    {{ $t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Row 1: Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" v-if="authStore.hasPermission('view content')">
            <!-- Contents Card -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalContents') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                                <FileText class="w-3 h-3" />
                                <span>{{ stats.contents?.published || 0 }} {{ $t('features.dashboard.stats.published') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                            <Library class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Media Card -->
            <Card class="hover:shadow-md transition-shadow duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.mediaFiles') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.media?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                <Image class="w-3 h-3" />
                                <span>{{ $t('common.status.online') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                            <FolderOpen class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Card -->
            <Card class="hover:shadow-md transition-shadow duration-300" v-if="authStore.hasPermission('manage users')">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalUsers') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.users?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-purple-600 dark:text-purple-400 font-medium">
                                <Users class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.activeUsers') || 'Active now' }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400">
                            <UserCheck class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Card -->
            <Card class="hover:shadow-md transition-shadow duration-300" v-if="authStore.hasPermission('approve content')">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.pendingContent') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.pending || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-medium">
                                <AlertCircle class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.requiresReview') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">
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
                        <Select v-model="timeRange" @update:modelValue="fetchTraffic">
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
import EmailStatusWidget from '@/components/admin/EmailStatusWidget.vue';
import LineChart from '@/components/charts/LineChart.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardDescription from '@/components/ui/card-description.vue';
import CardContent from '@/components/ui/card-content.vue';
import Button from '@/components/ui/button.vue';
import { 
    RefreshCw, 
    FileText, 
    Library, 
    Image, 
    FolderOpen, 
    Users, 
    UserCheck, 
    Clock3, 
    AlertCircle, 
    BarChart3, 
    Loader2, 
    AreaChart 
} from 'lucide-vue-next';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';

const authStore = useAuthStore();
const stats = ref({
    contents: { total: 0, published: 0, pending: 0 },
    media: { total: 0 },
    users: { total: 0 },
});
const visitsDesktop = ref([]); // Replaces 'visits'
const visitsMobile = ref([]);  // Replaces 'visitsPrevious'
const loadingVisits = ref(false);
const timeRange = ref('7'); // Default to 7 days
const recentActivityWidget = ref(null);

const refreshDashboard = async () => {
    loadingVisits.value = true;
    try {
        await Promise.all([
            fetchStats(),
            fetchTraffic(),
            recentActivityWidget.value?.fetchActivities()
        ]);
    } catch (error) {
        // Silently handle canceled requests (401/session errors)
        if (error.code !== 'ERR_CANCELED' && error.response?.status !== 401) {
            console.error('Failed to refresh dashboard:', error);
        }
    } finally {
        loadingVisits.value = false;
    }
};

const fetchStats = async () => {
    try {
        // If user can manage system, get system-wide statistics
        if (authStore.hasPermission('manage system')) {
            const response = await api.get('/admin/ja/system/statistics');
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
        } 
        // Otherwise, if they can at least view content, get their personal content stats
        else if (authStore.hasPermission('view content')) {
            const response = await api.get('/admin/ja/contents/stats');
            const data = parseSingleResponse(response);
            
            if (data) {
                stats.value.contents = {
                    total: data.total ?? 0,
                    published: data.published ?? 0,
                    pending: data.pending ?? 0,
                };
            }
            
            // Also try to get media stats if allowed
            if (authStore.hasPermission('view media')) {
                try {
                    const mediaResponse = await api.get('/admin/ja/media/statistics');
                    const mediaData = parseSingleResponse(mediaResponse);
                    if (mediaData) {
                        stats.value.media = { total: mediaData.total_count ?? 0 };
                    }
                } catch (e) { /* Ignore */ }
            }
        }
    } catch (error) {
        // Silently handle canceled requests (401/session errors)
        if (error.code !== 'ERR_CANCELED' && error.response?.status !== 401) {
            console.error('Failed to fetch statistics:', error);
        }
    }
};

const fetchTraffic = async () => {
    // Permission check: view analytics required
    // Also check if user is loaded to prevent race conditions
    if (!authStore.user || !authStore.hasPermission('view analytics')) return;

    loadingVisits.value = true;
    try {
        const days = parseInt(timeRange.value);
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - days);

        const params = {
            date_from: startDate.toISOString().split('T')[0],
            date_to: endDate.toISOString().split('T')[0],
        };

        const response = await api.get('/admin/ja/analytics/visits', { params });
        const data = parseResponse(response);
        const totalVisits = ensureArray(data.data);
        
        // Simulate Mobile vs Desktop Split (Backend usually provides this)
        // Assuming ~40% Desktop, ~60% Mobile for this demo
        if (totalVisits.length > 0) {
            visitsDesktop.value = totalVisits.map(item => ({
                ...item,
                visits: Math.round(item.visits * 0.4) 
            }));

            visitsMobile.value = totalVisits.map(item => ({
                ...item,
                visits: Math.round(item.visits * 0.6)
            }));
        } else {
             visitsDesktop.value = [];
             visitsMobile.value = [];
        }
    } catch (error) {
        // Silently handle canceled requests (401/session errors)
        if (error.code !== 'ERR_CANCELED' && error.response?.status !== 401) {
            console.error('Failed to fetch traffic:', error);
        }
    } finally {
        loadingVisits.value = false;
    }
};

onMounted(() => {
    fetchStats();
    fetchTraffic();
});
</script>
