<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ t('isp.monitor.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('isp.monitor.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Badge :variant="globalStats.network_health === 'Healthy' ? 'success' : 'warning'" class="px-3 py-1">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full animate-pulse bg-current"></div>
                        {{ t('isp.monitor.health', { status: globalStats.network_health }) }}
                    </div>
                </Badge>
                <div class="text-xs text-muted-foreground ml-4">
                    {{ t('isp.monitor.last_updated') }}: {{ lastUpdated }}
                </div>
            </div>
        </div>

        <!-- Global Throughput Chart -->
        <Card class="p-6 overflow-x-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-lg font-bold">{{ t('isp.monitor.throughput_title') }}</h2>
                    <p class="text-xs text-muted-foreground text-opacity-70">{{ t('isp.monitor.throughput_subtitle') }}</p>
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
                    label="Incoming"
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
            <Card v-for="node in nodes" :key="node.node_id" class="p-4 transition-all hover:border-primary/50 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div>
                        <h3 class="font-bold text-sm truncate w-32">{{ node.name }}</h3>
                        <p class="text-[10px] text-muted-foreground font-mono">{{ node.status }}</p>
                    </div>
                    <Badge variant="outline" class="text-[10px]">{{ node.latency }}ms</Badge>
                </div>

                <div class="space-y-3 relative z-10">
                    <div class="flex justify-between items-end">
                        <span class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold text-opacity-60">{{ t('isp.monitor.node_cpu') }}</span>
                        <span class="text-xs font-mono">{{ node.cpu_load }}%</span>
                    </div>
                    <div class="w-full bg-muted/40 h-1 rounded-full overflow-hidden">
                        <div 
                            class="h-full transition-all duration-500 bg-primary" 
                            :style="{ width: node.cpu_load + '%' }"
                            :class="node.cpu_load > 80 ? 'bg-destructive' : node.cpu_load > 50 ? 'bg-warning' : 'bg-primary'"
                        ></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-4 pt-2 border-t border-border/10">
                        <div>
                            <p class="text-[9px] text-muted-foreground uppercase opacity-70">IN</p>
                            <p class="text-sm font-mono leading-none">{{ node.traffic_in }}M</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] text-muted-foreground uppercase opacity-70">OUT</p>
                            <p class="text-sm font-mono leading-none">{{ node.traffic_out }}M</p>
                        </div>
                    </div>
                </div>

                <!-- Subtle background icon -->
                <Server class="absolute -right-2 -bottom-2 w-16 h-16 text-primary opacity-[0.03] group-hover:scale-110 transition-transform" />
            </Card>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { logger } from '@/utils/logger';
import { Card, Badge } from '@/components/ui';
import Server from 'lucide-vue-next/dist/esm/icons/server.js';
import LucideLoader from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import LineChart from '@/components/charts/LineChart.vue';
import api from '@/services/api';
import { ensureArray } from '@/utils/responseParser';
import { useI18n } from 'vue-i18n';
import type { MonitorNode, IspMonitorStats, IspMonitorHistoryPoint } from '@/types/isp';

const { t } = useI18n();

const nodes = ref<MonitorNode[]>([]);
const historyData = ref<IspMonitorHistoryPoint[]>([]);
const globalStats = ref<IspMonitorStats>({
    total_traffic_in: 0,
    total_traffic_out: 0,
    active_customers: 0,
    network_health: t('isp.monitor.connecting'),
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
        const response = await api.get('/admin/ja/isp/monitor/stats');
        const data = response.data?.data || response.data || {};
        
        globalStats.value = data.global || globalStats.value;
        nodes.value = ensureArray(data.nodes);
        historyData.value = ensureArray(data.history);
        lastUpdated.value = new Date().toLocaleTimeString();
    } catch (error: unknown) {
        // Narrow error to check for axios cancellation
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

<style scoped>
/* Any specific dashboard styles */
</style>
