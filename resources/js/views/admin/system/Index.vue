<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.system.info.title') }}</h1>
        </div>

        <!-- System Health -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-card shadow rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.info.health.title') }}</p>
                        <p class="text-2xl font-semibold mt-1" :class="systemHealth === 'healthy' ? 'text-green-600' : systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'">
                            {{ systemHealth === 'healthy' ? t('features.system.info.health.healthy') : systemHealth === 'warning' ? t('features.system.info.health.warning') : t('features.system.info.health.critical') }}
                        </p>
                    </div>
                    <div>
                        <svg
                            class="h-12 w-12"
                            :class="systemHealth === 'healthy' ? 'text-green-600' : systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                v-if="systemHealth === 'healthy'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-card shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.info.cache.title') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ cacheStatus || t('features.system.info.cache.active') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.info.uptime') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ formatUptime(systemInfo.uptime) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="bg-card shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-foreground">{{ t('features.system.info.title') }}</h2>
                <button
                    @click="clearCache"
                    :disabled="clearingCache"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm"
                >
                    {{ clearingCache ? t('features.system.info.cache.clearing') : t('features.system.info.cache.clear') }}
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-foreground mb-3">{{ t('features.system.info.sections.application') }}</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.phpVersion') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.php_version || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.laravelVersion') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.laravel_version || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.environment') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.environment || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.debugMode') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.debug_mode ? t('features.system.info.sections.enabled') : t('features.system.info.sections.disabled') }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-foreground mb-3">{{ t('features.system.info.sections.server') }}</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.serverSoftware') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.server_software || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.memoryUsage') }}</dt>
                            <dd class="text-sm text-foreground">{{ formatMemory(systemInfo.memory_usage) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.diskUsage') }}</dt>
                            <dd class="text-sm text-foreground">{{ formatDiskUsage(systemInfo.disk_usage) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.database') }}</dt>
                            <dd class="text-sm text-foreground">{{ systemInfo.database || '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="bg-card shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.system.info.statistics.title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-muted rounded-lg">
                    <p class="text-2xl font-semibold text-foreground">{{ statistics.total_contents || 0 }}</p>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.system.info.statistics.contents') }}</p>
                </div>
                <div class="text-center p-4 bg-muted rounded-lg">
                    <p class="text-2xl font-semibold text-foreground">{{ statistics.total_users || 0 }}</p>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.system.info.statistics.users') }}</p>
                </div>
                <div class="text-center p-4 bg-muted rounded-lg">
                    <p class="text-2xl font-semibold text-foreground">{{ statistics.total_media || 0 }}</p>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.system.info.statistics.media') }}</p>
                </div>
                <div class="text-center p-4 bg-muted rounded-lg">
                    <p class="text-2xl font-semibold text-foreground">{{ statistics.total_visits || 0 }}</p>
                    <p class="text-sm text-muted-foreground mt-1">{{ t('features.system.info.statistics.visits') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();

const systemInfo = ref({});
const statistics = ref(null);
const cacheStatus = ref('Active');
const clearingCache = ref(false);

const systemHealth = computed(() => {
    if (!systemInfo.value) return 'healthy';
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

const fetchSystemInfo = async () => {
    try {
        const response = await api.get('/admin/cms/system/info');
        systemInfo.value = parseSingleResponse(response) || {};
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/system/statistics');
            statistics.value = parseSingleResponse(statsResponse) || {};
        } catch (error) {
            // Set default if endpoint doesn't exist
            statistics.value = {
                total_contents: 0,
                total_users: 0,
                total_media: 0,
                total_visits: 0,
            };
        }
        
        // Fetch cache status
        try {
            const cacheResponse = await api.get('/admin/cms/system/cache-status');
            const cacheData = parseSingleResponse(cacheResponse) || {};
            cacheStatus.value = cacheData.status || 'Active';
        } catch (error) {
            cacheStatus.value = 'Active';
        }
    } catch (error) {
        console.error('Failed to fetch system info:', error);
    }
};

const clearCache = async () => {
    clearingCache.value = true;
    try {
        await api.post('/admin/cms/cache/clear');
        alert(t('features.system.info.cache.success'));
        await fetchSystemInfo();
    } catch (error) {
        console.error('Failed to clear cache:', error);
        alert(t('features.system.info.cache.failed'));
    } finally {
        clearingCache.value = false;
    }
};

const formatUptime = (seconds) => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};

const formatMemory = (bytes) => {
    if (!bytes) return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDiskUsage = (usage) => {
    if (!usage) return '-';
    if (typeof usage === 'object') {
        return `${formatMemory(usage.used)} / ${formatMemory(usage.total)} (${usage.percent || 0}%)`;
    }
    return usage;
};

onMounted(() => {
    fetchSystemInfo();
});
</script>

