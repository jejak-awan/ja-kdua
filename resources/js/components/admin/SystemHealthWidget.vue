<template>
  <Card class="system-health-widget h-full flex flex-col overflow-hidden duration-300">
    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Activity class="w-5 h-5" :class="overallStatusClass" />
        {{ $t('features.dashboard.widgets.systemHealth.title') }}
      </CardTitle>
      <Button
        variant="ghost"
        size="icon"
        @click="refresh"
        :disabled="loading"
        class="h-8 w-8 text-muted-foreground hover:text-foreground"
        title="Refresh"
      >
        <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
      </Button>
    </CardHeader>

    <CardContent class="flex-1 space-y-6 pt-2">
      <!-- Overall Status -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-border/40">
        <span class="text-sm font-semibold text-foreground">{{ $t('features.dashboard.widgets.systemHealth.overallStatus') }}</span>
        <Badge :class="overallStatusBadgeClass" variant="outline" class="border-none font-bold text-[10px]">
          {{ overallStatusText }}
        </Badge>
      </div>

      <!-- Health Metrics -->
      <div class="space-y-4">
        <!-- CPU -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-muted-foreground flex items-center gap-1.5 ">
              <Cpu class="w-3.5 h-3.5" />
              {{ $t('features.dashboard.widgets.systemHealth.cpu') }}
            </span>
            <span :class="getStatusClass(health.cpu?.status)" class="text-xs font-bold tabular-nums">
              {{ health.cpu?.percent || 0 }}%
            </span>
          </div>
          <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
            <div
              :class="getProgressBarClass(health.cpu?.status)"
              :style="{ width: `${health.cpu?.percent || 0}%` }"
              class="h-full transition-all duration-500 rounded-full shadow-[0_0_10px_rgba(0,0,0,0.1)]"
            ></div>
          </div>
          <p v-if="health.cpu?.load" class="text-[10px] text-muted-foreground line-clamp-1 italic">
            Load: {{ health.cpu.load }}
          </p>
        </div>

        <!-- Memory -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-muted-foreground flex items-center gap-1.5">
              <Layers class="w-3.5 h-3.5" />
              {{ $t('features.dashboard.widgets.systemHealth.memory') }}
            </span>
            <span :class="getStatusClass(health.memory?.status)" class="text-xs font-bold tabular-nums">
              {{ health.memory?.percent || 0 }}%
            </span>
          </div>
          <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
            <div
              :class="getProgressBarClass(health.memory?.status)"
              :style="{ width: `${health.memory?.percent || 0}%` }"
              class="h-full transition-all duration-500 rounded-full shadow-[0_0_10px_rgba(0,0,0,0.1)]"
            ></div>
          </div>
          <p class="text-[10px] text-muted-foreground line-clamp-1 italic">
            {{ health.memory?.used || '0 B' }} / {{ health.memory?.total || '0 B' }}
          </p>
        </div>

        <!-- System Components Row -->
        <div class="grid grid-cols-2 gap-3 pt-2">
             <!-- Disk -->
            <div class="p-3 rounded-xl bg-muted/20 border border-border/40 space-y-2">
                <div class="flex items-center justify-between">
                    <HardDrive class="w-4 h-4 text-muted-foreground" />
                    <span :class="getStatusClass(health.disk?.status)" class="text-[10px] font-bold">
                        {{ health.disk?.percent || 0 }}%
                    </span>
                </div>
                <p class="text-[10px] font-bold text-foreground truncate opacity-60">{{ $t('features.dashboard.widgets.systemHealth.disk') }}</p>
                <div class="h-1 w-full bg-muted rounded-full overflow-hidden">
                    <div
                        :class="getProgressBarClass(health.disk?.status)"
                        :style="{ width: `${health.disk?.percent || 0}%` }"
                        class="h-full transition-all duration-500"
                    ></div>
                </div>
            </div>

            <!-- Database -->
            <div class="p-3 rounded-xl bg-muted/20 border border-border/40 space-y-2">
                <div class="flex items-center justify-between">
                    <Database class="w-4 h-4 text-muted-foreground" />
                    <Badge variant="outline" class="border-none p-0 h-auto" :class="getStatusClass(health.database?.status)">
                        <CheckCircle2 v-if="health.database?.status === 'ok'" class="w-3.5 h-3.5" />
                        <AlertCircle v-else class="w-3.5 h-3.5" />
                    </Badge>
                </div>
                <p class="text-[10px] font-bold text-foreground truncate opacity-60">{{ $t('features.dashboard.widgets.systemHealth.database') }}</p>
                <p class="text-[10px] text-muted-foreground truncate italic">
                    {{ health.database?.status === 'ok' ? $t('features.dashboard.widgets.systemHealth.status.ok') : $t('features.dashboard.widgets.systemHealth.status.error') }}
                </p>
            </div>
        </div>

        <!-- Redis -->
        <div class="p-3 rounded-xl bg-muted/20 border border-border/40 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-orange-500/10 text-orange-500">
                    <Zap class="w-4 h-4" />
                </div>
                <div>
                    <p class="text-[10px] font-bold text-foreground opacity-60">{{ $t('features.dashboard.widgets.systemHealth.redis') }}</p>
                    <p class="text-[10px] text-muted-foreground truncate italic max-w-[120px]">
                        {{ health.redis?.message || $t('features.dashboard.widgets.systemHealth.status.unknown') }}
                    </p>
                </div>
            </div>
            <Badge :class="getStatusClass(health.redis?.status)" variant="outline" class="border-none font-bold text-[10px]">
                {{ health.redis?.status === 'ok' ? $t('features.dashboard.widgets.systemHealth.status.ok') : health.redis?.status === 'disabled' ? $t('features.dashboard.widgets.systemHealth.status.disabled') : $t('features.dashboard.widgets.systemHealth.status.error') }}
            </Badge>
        </div>
      </div>
    </CardContent>

    <CardFooter v-if="lastUpdated" class="pb-4 pt-0 justify-center">
      <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground font-medium opacity-60">
        <Clock class="w-3 h-3" />
        {{ $t('features.dashboard.widgets.systemHealth.lastUpdated', { time: formatTime(lastUpdated) }) }}
      </div>
    </CardFooter>
  </Card>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { parseSingleResponse } from '@/utils/responseParser';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';
