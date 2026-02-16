<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-foreground">Infrastructure Health</h2>
                <p class="text-sm text-muted-foreground mt-1">Monitor real-time hardware vitals and resource utilization.</p>
            </div>
            <div class="flex items-center gap-2">
                <Badge variant="outline" class="h-8 rounded-xl">
                    <Activity class="w-3 h-3 mr-1 text-green-500" />
                    Live Telemetry
                </Badge>
                <Button variant="outline" size="sm" class="rounded-xl" @click="fetchNodes">
                    <RefreshCw :class="['w-4 h-4 mr-2', loading && 'animate-spin']" />
                    Refresh All
                </Button>
            </div>
        </div>

        <div v-if="loading && nodes.length === 0" class="flex items-center justify-center h-64">
            <Loader2 class="w-8 h-8 animate-spin text-primary" />
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card v-for="node in nodes" :key="node.id" class="overflow-hidden border border-border/40 hover:border-primary/20 transition-all group rounded-xl">
                <CardHeader class="p-4 bg-muted/30 border-b border-border/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div :class="['p-2 rounded-xl', node.type === 'Router' ? 'bg-blue-500/10 text-blue-500' : 'bg-purple-500/10 text-purple-500']">
                                <Router v-if="node.type === 'Router'" class="w-5 h-5" />
                                <Cpu v-else class="w-5 h-5" />
                            </div>
                            <div>
                                <CardTitle class="text-sm font-bold">{{ node.name }}</CardTitle>
                                <CardDescription class="text-[10px] font-mono">{{ node.ip_address }}</CardDescription>
                            </div>
                        </div>
                        <Badge :variant="node.status === 'active' ? 'default' : 'secondary'" class="rounded-xl">
                            {{ node.status }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="p-4 space-y-4">
                    <!-- Vitals Grid -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <p class="text-[10px] text-muted-foreground font-semibold">CPU Load</p>
                            <div class="flex items-end gap-2">
                                <span class="text-xl font-black">{{ node.cpu_load || 0 }}%</span>
                                <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden mb-1">
                                    <div 
                                        :class="['h-full transition-all duration-1000', getLoadColor(node.cpu_load || 0)]"
                                        :style="{ width: (node.cpu_load || 0) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1 text-right border-l border-border/20 pl-4">
                            <p class="text-[10px] text-muted-foreground font-semibold">Memory Usage</p>
                            <p class="text-xl font-black">
                                {{ formatBytes(node.memory_usage || 0) }}
                            </p>
                            <p class="text-[9px] text-muted-foreground">Usage</p>
                        </div>
                        <div class="space-y-1 text-right border-l border-border/20 pl-4">
                            <p class="text-[10px] text-muted-foreground font-semibold">Uptime</p>
                            <p class="text-sm font-bold py-1">{{ node.uptime || 'N/A' }}</p>
                            <div class="flex items-center justify-end gap-1 text-[9px] text-green-500">
                                <ShieldCheck class="w-3 h-3" />
                                Secure
                            </div>
                        </div>
                    </div>

                    <!-- Traffic Quick Look -->
                    <div class="p-3 bg-muted/20 rounded-xl border border-border/10 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <ArrowDown class="w-3 h-3 text-blue-500" />
                                <span class="text-xs font-bold">{{ node.traffic_in || '0bps' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <ArrowUp class="w-3 h-3 text-primary" />
                                <span class="text-xs font-bold">{{ node.traffic_out || '0bps' }}</span>
                            </div>
                        </div>
                        <div class="text-[10px] font-mono text-muted-foreground">ether1-gateway</div>
                    </div>
                </CardContent>
                <CardFooter class="p-3 bg-muted/10 border-t border-border/10 flex justify-end gap-2">
                    <Button variant="ghost" size="sm" class="h-8 text-xs rounded-xl">
                        <Settings class="w-3 h-3 mr-1" />
                        Configure
                    </Button>
                    <Button variant="outline" size="sm" class="h-8 text-xs rounded-xl" @click="fetchVitals(node)">
                        <Zap class="w-3 h-3 mr-1" />
                        Force Sync
                    </Button>
                </CardFooter>
            </Card>
        </div>

        <div v-if="!loading && nodes.length === 0" class="text-center py-20 bg-muted/10 rounded-3xl border border-dashed">
            <Router class="w-12 h-12 mx-auto text-muted-foreground/30 mb-4" />
            <p class="text-muted-foreground font-medium">No service nodes configured for monitoring.</p>
        </div>

        <!-- Enterprise Assurance Section -->
        <Card class="rounded-3xl border border-border/40 bg-primary/5 overflow-hidden shadow-sm">
            <CardHeader class="p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                        <FileText class="w-5 h-5" />
                    </div>
                    <div>
                        <CardTitle>Enterprise Assurance</CardTitle>
                        <CardDescription>Generate official Network Health Certificates for your clients.</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-6 pt-0">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1 space-y-2">
                        <Label>Customer Search</Label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input 
                                v-model="customerSearch" 
                                placeholder="Enter Customer ID or Name..." 
                                class="pl-10 h-11 rounded-xl bg-background border-border/20"
                            />
                        </div>
                    </div>
                    <Button 
                        size="lg" 
                        class="h-11 px-8 rounded-xl shadow-md" 
                        :disabled="!customerSearch || generatingReport"
                        @click="generateReport"
                    >
                        <Loader2 v-if="generatingReport" class="w-4 h-4 mr-2 animate-spin" />
                        <FileDown v-else class="w-4 h-4 mr-2" />
                        Generate Certificate
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import { 
    Card, CardHeader, CardContent, CardTitle, CardDescription, CardFooter,
    Button, Badge
} from '@/components/ui';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import Cpu from 'lucide-vue-next/dist/esm/icons/cpu.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowDown from 'lucide-vue-next/dist/esm/icons/arrow-down.js';
import ArrowUp from 'lucide-vue-next/dist/esm/icons/arrow-up.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import FileDown from 'lucide-vue-next/dist/esm/icons/file-down.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';

const loading = ref(true);
const nodes = ref<any[]>([]);
const customerSearch = ref('');
const generatingReport = ref(false);
let refreshInterval: any = null;

const generateReport = async () => {
    if (!customerSearch.value) return;
    generatingReport.value = true;
    try {
        // Find customer first (simulated search or direct ID)
        // In a real app, you'd probably have a search endpoint, but here we can try direct ID
        const url = `/admin/janet/isp/network/monitor/olt/health-report/${customerSearch.value}`;
        window.open(api.defaults.baseURL + url, '_blank');
    } catch (_e) {
        console.error('Failed to trigger report');
    } finally {
        generatingReport.value = false;
    }
};

const fetchNodes = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/network/health/vitals');
        nodes.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to fetch node vitals', error);
    } finally {
        loading.value = false;
    }
};

const fetchVitals = async (node: any) => {
    try {
        const response = await api.get(`/admin/janet/isp/network/health/vitals/${node.id}`);
        const data = response.data.data;
        // Merge individual stats into the node object
        Object.assign(node, data);
    } catch (error) {
        console.error(`Failed to fetch vitals for node ${node.id}`, error);
    }
};

const getLoadColor = (cpu: number) => {
    if (cpu > 80) return 'bg-destructive';
    if (cpu > 50) return 'bg-yellow-500';
    return 'bg-green-500';
};

const formatBytes = (value: any) => {
    if (typeof value === 'string') return value;
    if (value === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(value) / Math.log(k));
    return parseFloat((value / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

onMounted(() => {
    fetchNodes();
    refreshInterval = setInterval(() => {
        fetchNodes(); // Refresh all using the bulk endpoint
    }, 15000); // Auto-refresh every 15s
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
