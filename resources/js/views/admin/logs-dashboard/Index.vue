<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.logs_dashboard.title') }}</h1>
            <p class="text-sm text-muted-foreground mt-1">{{ t('features.logs_dashboard.description') }}</p>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Activity Logs Card -->
            <router-link 
                to="/admin/activity-logs" 
                class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 transition-colors group"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="font-semibold text-foreground mb-1">{{ t('features.logs_dashboard.cards.activity.title') }}</h3>
                <p class="text-sm text-muted-foreground mb-3">{{ t('features.logs_dashboard.cards.activity.description') }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-foreground font-medium">{{ stats.activity?.total || 0 }}</span>
                    <span class="text-muted-foreground">{{ t('features.logs_dashboard.cards.activity.total') }}</span>
                </div>
            </router-link>

            <!-- Security Logs Card -->
            <router-link 
                to="/admin/security" 
                class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 transition-colors group"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="font-semibold text-foreground mb-1">{{ t('features.logs_dashboard.cards.security.title') }}</h3>
                <p class="text-sm text-muted-foreground mb-3">{{ t('features.logs_dashboard.cards.security.description') }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-foreground font-medium">{{ stats.security?.total || 0 }}</span>
                    <span class="text-muted-foreground">{{ t('features.logs_dashboard.cards.security.total') }}</span>
                </div>
            </router-link>

            <!-- Login History Card -->
            <router-link 
                to="/admin/login-history" 
                class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 transition-colors group"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="font-semibold text-foreground mb-1">{{ t('features.logs_dashboard.cards.login.title') }}</h3>
                <p class="text-sm text-muted-foreground mb-3">{{ t('features.logs_dashboard.cards.login.description') }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-foreground font-medium">{{ stats.login?.total || 0 }}</span>
                    <span class="text-muted-foreground">{{ t('features.logs_dashboard.cards.login.total') }}</span>
                </div>
            </router-link>

            <!-- System Logs Card -->
            <router-link 
                to="/admin/logs" 
                class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 transition-colors group"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="font-semibold text-foreground mb-1">{{ t('features.logs_dashboard.cards.system.title') }}</h3>
                <p class="text-sm text-muted-foreground mb-3">{{ t('features.logs_dashboard.cards.system.description') }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-foreground font-medium">{{ stats.system?.files || 0 }}</span>
                    <span class="text-muted-foreground">{{ t('features.logs_dashboard.cards.system.files') }}</span>
                </div>
            </router-link>
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity Logs -->
            <div class="bg-card border border-border rounded-lg">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <h2 class="font-semibold text-foreground">{{ t('features.logs_dashboard.recent.activity') }}</h2>
                    <router-link to="/admin/activity-logs" class="text-sm text-primary hover:underline">
                        {{ t('features.logs_dashboard.viewAll') }}
                    </router-link>
                </div>
                <div v-if="loading" class="p-6 text-center text-muted-foreground">{{ t('features.logs_dashboard.loading') }}</div>
                <div v-else-if="recentActivity.length === 0" class="p-6 text-center text-muted-foreground">{{ t('features.logs_dashboard.empty') }}</div>
                <div v-else class="divide-y divide-border">
                    <div v-for="log in recentActivity" :key="log.id" class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span :class="getActionBadgeClass(log.action)" class="text-xs px-2 py-1 rounded-full">
                                {{ getActionLabel(log.action) }}
                            </span>
                            <span class="text-sm text-foreground">{{ log.description || log.model_type }}</span>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Security Events -->
            <div class="bg-card border border-border rounded-lg">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <h2 class="font-semibold text-foreground">{{ t('features.logs_dashboard.recent.security') }}</h2>
                    <router-link to="/admin/security" class="text-sm text-primary hover:underline">
                        {{ t('features.logs_dashboard.viewAll') }}
                    </router-link>
                </div>
                <div v-if="loading" class="p-6 text-center text-muted-foreground">{{ t('features.logs_dashboard.loading') }}</div>
                <div v-else-if="recentSecurity.length === 0" class="p-6 text-center text-muted-foreground">{{ t('features.logs_dashboard.empty') }}</div>
                <div v-else class="divide-y divide-border">
                    <div v-for="log in recentSecurity" :key="log.id" class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span :class="getSecurityBadgeClass(log.event_type)" class="text-xs px-2 py-1 rounded-full">
                                {{ getSecurityEventLabel(log.event_type) }}
                            </span>
                            <span class="text-sm text-foreground">{{ log.ip_address }}</span>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();

const loading = ref(true);
const stats = ref({});
const recentActivity = ref([]);
const recentSecurity = ref([]);

const fetchStats = async () => {
    try {
        // Fetch activity logs stats
        const activityStats = await api.get('/admin/cms/activity-logs/statistics').catch(() => ({ data: {} }));
        stats.value.activity = activityStats.data?.data || activityStats.data || {};

        // Fetch security stats
        const securityStats = await api.get('/admin/cms/security/statistics').catch(() => ({ data: {} }));
        stats.value.security = securityStats.data?.data || securityStats.data || {};

        // Fetch login history stats
        const loginStats = await api.get('/admin/cms/login-history/statistics').catch(() => ({ data: {} }));
        stats.value.login = loginStats.data?.data || loginStats.data || {};

        // Fetch system logs
        const systemLogs = await api.get('/admin/cms/system/logs').catch(() => ({ data: {} }));
        stats.value.system = { files: (systemLogs.data?.data || []).length };
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const fetchRecentLogs = async () => {
    try {
        // Fetch recent activity
        const activityResponse = await api.get('/admin/cms/activity-logs/recent?limit=10').catch(() => ({ data: {} }));
        recentActivity.value = activityResponse.data?.data || [];

        // Fetch recent security
        const securityResponse = await api.get('/admin/cms/security/logs?per_page=10').catch(() => ({ data: {} }));
        const secData = securityResponse.data?.data?.data || securityResponse.data?.data || [];
        recentSecurity.value = Array.isArray(secData) ? secData : [];
    } catch (error) {
        console.error('Failed to fetch recent logs:', error);
    }
};

const getActionBadgeClass = (action) => {
    const classes = {
        created: 'bg-green-500/20 text-green-500',
        updated: 'bg-blue-500/20 text-blue-500',
        deleted: 'bg-red-500/20 text-red-500',
        login: 'bg-indigo-500/20 text-indigo-500',
        logout: 'bg-gray-500/20 text-gray-500',
    };
    return classes[action] || 'bg-gray-500/20 text-gray-500';
};

const getSecurityBadgeClass = (eventType) => {
    if (eventType?.includes('failed') || eventType?.includes('blocked')) {
        return 'bg-red-500/20 text-red-500';
    }
    if (eventType?.includes('success')) {
        return 'bg-green-500/20 text-green-500';
    }
    return 'bg-yellow-500/20 text-yellow-500';
};

const getActionLabel = (action) => {
    if (!action) return '-';
    const key = `features.activityLogs.filters.types.${action}`;
    const translated = t(key);
    return translated !== key ? translated : action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getSecurityEventLabel = (eventType) => {
    if (!eventType) return '-';
    // Use security event types if possible, fallback to activity logs types
    let key = `features.security.logs.eventTypes.${eventType}`;
    let translated = t(key);
    
    if (translated === key) {
        key = `features.activityLogs.filters.types.${eventType}`;
        translated = t(key);
    }
    
    return translated !== key ? translated : eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    return date.toLocaleDateString();
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchRecentLogs()]);
    loading.value = false;
});
</script>
