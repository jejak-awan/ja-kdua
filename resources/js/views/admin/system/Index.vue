<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">System Information</h1>
        </div>

        <!-- System Health -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">System Health</p>
                        <p class="text-2xl font-semibold mt-1" :class="systemHealth === 'healthy' ? 'text-green-600' : systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'">
                            {{ systemHealth === 'healthy' ? 'Healthy' : systemHealth === 'warning' ? 'Warning' : 'Critical' }}
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
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Cache Status</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ cacheStatus || 'Active' }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Uptime</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ formatUptime(systemInfo.uptime) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">System Information</h2>
                <button
                    @click="clearCache"
                    :disabled="clearingCache"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm"
                >
                    {{ clearingCache ? 'Clearing...' : 'Clear Cache' }}
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Application</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">PHP Version</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.php_version || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Laravel Version</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.laravel_version || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Environment</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.environment || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Debug Mode</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.debug_mode ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Server</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Server Software</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.server_software || '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Memory Usage</dt>
                            <dd class="text-sm text-gray-900">{{ formatMemory(systemInfo.memory_usage) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Disk Usage</dt>
                            <dd class="text-sm text-gray-900">{{ formatDiskUsage(systemInfo.disk_usage) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Database</dt>
                            <dd class="text-sm text-gray-900">{{ systemInfo.database || '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div v-if="statistics" class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">System Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_contents || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total Contents</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_users || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total Users</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_media || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total Media</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-semibold text-gray-900">{{ statistics.total_visits || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total Visits</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';

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
        systemInfo.value = response.data.data || response.data;
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/admin/cms/system/statistics');
            statistics.value = statsResponse.data;
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
            cacheStatus.value = cacheResponse.data.status || 'Active';
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
        alert('Cache cleared successfully');
        await fetchSystemInfo();
    } catch (error) {
        console.error('Failed to clear cache:', error);
        alert('Failed to clear cache');
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

