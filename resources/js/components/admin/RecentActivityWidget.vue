<template>
    <div class="bg-card rounded-lg shadow-sm border border-border h-full flex flex-col">
        <div class="px-6 py-4 border-b border-border flex justify-between items-center">
            <h3 class="text-lg font-semibold text-foreground flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $t('features.dashboard.widgets.recentActivity.title') }}
            </h3>
            <span class="text-xs text-muted-foreground dark:text-gray-400 flex items-center">
                <span class="w-2 h-2 rounded-full bg-green-500 mr-1 animate-pulse"></span>
                {{ $t('features.dashboard.widgets.recentActivity.live') }}
            </span>
        </div>

        <div class="flex-1 overflow-y-auto p-0">
            <div v-if="loading && activities.length === 0" class="p-6 text-center text-muted-foreground">
                {{ $t('features.dashboard.widgets.recentActivity.loading') }}
            </div>
            
            <div v-else-if="activities.length === 0" class="p-6 text-center text-muted-foreground">
                {{ $t('features.dashboard.widgets.recentActivity.empty') }}
            </div>

            <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <div v-for="activity in activities" :key="activity.id" class="p-4 hover:bg-muted dark:hover:bg-gray-750 transition-colors">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <span 
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold ring-2 ring-white dark:ring-gray-800"
                                :class="getUserAvatarClass(activity)"
                            >
                                {{ getUserInitials(activity.user?.name) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-foreground truncate">
                                {{ activity.user?.name || 'System' }}
                            </p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400">
                                <span 
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium mr-1"
                                    :class="getActionBadgeClass(activity.action || activity.type)"
                                >
                                    {{ activity.action || activity.type || 'unknown' }}
                                </span>
                                {{ activity.description }}
                            </p>
                             <p class="text-xs text-gray-400 mt-1 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatTime(activity.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-3 border-t border-border bg-muted dark:bg-gray-800/50 rounded-b-lg">
            <router-link :to="{ name: 'activity-logs' }" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium flex items-center justify-center">
                {{ $t('features.dashboard.widgets.recentActivity.viewAll') }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';

const { t } = useI18n();

const activities = ref([]);
const loading = ref(false);
let refreshInterval = null;

const fetchActivities = async () => {
    // Only set loading on first load
    if (activities.value.length === 0) {
        loading.value = true;
    }
    
    try {
        // Fetch last 10 activities
        const response = await api.get('/admin/cms/activity-logs', { params: { per_page: 10 } });
        const data = response.data?.data || response.data || [];
        activities.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch recent activities:', error);
    } finally {
        loading.value = false;
    }
};

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000); // seconds

    if (diff < 60) return t('features.dashboard.widgets.recentActivity.time.justNow');
    if (diff < 3600) return t('features.dashboard.widgets.recentActivity.time.ago', { time: `${Math.floor(diff / 60)}m` });
    if (diff < 86400) return t('features.dashboard.widgets.recentActivity.time.ago', { time: `${Math.floor(diff / 3600)}h` });
    
    return d.toLocaleDateString();
};

const getUserInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(n => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const getUserAvatarClass = (activity) => {
    // Generate consistent color based on user ID or name char code
    const id = activity.user_id || 0;
    const colors = [
        'bg-indigo-100 text-indigo-800',
        'bg-green-500/20 text-green-400',
        'bg-blue-500/20 text-blue-400',
        'bg-yellow-500/20 text-yellow-400',
        'bg-purple-100 text-purple-800',
        'bg-pink-100 text-pink-800',
        'bg-red-500/20 text-red-400',
    ];
    return colors[id % colors.length];
};

const getActionBadgeClass = (action) => {
    const a = (action || '').toLowerCase();
    if (a.includes('create')) return 'bg-green-500/20 text-green-400 dark:bg-green-900/30 dark:text-green-400';
    if (a.includes('update')) return 'bg-blue-500/20 text-blue-400 dark:bg-blue-900/30 dark:text-blue-400';
    if (a.includes('delete')) return 'bg-red-500/20 text-red-400 dark:bg-red-900/30 dark:text-red-400';
    if (a.includes('login') || a.includes('logout')) return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
    return 'bg-secondary text-secondary-foreground dark:bg-gray-700 dark:text-gray-400';
};

onMounted(() => {
    fetchActivities();
    refreshInterval = setInterval(fetchActivities, 30000); // 30s refresh
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
