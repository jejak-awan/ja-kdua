<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('features.dashboard.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}</p>
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

        <!-- Row 2: Recent Activity (Full Width) -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle>{{ $t('features.dashboard.traffic.visits') }}</CardTitle>
                <!-- Time Range Filter -->
                <div class="w-[180px]">
                    <Select v-model="timeRange" @update:modelValue="fetchStats">
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
                    <div class="h-[250px] w-full">
                    <LineChart 
                        v-if="activityData.length > 0"
                        :data="activityData" 
                        :label="$t('features.dashboard.traffic.visits')"
                    />
                    <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground">
                        <Activity class="w-10 h-10 mb-2 opacity-50" />
                        <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
                    </div>
                    </div>
            </CardContent>
        </Card>

        <!-- Row 3: Status Distribution & Top Content & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
             <!-- Status Distribution (Col 1) -->
             <Card class="col-span-1 lg:col-span-1">
                <CardHeader>
                    <CardTitle>{{ $t('features.dashboard.stats.creator.contentStatus') }}</CardTitle>
                </CardHeader>
                <CardContent>
                     <div class="h-[250px] w-full flex items-center justify-center">
                        <DoughnutChart 
                            v-if="statusData.length > 0" 
                            :data="statusData" 
                            label-key="label"
                            value-key="count"
                        />
                        <div v-else class="text-center text-muted-foreground">
                             <PieChart class="w-10 h-10 mb-2 opacity-50 mx-auto" />
                             <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
                        </div>
                     </div>
                </CardContent>
            </Card>

            <!-- Top Content (Col 2-3) -->
            <Card class="col-span-1 lg:col-span-2">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Trophy class="w-5 h-5 text-amber-500" />
                        {{ $t('features.dashboard.stats.creator.topContent') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Table v-if="topContent.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ $t('features.dashboard.table.content') }}</TableHead>
                                <TableHead class="text-right">{{ $t('features.dashboard.table.views') }}</TableHead>
                                <TableHead class="text-right">{{ $t('features.dashboard.table.status') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="content in topContent" :key="content.id">
                                <TableCell class="font-medium">
                                    <div class="flex flex-col">
                                        <span class="truncate max-w-[200px]">{{ content.title }}</span>
                                        <span class="text-xs text-muted-foreground capitalize">{{ content.type }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">{{ content.views }}</TableCell>
                                <TableCell class="text-right">
                                    <Badge variant="outline" :class="getStatusColor(content.status)" class="capitalize">
                                        {{ mapStatusToLabel(content.status) }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                     <div v-else class="h-[200px] flex flex-col items-center justify-center text-muted-foreground">
                        <FileText class="w-10 h-10 mb-2 opacity-50" />
                        <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions (Col 4) -->
            <div class="col-span-1 lg:col-span-1">
                 <QuickActions />
            </div>
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
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import Badge from '@/components/ui/badge.vue';
import Table from '@/components/ui/table.vue';
import TableBody from '@/components/ui/table-body.vue';
import TableCell from '@/components/ui/table-cell.vue';
import TableHead from '@/components/ui/table-head.vue';
import TableHeader from '@/components/ui/table-header.vue';
import TableRow from '@/components/ui/table-row.vue';

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
    Activity,
    Trophy
} from 'lucide-vue-next';

const { t } = useI18n();
const authStore = useAuthStore();
const stats = ref({});
const statusData = ref([]);
const activityData = ref([]);
const topContent = ref([]);
const loading = ref(false);
const timeRange = ref('30'); // Default to 30 days

const mapStatusToLabel = (status) => {
    // Map status to translation key
    // assuming status are: published, draft, pending
    const map = {
        'published': t('features.dashboard.stats.creator.published'),
        'draft': t('features.dashboard.stats.creator.drafts'),
        'pending': t('features.dashboard.stats.creator.pendingReview'),
    };
    return map[status] || status; // Fallback to raw status
};

const getStatusColor = (status) => {
    switch (status) {
        case 'published': return 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
        case 'pending': return 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800';
        case 'draft': return 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800';
        default: return 'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800';
    }
};

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/creator', {
            params: { days: timeRange.value }
        });
        const data = parseSingleResponse(response);
        
        if (data) {
            // Stats
            if (data.stats) {
                stats.value = data.stats;
            }
            
            // Content Status Chart
            if (data.charts && data.charts.myContentByStatus) {
                const rawStatus = ensureArray(data.charts.myContentByStatus);
                statusData.value = rawStatus.map(item => ({
                    label: mapStatusToLabel(item.status),
                    count: item.count
                }));
            }
            
            // Recent Activity Chart (Now Content Traffic)
            // Note: API key changed to 'contentTraffic'
            const rawTraffic = data.charts.contentTraffic ? ensureArray(data.charts.contentTraffic) : (data.charts.recentActivity ? ensureArray(data.charts.recentActivity) : []);
            
            activityData.value = rawTraffic.map(item => ({
                period: item.date,
                visits: item.count
            }));

            // Top Content
            if (data.topContent) {
                 topContent.value = ensureArray(data.topContent);
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
