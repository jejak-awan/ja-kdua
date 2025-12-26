<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.dashboard.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="fetchStats" :disabled="loadingVisits">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loadingVisits }" />
                    {{ $t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Row 1: Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Contents Card -->
            <Card class="bg-card/50 border-none shadow-sm hover:bg-card transition-colors">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalContents') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-indigo-500 font-medium">
                                <FileText class="w-3 h-3" />
                                <span>{{ stats.contents?.published || 0 }} {{ $t('features.dashboard.stats.published') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-500">
                            <Library class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Media Card -->
            <Card class="bg-card/50 border-none shadow-sm hover:bg-card transition-colors">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.mediaFiles') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.media?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-emerald-500 font-medium">
                                <Image class="w-3 h-3" />
                                <span>{{ $t('common.status.online') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
                            <FolderOpen class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Card -->
            <Card class="bg-card/50 border-none shadow-sm hover:bg-card transition-colors">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.totalUsers') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.users?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-purple-500 font-medium">
                                <Users class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.activeUsers') || 'Active now' }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-500">
                            <UserCheck class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Card -->
            <Card class="bg-card/50 border-none shadow-sm hover:bg-card transition-colors">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.pendingContent') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.contents?.pending || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-amber-500 font-medium">
                                <AlertCircle class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.requiresReview') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-500">
                            <Clock3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Row 2: Traffic Chart & System Health -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Traffic Chart (Main) -->
            <Card class="lg:col-span-2">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            {{ $t('features.dashboard.traffic.title') }}
                        </CardTitle>
                        <CardDescription>{{ $t('features.dashboard.traffic.last7Days') }}</CardDescription>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="h-[350px] mt-4">
                        <div v-if="loadingVisits" class="h-full flex items-center justify-center">
                            <Loader2 class="h-8 w-8 text-primary animate-spin" />
                        </div>
                        <LineChart
                            v-else-if="visits.length > 0"
                            :data="visits"
                            :label="$t('features.dashboard.traffic.visits')"
                            :gradient="true"
                        />
                         <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-2">
                            <AreaChart class="w-10 h-10 opacity-20" />
                            <p>{{ $t('features.dashboard.traffic.noData') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- System Health (Side) -->
            <div class="lg:col-span-1">
                <SystemHealthWidget class="h-full" />
            </div>
        </div>

        <!-- Row 3: Recent Activity & Quick Actions -->
         <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity (Main) -->
            <div class="lg:col-span-2">
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
