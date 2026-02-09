<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('isp.admin.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.admin.subtitle') }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card class="cursor-pointer hover:border-primary transition-colors" @click="navigateTo('isp-settings')">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <Settings class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h3 class="font-medium">{{ t('isp.admin.settings') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ t('isp.admin.settings_desc') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="cursor-pointer hover:border-primary transition-colors" @click="navigateTo('isp-payment-gateway')">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <CreditCard class="w-6 h-6 text-green-600" />
                        </div>
                        <div>
                            <h3 class="font-medium">{{ t('isp.admin.payment_gateway') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ t('isp.admin.payment_gateway_desc') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="cursor-pointer hover:border-primary transition-colors" @click="navigateTo('isp-router')">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <Router class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="font-medium">{{ t('isp.admin.mikrotik') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ t('isp.admin.mikrotik_desc') }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- System Status -->
        <Card>
            <CardHeader>
                <CardTitle>{{ t('isp.admin.system_status') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center gap-3 p-4 border rounded-lg">
                        <div :class="['w-3 h-3 rounded-full', status.router ? 'bg-green-500' : 'bg-red-500']"></div>
                        <div>
                            <p class="text-sm font-medium">{{ t('isp.admin.status.router') }}</p>
                            <p class="text-xs text-muted-foreground">{{ status.router ? t('common.status.connected') : t('common.status.disconnected') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 border rounded-lg">
                        <div :class="['w-3 h-3 rounded-full', status.radius ? 'bg-green-500' : 'bg-yellow-500']"></div>
                        <div>
                            <p class="text-sm font-medium">{{ t('isp.admin.status.radius') }}</p>
                            <p class="text-xs text-muted-foreground">{{ status.radius ? t('common.status.active') : t('common.status.not_configured') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 border rounded-lg">
                        <div :class="['w-3 h-3 rounded-full', status.whatsapp ? 'bg-green-500' : 'bg-yellow-500']"></div>
                        <div>
                            <p class="text-sm font-medium">{{ t('isp.admin.status.whatsapp') }}</p>
                            <p class="text-xs text-muted-foreground">{{ status.whatsapp ? t('common.status.connected') : t('common.status.not_configured') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 border rounded-lg">
                        <div :class="['w-3 h-3 rounded-full', status.payment ? 'bg-green-500' : 'bg-yellow-500']"></div>
                        <div>
                            <p class="text-sm font-medium">{{ t('isp.admin.status.payment') }}</p>
                            <p class="text-xs text-muted-foreground">{{ status.payment ? t('common.status.active') : t('common.status.not_configured') }}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Recent Activity -->
        <Card>
            <CardHeader>
                <CardTitle>{{ t('isp.admin.recent_activity') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div v-for="(activity, index) in recentActivity" :key="index" class="flex items-start gap-4 pb-4 border-b last:border-0">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center', getActivityColor(activity.type)]">
                            <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm">{{ activity.message }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</p>
                        </div>
                    </div>
                    <p v-if="recentActivity.length === 0" class="text-center text-muted-foreground py-4">
                        {{ t('isp.admin.no_activity') }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import CircleAlert from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import CircleCheck from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import {
    Card, CardContent, CardHeader, CardTitle
} from '@/components/ui';
import api from '@/services/api';

const { t } = useI18n();
const router = useRouter();

interface Activity {
    type: 'info' | 'success' | 'warning' | 'error';
    message: string;
    created_at: string;
}

const status = reactive({
    router: false,
    radius: false,
    whatsapp: false,
    payment: false
});

const recentActivity = ref<Activity[]>([]);

const navigateTo = (name: string) => {
    router.push({ name });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const getActivityColor = (type: string) => {
    const colors: Record<string, string> = {
        info: 'bg-blue-100 text-blue-600',
        success: 'bg-green-100 text-green-600',
        warning: 'bg-yellow-100 text-yellow-600',
        error: 'bg-red-100 text-red-600'
    };
    return colors[type] || colors.info;
};

const getActivityIcon = (type: string) => {
    const icons: Record<string, unknown> = {
        info: Bell,
        success: CircleCheck,
        warning: CircleAlert,
        error: CircleAlert
    };
    return icons[type] || Bell;
};

const loadStatus = async () => {
    try {
        const response = await api.get('/admin/isp/admin/status');
        if (response.data.success) {
            Object.assign(status, response.data.data.status || {});
            recentActivity.value = response.data.data.activity || [];
        }
    } catch (error) {
        console.error('Failed to load status:', error);
    }
};

onMounted(() => {
    loadStatus();
});
</script>
