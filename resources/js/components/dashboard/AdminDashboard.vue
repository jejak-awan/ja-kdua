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
            <Card class="hover:shadow-md transition-all duration-300">
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
            <Card class="hover:shadow-md transition-all duration-300">
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
            <Card class="hover:shadow-md transition-all duration-300" v-if="authStore.hasPermission('manage users')">
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
            <Card class="hover:shadow-md transition-all duration-300" v-if="authStore.hasPermission('approve content')">
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

        <!-- Row 2: Traffic Chart (Full Width) -->
        <div class="w-full" v-if="authStore.hasPermission('view analytics')">
            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <div class="space-y-1">
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BarChart3 class="w-5 h-5 text-primary" />
                            {{ $t('features.dashboard.traffic.title') }}
                        </CardTitle>
                        <CardDescription>
                            {{ $t('features.dashboard.traffic.overview') }}
                        </CardDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <select v-model="visitPeriod" class="h-8 w-[120px] rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm ring-offset-background focus:outline-none focus:ring-1 focus:ring-ring">
                            <option value="7d">{{ $t('common.timePeriods.last7Days') }}</option>
                            <option value="30d">{{ $t('common.timePeriods.last30Days') }}</option>
                            <option value="90d">{{ $t('common.timePeriods.last3Months') }}</option>
                        </select>
                    </div>
                </CardHeader>
                <CardContent class="pl-0">
                    <div class="h-[350px] w-full">
                        <LineChart 
                            :data="visitsData" 
                            :categories="visitsCategories"
                            :loading="loadingVisits" 
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Row 3: Recent Activity & System Health -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" v-if="authStore.hasPermission('view system')">
            <!-- Recent Activity -->
            <RecentActivityWidget />

            <!-- System Health -->
            <SystemHealthWidget />
        </div>
        
        <!-- Row 4: Quick Actions -->
        <div class="mt-8" v-if="authStore.hasPermission('create content') || authStore.hasPermission('upload media')">
             <QuickActions />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import { parseResponse, ensureArray } from '../../utils/responseParser';

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
    FileText, 
    Image, 
    Users, 
    AlertCircle, 
    Library, 
    FolderOpen, 
    UserCheck, 
    Clock3,
    BarChart3,
    RefreshCw
} from 'lucide-vue-next';

const authStore = useAuthStore();
const stats = ref({});
const visitsData = ref([]);
const visitsCategories = ref([]);
const loadingVisits = ref(false);
const visitPeriod = ref('7d');

const fetchStats = async () => {
    try {
        // Use the new consolidated admin endpoint
        const response = await api.get('/dashboard/admin'); 
        const data = parseResponse(response);
        
        // Map the new API structure to match what the template expects
        if (data.stats) {
            stats.value = {
                contents: data.stats.contents || { total: 0, published: 0, pending: 0 },
                media: data.stats.media || { total: 0 },
                users: data.stats.users || { total: 0, active: 0 }
            };
        }
    } catch (error) {
        console.error('Failed to fetch statistics:', error);
    }
};

const fetchVisits = async () => {
    if (!authStore.hasPermission('view analytics')) return;
    
    loadingVisits.value = true;
    try {
        // Use the original analytics endpoint which supports period filtering
        const response = await api.get('/admin/cms/analytics/visits', {
            params: { period: visitPeriod.value }
        });
        const data = parseResponse(response);
        
        visitsCategories.value = ensureArray(data.labels);
        visitsData.value = [{
            name: t('features.dashboard.traffic.visitors'),
            data: ensureArray(data.values)
        }];
    } catch (error) {
        console.error('Failed to fetch visits:', error);
        // Fallback or empty state
        visitsCategories.value = [];
        visitsData.value = [];
    } finally {
        loadingVisits.value = false;
    }
};

const refreshDashboard = () => {
    fetchStats();
    fetchVisits();
};

watch(visitPeriod, () => {
    fetchVisits();
});

onMounted(() => {
    fetchStats();
    fetchVisits();
});
</script>
