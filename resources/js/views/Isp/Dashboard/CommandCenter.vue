<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ t('isp.admin.title') }}</h2>
                <p class="text-muted-foreground">{{ t('isp.admin.subtitle') }}</p>
            </div>
        </div>

        <!-- BI Command Center (Glassmorphism) -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- MRR Card -->
            <Card class="border-border/40 bg-white/5 backdrop-blur-md shadow-xl rounded-xl overflow-hidden group">
                <CardContent class="p-6 relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/20 rounded-full blur-3xl group-hover:bg-primary/30 transition-all duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-primary opacity-70">{{ t('isp.admin.mrr') }}</p>
                            <p class="text-3xl font-black text-foreground">Rp {{ formatNumber(biStats.mrr) }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-success font-bold mt-2">
                                <TrendingUp class="w-3.5 h-3.5" />
                                <span>{{ t('isp.admin.mrr_growth') }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-primary/20 text-primary border border-primary/20 shadow-inner">
                            <DollarSign class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Churn Rate Card -->
            <Card class="border-border/40 bg-white/5 backdrop-blur-md shadow-xl rounded-xl overflow-hidden group">
                <CardContent class="p-6 relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-destructive/20 rounded-full blur-3xl group-hover:bg-destructive/30 transition-all duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-destructive opacity-70">{{ t('isp.admin.churn_rate') }}</p>
                            <p class="text-3xl font-black text-foreground">2.4%</p>
                            <div class="flex items-center gap-1.5 text-xs text-success font-bold mt-2">
                                <TrendingUp class="w-3.5 h-3.5" />
                                <span>{{ t('isp.admin.churn_optimization') }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-destructive/20 text-destructive border border-destructive/20 shadow-inner">
                            <ArrowDown class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Growth Forecast Card -->
            <Card class="border-white/10 bg-white/5 backdrop-blur-md shadow-xl overflow-hidden group">
                <CardContent class="p-6 relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-success/20 rounded-full blur-3xl group-hover:bg-success/30 transition-all duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-success opacity-70">{{ t('isp.admin.growth_forecast') }}</p>
                            <p class="text-3xl font-black text-foreground">+12.5%</p>
                            <div class="flex items-center gap-1.5 text-xs text-primary font-bold mt-2">
                                <Target class="w-3.5 h-3.5" />
                                <span>{{ t('isp.admin.growth_target') }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-success/20 text-success border border-success/20 shadow-inner">
                            <Zap class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- CLV Card -->
            <Card class="border-white/10 bg-white/5 backdrop-blur-md shadow-xl overflow-hidden group">
                <CardContent class="p-6 relative">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/20 rounded-full blur-3xl group-hover:bg-blue-500/30 transition-all duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-blue-500 opacity-70">{{ t('isp.admin.clv') }}</p>
                            <p class="text-3xl font-black text-foreground">Rp {{ formatNumber(biStats.clv) }}</p>
                            <div class="flex items-center gap-1.5 text-xs text-blue-500 font-bold mt-2">
                                <ShieldCheck class="w-3.5 h-3.5" />
                                <span>{{ t('isp.admin.clv_score') }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-2xl bg-blue-500/20 text-blue-500 border border-blue-500/20 shadow-inner">
                            <Award class="w-6 h-6" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Financial Projection Chart -->
        <Card class="border-border/40 bg-white/5 backdrop-blur-md shadow-xl rounded-xl overflow-hidden">
            <CardHeader class="flex flex-row items-center justify-between pb-2 border-b border-white/5">
                <div class="space-y-1">
                    <CardTitle class="text-lg flex items-center gap-2 font-bold text-primary">
                        <LineChartIcon class="w-5 h-5" />
                        {{ t('isp.admin.financial_projection') }}
                    </CardTitle>
                    <CardDescription class="text-xs text-muted-foreground opacity-70">{{ t('isp.admin.projection_desc') }}</CardDescription>
                </div>
            </CardHeader>
            <CardContent class="p-6">
                <div class="h-[300px] mt-2 group">
                    <LineChart
                        v-if="projectionData.length > 0"
                        :data="projectionData"
                        label="Projected Revenue"
                        class="transition-all duration-700 group-hover:scale-[1.01]"
                    />
                    <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-2 opacity-30">
                        <Loader2 class="h-10 w-10 animate-spin" />
                        <p class="text-xs font-bold opacity-70">{{ t('isp.admin.synthesizing_data') }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

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

            <Card class="cursor-pointer hover:border-primary transition-colors" @click="navigateTo('isp-server')">
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import CircleAlert from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import CircleCheck from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import DollarSign from 'lucide-vue-next/dist/esm/icons/dollar-sign.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import ArrowDown from 'lucide-vue-next/dist/esm/icons/trending-down.js';
import Target from 'lucide-vue-next/dist/esm/icons/target.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Award from 'lucide-vue-next/dist/esm/icons/award.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import LineChartIcon from 'lucide-vue-next/dist/esm/icons/chart-line.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import {
    Card, CardContent, CardHeader, CardTitle, CardDescription, Button
} from '@/components/ui';
import api from '@/services/api';
import LineChart from '@/components/charts/LineChart.vue';
import { parseSingleResponse } from '@/utils/responseParser';
import { logger } from '@/utils/logger';

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

const biStats = ref({
    mrr: 0,
    clv: 0,
    churn_risk: 0
});

const projectionData = ref<any[]>([]);

const recentActivity = ref<Activity[]>([]);

const navigateTo = (name: string) => {
    router.push({ name });
};

const formatNumber = (val: number) => {
    return new Intl.NumberFormat('id-ID').format(val || 0);
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
        const response = await api.get('/admin/janet/isp/admin/status');
        if (response.data.success) {
            Object.assign(status, response.data.data.status || {});
            recentActivity.value = response.data.data.activity || [];
        }
    } catch (error) {
        console.error('Failed to load status:', error);
    }
};

const fetchBiData = async () => {
    try {
        const [biRes, projRes] = await Promise.all([
            api.get('/billing/analytics/bi'),
            api.get('/billing/analytics/projections')
        ]);
        
        const bi = parseSingleResponse<any>(biRes);
        const proj = parseSingleResponse<any>(projRes);
        
        if (bi) {
            biStats.value = {
                mrr: bi.mrr,
                clv: bi.clv,
                churn_risk: bi.churn_risk_count
            };
        }
        
        if (proj && proj.projections) {
            projectionData.value = Object.entries(proj.projections).map(([period, visits]) => ({
                period,
                visits: Math.round(Number(visits))
            }));
        }
    } catch (e) {
        logger.error('Failed to fetch BI data', e);
    }
};

onMounted(() => {
    loadStatus();
    fetchBiData();
});
</script>

<style scoped>
:root {
    --glass-bg: rgba(255, 255, 255, 0.05);
    --glass-border: rgba(255, 255, 255, 0.1);
}

.dark {
    --glass-bg: rgba(0, 0, 0, 0.2);
    --glass-border: rgba(255, 255, 255, 0.05);
}

.backdrop-blur-md {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

.shadow-xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-8px) rotate(1deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes pulse-glow {
    0% { box-shadow: 0 0 0 0 rgba(var(--primary), 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(var(--primary), 0); }
    100% { box-shadow: 0 0 0 0 rgba(var(--primary), 0); }
}

.group:hover .relative.z-10 {
    animation: float 4s ease-in-out infinite;
}

.group:hover .p-3.rounded-2xl {
    transform: scale(1.1);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Glassmorphism Refinement */
.bg-white\/5 {
    background-color: var(--glass-bg);
}

.border-white\/10 {
    border-color: var(--glass-border);
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

/* Chart Hover Effect */
.group-hover\:scale-\[1\.01\] {
    filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.1));
}
</style>
