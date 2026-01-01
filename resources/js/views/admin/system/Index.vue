<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.system.info.title') }}</h1>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-6 animate-pulse">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-card border border-border rounded-lg p-6 h-24"></div>
                <div class="bg-card border border-border rounded-lg p-6 h-24"></div>
                <div class="bg-card border border-border rounded-lg p-6 h-24"></div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-card border border-border rounded-lg p-6 h-64"></div>
                <div class="bg-card border border-border rounded-lg p-6 h-64"></div>
            </div>
        </div>

        <!-- Main Content (only show when loaded) -->
        <template v-else>
        <!-- System Health -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-card border border-border rounded-lg p-6">
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
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">{{ t('features.system.info.cache.title') }}</p>
                        <p class="text-2xl font-semibold text-foreground">{{ cacheStatus || t('features.system.info.cache.active') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-card border border-border rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- System Info -->
            <div class="lg:col-span-2 bg-card border border-border rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-foreground">{{ t('features.system.info.title') }}</h2>
                    <router-link
                        to="/admin/settings?tab=performance"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ t('features.system.info.cache.manage') }}
                    </router-link>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">{{ t('features.system.info.sections.application') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.phpVersion') }}</dt>
                                <dd class="text-sm text-foreground font-mono">{{ systemInfo.php_version || '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.laravelVersion') }}</dt>
                                <dd class="text-sm text-foreground font-mono">{{ systemInfo.laravel_version || '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.environment') }}</dt>
                                <dd class="text-sm text-foreground capitalize">{{ systemInfo.environment || '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.debugMode') }}</dt>
                                <dd class="text-sm" :class="systemInfo.debug_mode ? 'text-red-500' : 'text-green-500'">{{ systemInfo.debug_mode ? t('features.system.info.sections.enabled') : t('features.system.info.sections.disabled') }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">{{ t('features.system.info.sections.server') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.serverSoftware') }}</dt>
                                <dd class="text-sm text-foreground truncate max-w-[200px]" :title="systemInfo.server_software">{{ systemInfo.server_software || '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.memoryUsage') }}</dt>
                                <dd class="text-sm text-foreground font-mono">{{ displayMemory }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.diskUsage') }}</dt>
                                <dd class="text-sm text-foreground font-mono">{{ displayDisk }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-muted-foreground">{{ t('features.system.info.sections.database') }}</dt>
                                <dd class="text-sm text-foreground font-semibold">{{ systemInfo.database || '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-card border border-border rounded-lg p-6">
                <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.system.info.quickActions.title') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <router-link
                        to="/admin/settings"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.settings') }}</span>
                    </router-link>
                    
                    <router-link
                        to="/admin/backups"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.backups') }}</span>
                    </router-link>
                    
                    <router-link
                        to="/admin/redis"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-red-500 dark:text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.redis') }}</span>
                    </router-link>
                    
                    <router-link
                        to="/admin/scheduled-tasks"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-blue-500 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.scheduledTasks') }}</span>
                    </router-link>
                    
                    <router-link
                        to="/admin/command-runner"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-yellow-500 dark:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.commandRunner') }}</span>
                    </router-link>

                    <router-link
                        to="/admin/system/notifications"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-purple-500 dark:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.notifications') }}</span>
                    </router-link>

                    <router-link
                        to="/admin/settings?tab=email"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-orange-500 dark:text-orange-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.emailSettings') }}</span>
                    </router-link>

                    <router-link
                        to="/admin/email-templates"
                        class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50 transition-colors duration-200"
                    >
                        <svg class="h-8 w-8 text-sky-500 dark:text-sky-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-medium text-foreground text-center">{{ t('features.system.info.quickActions.emailTemplates') }}</span>
                    </router-link>
                </div>
            </div>
        </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseSingleResponse } from '../../../utils/responseParser';

const { t } = useI18n();

const loading = ref(true);
const systemInfo = ref({});
const cacheStatus = ref('Active');

const systemHealth = computed(() => {
    if (!systemInfo.value) return 'healthy';
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

// Computed for display - handle pre-formatted strings from backend
const displayMemory = computed(() => {
    if (!systemInfo.value.memory_usage) return '-';
    // Backend now sends formatted string like "2.44 GB"
    if (typeof systemInfo.value.memory_usage === 'string') {
        return systemInfo.value.memory_usage;
    }
    return formatBytes(systemInfo.value.memory_usage);
});

const displayDisk = computed(() => {
    const usage = systemInfo.value.disk_usage;
    if (!usage) return '-';
    if (typeof usage === 'object') {
        // Backend sends: { used: "30.85 GB", total: "97.87 GB", percent: 31.52 }
        return `${usage.used} / ${usage.total} (${usage.percent || 0}%)`;
    }
    return usage;
});

const fetchSystemInfo = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/system/info');
        systemInfo.value = parseSingleResponse(response) || {};
        
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
    } finally {
        loading.value = false;
    }
};

const formatUptime = (seconds) => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};

const formatBytes = (bytes) => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchSystemInfo();
});
</script>
