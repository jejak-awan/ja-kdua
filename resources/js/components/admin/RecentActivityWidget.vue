<template>
    <Card class="flex flex-col h-full overflow-hidden border-none shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-4 space-y-0">
            <div class="space-y-1">
                <CardTitle class="text-xl font-bold flex items-center gap-2">
                    <History class="w-5 h-5 text-indigo-500" />
                    {{ $t('features.dashboard.widgets.recentActivity.title') }}
                </CardTitle>
                <CardDescription v-if="activities.length > 0">
                    {{ $t('features.dashboard.widgets.recentActivity.description') || 'Latest system events' }}
                </CardDescription>
            </div>
            <div class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-500">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-medium uppercase tracking-wider">{{ $t('features.dashboard.widgets.recentActivity.live') }}</span>
            </div>
        </CardHeader>

        <CardContent class="flex-1 overflow-y-auto p-0 border-t border-border/40">
            <div v-if="loading && activities.length === 0" class="flex flex-col items-center justify-center p-12 text-muted-foreground space-y-3">
                <Loader2 class="w-8 h-8 animate-spin opacity-50" />
                <p>{{ $t('features.dashboard.widgets.recentActivity.loading') }}</p>
            </div>
            
            <div v-else-if="activities.length === 0" class="flex flex-col items-center justify-center p-12 text-muted-foreground space-y-3">
                <div class="p-4 rounded-full bg-muted/30">
                    <ZapOff class="w-8 h-8 opacity-20" />
                </div>
                <p>{{ $t('features.dashboard.widgets.recentActivity.empty') }}</p>
            </div>

            <div v-else class="divide-y divide-border/40">
                <div v-for="activity in activities" :key="activity.id" class="p-4 hover:bg-muted/30 transition-colors group">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <Avatar class="h-10 w-10 ring-2 ring-background group-hover:ring-muted transition-all">
                                <AvatarFallback :class="getUserAvatarClass(activity)" class="font-bold text-xs">
                                    {{ getUserInitials(activity.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </div>
                        <div class="flex-1 min-w-0 space-y-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ activity.user?.name || 'System' }}
                                </p>
                                <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                                    <Clock class="w-3 h-3" />
                                    {{ formatTime(activity.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground flex items-center flex-wrap gap-2">
                                <Badge 
                                    variant="outline"
                                    class="h-5 px-1.5 text-[10px] font-bold uppercase tracking-wider border-none"
                                    :class="getActionBadgeClass(activity.action || activity.type)"
                                >
                                    {{ activity.action || activity.type || 'unknown' }}
                                </Badge>
                                <span class="line-clamp-1">{{ activity.description }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
        
        <div class="p-3 bg-muted/10 border-t border-border/40">
            <Button variant="ghost" size="sm" class="w-full text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/5 group" asChild>
                <router-link :to="{ name: 'activity-logs' }" class="flex items-center justify-center">
                    {{ $t('features.dashboard.widgets.recentActivity.viewAll') }}
                    <ArrowRight class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" />
                </router-link>
            </Button>
        </div>
    </Card>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardDescription from '@/components/ui/card-description.vue';
import CardContent from '@/components/ui/card-content.vue';
import Button from '@/components/ui/button.vue';
import Badge from '@/components/ui/badge.vue';
import Avatar from '@/components/ui/avatar.vue';
import AvatarFallback from '@/components/ui/avatar-fallback.vue';
import { History, Clock, ArrowRight, Loader2, ZapOff } from 'lucide-vue-next';

const { t } = useI18n();

const activities = ref([]);
const loading = ref(false);
let refreshInterval = null;

const fetchActivities = async () => {
    if (window.__isSessionTerminated) {
        if (refreshInterval) clearInterval(refreshInterval);
        return;
    }

    if (activities.value.length === 0) {
        loading.value = true;
    }
    
    try {
        const response = await api.get('/admin/cms/activity-logs', { params: { per_page: 6 } });
        const { data } = parseResponse(response);
        activities.value = data || [];
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
    const diff = Math.floor((now - d) / 1000);

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
    const id = activity.user_id || 0;
    const colors = [
        'bg-indigo-500/10 text-indigo-500',
        'bg-emerald-500/10 text-emerald-500',
        'bg-blue-500/10 text-blue-500',
        'bg-amber-500/10 text-amber-500',
        'bg-purple-500/10 text-purple-500',
        'bg-rose-500/10 text-rose-500',
        'bg-cyan-500/10 text-cyan-500',
    ];
    return colors[id % colors.length];
};

const getActionBadgeClass = (action) => {
    const a = (action || '').toLowerCase();
    if (a.includes('create')) return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    if (a.includes('update')) return 'bg-blue-500/10 text-blue-600 dark:text-blue-400';
    if (a.includes('delete')) return 'bg-rose-500/10 text-rose-600 dark:text-rose-400';
    if (a.includes('login') || a.includes('logout')) return 'bg-purple-500/10 text-purple-600 dark:text-purple-400';
    return 'bg-muted text-muted-foreground';
};

onMounted(() => {
    fetchActivities();
    refreshInterval = setInterval(fetchActivities, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