import CardFooter from '@/components/ui/card-footer.vue';
import Button from '@/components/ui/button.vue';
import Badge from '@/components/ui/badge.vue';
import { 
    Activity, 
    RefreshCw, 
    Cpu, 
    Layers, 
    HardDrive, 
    Database, 
    Zap, 
    Clock, 
    CheckCircle2, 
    AlertCircle 
} from 'lucide-vue-next';

const { t } = useI18n();
const authStore = useAuthStore();


const health = ref({
  cpu: { percent: 0, status: 'unknown' },
  memory: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  disk: { percent: 0, used: '0 B', total: '0 B', status: 'unknown' },
  database: { status: 'unknown', message: 'Unknown' },
  redis: { status: 'unknown', message: 'Unknown' },
  overall: 'unknown',
});

const loading = ref(false);
const lastUpdated = ref(null);
let refreshInterval = null;

const overallStatus = computed(() => health.value.overall || 'unknown');

const overallStatusText = computed(() => {
  const status = overallStatus.value;
  return t(`features.dashboard.widgets.systemHealth.status.${status}`);
});

const overallStatusClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'text-emerald-500';
  if (status === 'warning') return 'text-amber-500';
  if (status === 'critical') return 'text-rose-500';
  return 'text-muted-foreground';
});

const overallStatusBadgeClass = computed(() => {
  const status = overallStatus.value;
  if (status === 'healthy') return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
  if (status === 'warning') return 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
  if (status === 'critical') return 'bg-rose-500/10 text-rose-600 dark:text-rose-400';
  return 'bg-muted text-muted-foreground';
});

const getStatusClass = (status) => {
  if (status === 'ok') return 'text-emerald-600 dark:text-emerald-400';
  if (status === 'warning') return 'text-amber-600 dark:text-amber-400';
  if (status === 'critical' || status === 'error') return 'text-rose-600 dark:text-rose-400';
  return 'text-muted-foreground';
};

const getProgressBarClass = (status) => {
  if (status === 'ok') return 'bg-emerald-500';
  if (status === 'warning') return 'bg-amber-500';
  if (status === 'critical' || status === 'error') return 'bg-rose-500';
  return 'bg-muted-foreground/30';
};

const fetchHealth = async () => {
  if (window.__isSessionTerminated) {
    if (refreshInterval) clearInterval(refreshInterval);
    return;
  }

  // Permission check
  if (!authStore.hasPermission('manage system')) return;

  loading.value = true;
  try {
    const response = await api.get('/admin/ja/system/health/detailed');
    const data = parseSingleResponse(response);
    if (data) {
      health.value = data;
      lastUpdated.value = new Date();
    }
  } catch (error) {
    // Silently handle canceled requests (401/session errors)
    if (error.code !== 'ERR_CANCELED' && error.response?.status !== 401) {
      console.error('Failed to fetch system health:', error);
    }
  } finally {
    loading.value = false;
  }
};

const refresh = () => {
  fetchHealth();
};

const formatTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

onMounted(() => {
  fetchHealth();
  refreshInterval = setInterval(fetchHealth, 120000); // Increased from 30s to 2m
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
/* Scoped styles removed as we are using Tailwind utilities and Shadcn-aligned structure */
</style>

