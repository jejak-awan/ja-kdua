<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</h1>
                <p class="text-muted-foreground">{{ $t('features.dashboard.creatorSubtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="refreshDashboard" :disabled="loading">
                    <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
                    {{ $t('common.actions.refresh') }}
                </Button>
            </div>
        </div>

        <!-- Row 1: Personal Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- My Contents -->
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.creator.myContent') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.myContents?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-indigo-500 font-medium">
                                <FileText class="w-3 h-3" />
                                <span>{{ stats.myContents?.published || 0 }} {{ $t('features.dashboard.stats.creator.published') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-500">
                            <PenTool class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pending Review -->
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.creator.pendingReview') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.myContents?.pending || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-amber-500 font-medium">
                                <Clock3 class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.creator.awaitingApproval') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-500">
                            <AlertCircle class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- My Media -->
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.creator.myMedia') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.myMedia?.total || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-emerald-500 font-medium">
                                <Image class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.creator.uploadedFiles') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-500">
                            <FolderOpen class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Drafts -->
            <Card class="hover:shadow-md transition-all duration-300">
                <CardContent class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-muted-foreground">{{ $t('features.dashboard.stats.creator.drafts') }}</p>
                            <p class="text-3xl font-bold text-foreground">{{ stats.myContents?.draft || 0 }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                                <FileText class="w-3 h-3" />
                                <span>{{ $t('features.dashboard.stats.creator.workInProgress') }}</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-500/10 text-slate-500">
                            <Edit3 class="w-5 h-5" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Row 2: Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
             <Card class="col-span-1">
                <CardHeader>
                    <CardTitle>{{ $t('features.dashboard.stats.creator.contentStatus') }}</CardTitle>
                </CardHeader>
                <CardContent>
                     <div class="h-[300px] w-full flex items-center justify-center">
                        <DoughnutChart 
                            v-if="statusData.length > 0" 
                            :data="statusData" 
                            :labels="statusLabels" 
                        />
                        <div v-else class="text-center text-muted-foreground">
                             <PieChart class="w-10 h-10 mb-2 opacity-50 mx-auto" />
                             <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
                        </div>
                     </div>
                </CardContent>
            </Card>
            
            <Card class="col-span-1">
                <CardHeader>
                    <CardTitle>{{ $t('features.dashboard.stats.creator.recentActivity') }}</CardTitle>
                </CardHeader>
                 <CardContent>
                     <div class="h-[300px] w-full">
                        <LineChart 
                            v-if="activityData.length > 0"
                            :data="[{ name: 'Activity', data: activityData }]" 
                            :categories="activityLabels"
                        />
                        <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground">
                            <Activity class="w-10 h-10 mb-2 opacity-50" />
                            <span class="text-sm">{{ $t('features.dashboard.widgets.recentActivity.empty') }}</span>
                        </div>
                     </div>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
             <QuickActions />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { parseSingleResponse, ensureArray } from '../../utils/responseParser';

import QuickActions from '@/components/admin/QuickActions.vue';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';
import Button from '@/components/ui/button.vue';
import DoughnutChart from '@/components/charts/DoughnutChart.vue';
import LineChart from '@/components/charts/LineChart.vue';

import { 
    FileText, 
    Image, 
    AlertCircle, 
    FolderOpen, 
    Clock3,
    RefreshCw,
    PenTool,
    Edit3,
    PieChart,
    Activity
} from 'lucide-vue-next';

const { t } = useI18n();
const authStore = useAuthStore();
const stats = ref({});
const statusData = ref([]);
const statusLabels = ref([]);
const activityData = ref([]);
const activityLabels = ref([]);
const loading = ref(false);

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/creator');
        const data = parseSingleResponse(response);
        
        if (data) {
            // Stats
            if (data.stats) {
                stats.value = data.stats;
            }
            
            // Content Status Chart
            if (data.charts && data.charts.myContentByStatus) {
                const statusChart = ensureArray(data.charts.myContentByStatus);
                statusLabels.value = statusChart.map(item => item.status);
                statusData.value = statusChart.map(item => item.count);
            }
            
            // Recent Activity Chart
            if (data.charts && data.charts.recentActivity) {
                const activityChart = ensureArray(data.charts.recentActivity);
                activityLabels.value = activityChart.map(item => item.date);
                activityData.value = activityChart.map(item => item.count);
            }
        }
    } catch (error) {
        console.error('Failed to fetch creator stats:', error);
    } finally {
        loading.value = false;
    }
};

const refreshDashboard = () => {
    fetchStats();
};

onMounted(() => {
    fetchStats();
});
</script>
