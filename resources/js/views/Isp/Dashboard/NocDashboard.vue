<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.monitor.title') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.monitor.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Badge :variant="globalStats.network_health === 'Healthy' ? 'success' : 'warning'" class="px-3 py-1 shadow-sm border-none">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full animate-pulse bg-current"></div>
                        {{ t('isp.monitor.health', { status: t(`isp.monitor.health_status.${(globalStats.network_health || 'healthy').toLowerCase()}`) }) }}
                    </div>
                </Badge>
                <div class="text-xs text-muted-foreground ml-4">
                    {{ t('isp.monitor.last_updated') }}: {{ lastUpdated }}
                </div>
            </div>
        </div>

        <Tabs :default-value="activeTab" @update:model-value="onTabChange" class="w-full">
            <div class="mb-8 flex items-center justify-between border-b">
                <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
                    <TabsTrigger value="overview" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <Monitor class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.overview', 'Overview') }}
                    </TabsTrigger>
                    <TabsTrigger value="sessions" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <Wifi class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.sessions', 'Sessions') }}
                    </TabsTrigger>
                    <TabsTrigger value="radius" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <Shield class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.radius', 'RADIUS') }}
                    </TabsTrigger>
                    <TabsTrigger value="traffic" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <TrendingUp class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.traffic', 'Traffic') }}
                    </TabsTrigger>
                    <TabsTrigger value="health" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <HeartPulse class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.health', 'Health') }}
                    </TabsTrigger>
                    <TabsTrigger value="map" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <MapIcon class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.map', 'Map') }}
                    </TabsTrigger>
                    <TabsTrigger value="heatmap" class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors">
                        <MapPin class="w-4 h-4 mr-2" /> {{ t('isp.noc.tabs.heatmap', 'Heatmap') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <!-- Overview Tab (existing content) -->
            <TabsContent value="overview" class="mt-6 space-y-6">
                <!-- Global Throughput Chart -->
                <Card class="p-6 overflow-x-auto border border-border/40 shadow-sm rounded-xl bg-gradient-to-br from-card to-muted/30">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-lg font-bold">{{ t('isp.monitor.throughput_title') }}</h2>
                            <p class="text-xs text-muted-foreground opacity-70">{{ t('isp.monitor.throughput_subtitle') }}</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex flex-col items-end">
                                <span class="text-xs text-muted-foreground">{{ t('isp.monitor.incoming') }}</span>
                                <span class="text-lg font-mono font-bold text-primary">{{ globalStats.total_traffic_in }} Mbps</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-xs text-muted-foreground">{{ t('isp.monitor.outgoing') }}</span>
                                <span class="text-lg font-mono font-bold text-success">{{ globalStats.total_traffic_out }} Mbps</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-[250px] w-full">
                        <LineChart 
                            v-if="chartData.length > 0"
                            :data="chartData"
                            :label="t('isp.monitor.incoming')"
                            :compare-data="compareData"
                            :compare-label="t('isp.monitor.outgoing')"
                        />
                        <div v-else class="h-full flex items-center justify-center">
                            <LucideLoader class="w-6 h-6 animate-spin text-muted-foreground" />
                        </div>
                    </div>
                </Card>

                <!-- Node Status Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    <Card v-for="node in nodes" :key="node.node_id" class="p-4 transition-all hover:scale-[1.02] active:scale-[0.98] border border-border/40 shadow-sm rounded-xl bg-card/60 backdrop-blur-sm relative overflow-hidden group">
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div>
                                <h3 class="font-bold text-sm truncate w-32">{{ node.name }}</h3>
                                <p class="text-[10px] text-muted-foreground font-semibold opacity-60">{{ node.status || t('isp.common.status.active') }}</p>
                            </div>
                            <Badge variant="outline" class="text-[10px] font-bold border-primary/20 bg-primary/5 rounded-lg">{{ node.latency || '0.0' }}ms</Badge>
                        </div>
                        <div class="space-y-3 relative z-10">
                            <div class="flex justify-between items-end">
                                <span class="text-[10px] text-muted-foreground font-bold opacity-60">{{ t('isp.monitor.node_cpu') }}</span>
                                <span class="text-xs font-mono">{{ node.cpu || 0 }}%</span>
                            </div>
                            <div class="w-full bg-muted/40 h-1 rounded-full overflow-hidden">
                                <div 
                                    class="h-full transition-all duration-500 bg-primary" 
                                    :style="{ width: (node.cpu || 0) + '%' }"
                                    :class="(node.cpu || 0) > 80 ? 'bg-destructive' : (node.cpu || 0) > 50 ? 'bg-warning' : 'bg-primary'"
                                ></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-4 pt-2 border-t border-border/10">
                                <div>
                                    <p class="text-[9px] text-muted-foreground font-bold opacity-70">In</p>
                                    <p class="text-sm font-mono leading-none">{{ node.traffic_in || 0 }}M</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-muted-foreground font-bold opacity-70">Out</p>
                                    <p class="text-sm font-mono leading-none">{{ node.traffic_out || 0 }}M</p>
                                </div>
                            </div>
                        </div>
                        <Server class="absolute -right-2 -bottom-2 w-16 h-16 text-primary opacity-[0.03] group-hover:scale-110 transition-transform" />
                    </Card>
                </div>
            </TabsContent>

            <!-- Sessions Tab -->
            <TabsContent value="sessions" class="mt-6">
                <ActiveSessions v-if="loaded.sessions" />
            </TabsContent>

            <!-- RADIUS Tab -->
            <TabsContent value="radius" class="mt-6 space-y-6">
                <Tabs default-value="radius-sessions">
                    <TabsList class="bg-muted/30">
                        <TabsTrigger value="radius-sessions">{{ t('isp.noc.tabs.radius_sessions', 'Sessions') }}</TabsTrigger>
                        <TabsTrigger value="radius-logs">{{ t('isp.noc.tabs.radius_logs', 'Logs') }}</TabsTrigger>
                    </TabsList>
                    <TabsContent value="radius-sessions" class="mt-4">
                        <RadiusSessions v-if="loaded.radius" />
                    </TabsContent>
                    <TabsContent value="radius-logs" class="mt-4">
                        <RadiusLogs v-if="loaded.radius" />
                    </TabsContent>
                </Tabs>
            </TabsContent>

            <!-- Traffic Tab -->
            <TabsContent value="traffic" class="mt-6 space-y-6">
                <Tabs default-value="traffic-monitor">
                    <TabsList class="bg-muted/30">
                        <TabsTrigger value="traffic-monitor">{{ t('isp.noc.tabs.traffic_monitor', 'Monitor') }}</TabsTrigger>
                        <TabsTrigger value="traffic-report">{{ t('isp.noc.tabs.traffic_report', 'Report') }}</TabsTrigger>
                    </TabsList>
                    <TabsContent value="traffic-monitor" class="mt-4">
                        <TrafficMonitor v-if="loaded.traffic" />
                    </TabsContent>
                    <TabsContent value="traffic-report" class="mt-4">
                        <TrafficReport v-if="loaded.traffic" />
                    </TabsContent>
                </Tabs>
            </TabsContent>

            <!-- Health Tab -->
            <TabsContent value="health" class="mt-6">
                <HealthCenter v-if="loaded.health" />
            </TabsContent>

            <!-- Map Tab -->
            <TabsContent value="map" class="mt-6">
                <InfraMap v-if="loaded.map" />
            </TabsContent>

            <!-- Heatmap Tab -->
            <TabsContent value="heatmap" class="mt-6">
                <CoverageHeatmap v-if="loaded.heatmap" />
            </TabsContent>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { logger } from '@/utils/logger';
import { Card, Badge, Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import Monitor from 'lucide-vue-next/dist/esm/icons/monitor.js';
import Wifi from 'lucide-vue-next/dist/esm/icons/wifi.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import HeartPulse from 'lucide-vue-next/dist/esm/icons/heart-pulse.js';
import MapIcon from 'lucide-vue-next/dist/esm/icons/map.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import LucideLoader from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import LineChart from '@/components/charts/LineChart.vue';
import api from '@/services/api';
import { ensureArray } from '@/utils/responseParser';
import { useI18n } from 'vue-i18n';
import type { MonitorNode, IspMonitorStats, IspMonitorHistoryPoint } from '@/types/isp';

// Lazy-loaded sub-tabs
import { defineAsyncComponent } from 'vue';
const ActiveSessions = defineAsyncComponent(() => import('./ActiveSessions.vue'));
const RadiusSessions = defineAsyncComponent(() => import('./RadiusSessions.vue'));
const RadiusLogs = defineAsyncComponent(() => import('./RadiusLogs.vue'));
const TrafficMonitor = defineAsyncComponent(() => import('./TrafficMonitor.vue'));
const TrafficReport = defineAsyncComponent(() => import('./TrafficReport.vue'));
const HealthCenter = defineAsyncComponent(() => import('./HealthCenter.vue'));
const InfraMap = defineAsyncComponent(() => import('../Network/Infrastructure/Map.vue'));
const CoverageHeatmap = defineAsyncComponent(() => import('./CoverageHeatmap.vue'));

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const activeTab = ref((route.query.tab as string) || 'overview');
const loaded = reactive({
    sessions: false,
    radius: false,
    traffic: false,
    health: false,
    map: false,
    heatmap: false,
});

const onTabChange = (tab: string | number | boolean) => {
    const tabStr = String(tab);
    activeTab.value = tabStr;
    router.replace({ query: { ...route.query, tab: tabStr === 'overview' ? undefined : tabStr } });
    // Activate lazy load on first visit
    if (tabStr === 'sessions') loaded.sessions = true;
    if (tabStr === 'radius') loaded.radius = true;
    if (tabStr === 'traffic') loaded.traffic = true;
    if (tabStr === 'health') loaded.health = true;
    if (tabStr === 'map') loaded.map = true;
    if (tabStr === 'heatmap') loaded.heatmap = true;
};

// Trigger initial tab load if query param is set
if (activeTab.value !== 'overview') {
    onTabChange(activeTab.value);
}

// --- Overview data ---
const nodes = ref<MonitorNode[]>([]);
const historyData = ref<IspMonitorHistoryPoint[]>([]);
const globalStats = ref<IspMonitorStats>({
    total_traffic_in: 0,
    total_traffic_out: 0,
    active_customers: 0,
    network_health: 'connecting',
});
const lastUpdated = ref('');
let pollInterval: ReturnType<typeof setInterval> | null = null;

const chartData = computed(() => {
    return historyData.value.map(h => ({
        period: h.time,
        visits: h.in,
        label: t('isp.monitor.incoming')
    }));
});

const compareData = computed(() => {
    return historyData.value.map(h => ({
        period: h.time,
        visits: h.out
    }));
});

const fetchData = async () => {
    try {
        const response = await api.get('/admin/janet/isp/monitor/stats');
        const data = response.data?.data || response.data || {};
        
        globalStats.value = data.global || globalStats.value;
        nodes.value = ensureArray(data.nodes);
        historyData.value = ensureArray(data.history);
        lastUpdated.value = new Date().toLocaleTimeString('id-ID');
    } catch (error: unknown) {
        const err = error as Record<string, unknown>;
        const isAxiosError = error && typeof error === 'object' && 
                           (('name' in err && err.name === 'CanceledError') || 
                            ('code' in err && err.code === 'ERR_CANCELED'));
        
        if (!isAxiosError) {
            logger.error('Failed to fetch NOC stats:', error);
        }
    }
};

onMounted(() => {
    fetchData();
    pollInterval = setInterval(fetchData, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>
