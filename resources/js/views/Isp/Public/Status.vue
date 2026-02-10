<template>
    <div class="min-h-screen bg-background flex flex-col items-center py-12 px-4 animate-in fade-in duration-700">
        <!-- Brand -->
        <div class="flex items-center gap-3 mb-12">
            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-primary-foreground">
                <Globe class="w-6 h-6" />
            </div>
            <h1 class="text-2xl font-black tracking-tighter uppercase transition-all hover:tracking-normal cursor-default">
                K2NET <span class="text-primary font-light">Status</span>
            </h1>
        </div>

        <div class="w-full max-w-2xl space-y-8">
            <!-- Health Status Card -->
            <Card class="rounded-3xl border-none shadow-xl overflow-hidden bg-card/50 backdrop-blur-sm border border-border/50">
                <CardContent class="p-8">
                    <div v-if="loading" class="flex justify-center py-8">
                        <Loader2 class="w-8 h-8 animate-spin text-primary" />
                    </div>
                    
                    <div v-else class="text-center space-y-4">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-2" :class="statusData?.is_healthy ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive'">
                            <CheckCircle2 v-if="statusData?.is_healthy" class="w-10 h-10" />
                            <AlertCircle v-else class="w-10 h-10" />
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight">
                            {{ statusData?.is_healthy ? t('isp.outages.public.operational') : t('isp.outages.public.issues') }}
                        </h2>
                        <p class="text-muted-foreground">
                            {{ statusData?.is_healthy ? t('isp.outages.public.description_healthy') : t('isp.outages.public.description_issues') }}
                        </p>
                        <div class="pt-4 text-[10px] text-muted-foreground uppercase tracking-widest">
                            {{ t('isp.outages.public.last_checked') }}: {{ dayjs(statusData?.last_updated).format('HH:mm:ss') }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Active Incidents -->
            <div v-if="statusData?.active_incidents?.length" class="space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-muted-foreground px-2">
                    {{ t('isp.outages.public.active') }}
                </h3>
                <Card v-for="incident in statusData.active_incidents" :key="incident.id" class="rounded-2xl border-l-4 border-l-destructive shadow-sm">
                    <CardHeader class="pb-2">
                        <div class="flex justify-between items-start">
                            <CardTitle class="text-lg">{{ incident.title }}</CardTitle>
                            <Badge variant="outline" class="capitalize">{{ incident.status }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground mb-4">{{ incident.description }}</p>
                        <div class="flex items-center gap-4 text-xs text-muted-foreground font-mono">
                            <div class="flex items-center gap-1">
                                <MapPin class="w-3 h-3" />
                                {{ incident.node?.name || 'Global' }}
                            </div>
                            <div class="flex items-center gap-1">
                                <Clock class="w-3 h-3" />
                                Since {{ dayjs(incident.started_at).format('MMM DD, HH:mm') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent History -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-muted-foreground px-2">
                    {{ t('isp.outages.public.recent') }}
                </h3>
                <div class="bg-card/30 rounded-2xl border border-border divide-y divide-border overflow-hidden">
                    <div v-if="!statusData?.recent_resolved?.length" class="p-8 text-center text-sm text-muted-foreground">
                        {{ t('isp.outages.public.no_incidents') }}
                    </div>
                    <div v-for="incident in statusData?.recent_resolved" :key="incident.id" class="p-6 flex items-start gap-4">
                        <CheckCircle2 class="w-5 h-5 text-success mt-0.5 shrink-0" />
                        <div class="space-y-1">
                            <div class="font-bold">{{ incident.title }}</div>
                            <p class="text-xs text-muted-foreground line-clamp-2">{{ incident.description }}</p>
                            <div class="text-[10px] text-muted-foreground uppercase pt-1">
                                Resolved on {{ dayjs(incident.resolved_at).format('MMM DD, HH:mm') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center pt-8">
                <Button variant="link" class="text-muted-foreground gap-2" @click="$router.push('/')">
                    <ArrowLeft class="w-4 h-4" />
                    {{ t('isp.outages.public.back') }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { 
    Card, 
    CardContent, 
    CardHeader, 
    CardTitle,
    Badge,
    Button
} from '@/components/ui';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import dayjs from 'dayjs';

const { t } = useI18n();

interface Incident {
    id: number;
    title: string;
    description: string;
    status: string;
    started_at: string;
    resolved_at: string | null;
    node?: { name: string };
}

interface StatusData {
    is_healthy: boolean;
    active_incidents: Incident[];
    recent_resolved: Incident[];
    last_updated: string;
}

const statusData = ref<StatusData | null>(null);
const loading = ref(true);

const fetchStatus = async () => {
    loading.value = true;
    try {
        const response = await api.get('/api/core/public/isp/status');
        if (response.data.success) {
            statusData.value = response.data.data;
        }
    } catch (_error) {
        console.error('Failed to fetch status');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStatus();
    // Poll every minute
    setInterval(fetchStatus, 60000);
});
</script>
