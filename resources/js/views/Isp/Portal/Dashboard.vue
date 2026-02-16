<template>
    <div class="space-y-6 pb-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-foreground">{{ $t('isp.member.dashboard.welcome', { name: data.user?.name }) }}</h1>
                <p class="text-sm text-muted-foreground opacity-80">{{ $t('isp.member.dashboard.description') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-4 p-5 bg-white/5 border border-white/10 rounded-3xl shadow-lg backdrop-blur-md group/pulse">
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 rotate-3 group-hover/pulse:rotate-0 transition-transform duration-500">
                        <Activity class="w-8 h-8 text-primary animate-pulse" />
                    </div>
                    <div 
                        class="absolute -right-1 -top-1 w-5 h-5 rounded-full border-2 border-background animate-bounce"
                        :class="data.connection?.status === 'online' ? 'bg-success shadow-[0_0_15px_rgba(34,197,94,0.8)]' : 'bg-destructive shadow-[0_0_15px_rgba(239,68,68,0.8)]'"
                    ></div>
                </div>
                <div>
                    <h4 class="text-lg font-black text-foreground leading-tight">{{ data.connection?.status === 'online' ? $t('isp.member.dashboard.service_active') : $t('isp.member.dashboard.service_offline') }}</h4>
                    <p class="text-xs text-muted-foreground font-medium opacity-70 mt-0.5">{{ $t('isp.member.dashboard.pulse_desc') }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 p-5 bg-white/5 border border-white/10 rounded-3xl shadow-lg backdrop-blur-md group/speed transition-all hover:bg-white/10">
                <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 -rotate-3 group-hover/speed:rotate-0 transition-transform duration-500 text-indigo-400">
                    <Zap class="w-8 h-8" />
                </div>
                <div>
                    <p class="text-[10px] text-muted-foreground font-semibold tracking-tight opacity-60">{{ $t('isp.member.dashboard.bandwidth') }}</p>
                    <h4 class="text-xl font-black text-foreground">{{ data.device?.metadata?.speed || '...' }}</h4>
                </div>
            </div>
        </div>

        <div v-if="data.active_outages?.length" class="animate-in slide-in-from-top-4 duration-500">
            <Alert variant="destructive" class="rounded-2xl border-none shadow-lg bg-destructive/10 text-destructive flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <AlertTriangle class="w-5 h-5 animate-pulse" />
                    <div>
                        <AlertTitle class="font-bold">{{ $t('isp.member.dashboard.incident_title') }}</AlertTitle>
                        <AlertDescription class="text-xs opacity-90">
                            {{ $t('isp.member.dashboard.incident_desc', { title: data.active_outages[0].title }) }}
                        </AlertDescription>
                    </div>
                </div>
                <Button variant="ghost" size="sm" class="rounded-xl hover:bg-destructive/20" @click="$router.push('/status')">
                    {{ $t('isp.member.dashboard.view_status') }}
                    <ExternalLink class="w-3 h-3 ml-2" />
                </Button>
            </Alert>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Service & Usage -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Service Status Card (Glassmorphism) -->
                <Card class="p-8 border-white/10 bg-white/5 backdrop-blur-xl shadow-2xl overflow-hidden relative group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/20 rounded-full blur-3xl group-hover:bg-primary/30 transition-all duration-1000"></div>
                    <div class="flex justify-between items-start mb-8 relative z-10">
                        <div>
                            <p class="text-[10px] text-primary tracking-tight font-bold opacity-80">{{ $t('isp.member.dashboard.current_plan') }}</p>
                            <h2 class="text-3xl font-black text-foreground mt-1">{{ data.device?.metadata?.plan_name || $t('common.labels.loading') }}</h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative z-10 mb-6">
                        <div class="p-3 bg-muted/40 rounded-lg">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ $t('isp.member.dashboard.signal') }}</p>
                            <p class="text-sm font-mono">{{ data.connection?.signal_strength || 'N/A' }}</p>
                        </div>
                        <div class="p-3 bg-muted/40 rounded-lg">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ $t('isp.member.dashboard.latency') }}</p>
                            <p class="text-sm font-mono">{{ data.connection?.last_latency || 'N/A' }}</p>
                        </div>
                        <div class="p-3 bg-muted/40 rounded-lg">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ $t('isp.member.dashboard.ip_address') }}</p>
                            <p class="text-sm font-mono">{{ data.device?.metadata?.ip_address || '---' }}</p>
                        </div>
                        <div class="p-3 bg-muted/40 rounded-lg">
                            <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ $t('isp.member.dashboard.uptime') }}</p>
                            <p class="text-sm">{{ data.connection?.uptime || 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="h-[200px] w-full relative z-10">
                        <LineChart 
                            v-if="chartData.length > 0"
                            :data="chartData"
                            :label="$t('isp.usage.usage_mbps')"
                        />
                        <div v-else-if="!loading" class="h-full flex items-center justify-center text-muted-foreground italic text-xs">
                            {{ $t('common.messages.no_data') }}
                        </div>
                        <div v-else class="h-full flex items-center justify-center">
                            <LucideLoader class="w-6 h-6 animate-spin text-muted-foreground" />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end relative z-10">
                        <router-link :to="{ name: 'isp.member.usage' }" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            <Activity class="w-3 h-3" />
                            {{ $t('isp.member.dashboard.view_analytics') }}
                        </router-link>
                    </div>
                    
                    <Wifi class="absolute -right-4 -bottom-4 w-32 h-32 text-primary opacity-[0.03] group-hover:scale-110 transition-transform" />
                </Card>

                <!-- Registered Devices -->
                <Card class="p-6">
                    <h3 class="text-lg font-bold mb-4">{{ $t('isp.member.dashboard.my_devices') }}</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-border/40 rounded-xl hover:bg-muted/30 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <Router class="w-6 h-6" />
                                </div>
                                <div>
                                    <p class="font-bold text-sm">{{ data.device?.type || 'Standard Router' }}</p>
                                    <p class="text-xs text-muted-foreground font-mono">{{ data.device?.mac_address || 'N/A' }}</p>
                                </div>
                            </div>
                            <Badge variant="outline" class="text-[10px] font-bold">Standard</Badge>
                        </div>
                    </div>
                </Card>

                <!-- FUP Status Card -->
                <Card v-if="data.fup?.enabled" class="p-6 relative overflow-hidden group">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-2">
                            <Zap class="w-4 h-4 text-primary" />
                            <h3 class="text-sm font-bold tracking-tight opacity-60">{{ $t('isp.usage.quota_used') }}</h3>
                        </div>
                        <Badge :variant="data.fup.is_throttled ? 'destructive' : 'outline'" class="animate-in fade-in zoom-in duration-300">
                            {{ data.fup.is_throttled ? $t('isp.usage.throttled_notice') : $t('isp.member.dashboard.service_active') }}
                        </Badge>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-end mb-1">
                            <div>
                                <span class="text-3xl font-bold">{{ data.fup.usage_gb }}</span>
                                <span class="text-muted-foreground ml-1">GB</span>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-muted-foreground font-bold opacity-60">{{ $t('isp.usage.quota_remaining') }}</p>
                                <p class="text-sm font-mono font-bold">{{ Math.max(0, (data.fup.limit_gb || 0) - data.fup.usage_gb).toFixed(2) }} GB</p>
                            </div>
                        </div>

                        <!-- Custom Progress Bar -->
                        <div class="h-3 w-full bg-muted rounded-full overflow-hidden p-[2px]">
                            <div 
                                class="h-full rounded-full transition-all duration-1000 ease-out"
                                :class="data.fup.is_throttled ? 'bg-destructive' : 'bg-primary'"
                                :style="{ width: `${Math.min(100, (data.fup.usage_gb / (data.fup.limit_gb || 1)) * 100)}%` }" 
                            />
                        </div>

                        <div class="flex justify-between text-[10px] text-muted-foreground font-medium tracking-tight">
                            <span>0 GB</span>
                            <span>{{ data.fup.limit_gb || $t('isp.usage.unlimited') }} GB</span>
                        </div>

                        <div v-if="data.fup.is_throttled" class="p-3 bg-destructive/10 border border-destructive/20 rounded-xl flex items-start gap-3 mt-4 animate-in slide-in-from-bottom-2">
                            <AlertTriangle class="w-4 h-4 text-destructive shrink-0 mt-0.5" />
                            <p class="text-xs text-destructive leading-tight">
                                {{ $t('isp.usage.throttled_notice') }} <br />
                                <span class="font-bold opacity-80">Max Speed: {{ data.fup.throttled_speed }}</span>
                            </p>
                        </div>
                    </div>
                    <Activity class="absolute -left-4 -bottom-4 w-24 h-24 text-primary opacity-[0.02]" />
                </Card>
            </div>

            <!-- Right Column: Billing & Actions -->
            <div class="space-y-6">
                <!-- Billing Summary Card -->
                <Card class="p-6 border-t-4 border-t-primary">
                    <div class="flex items-center gap-3 mb-6">
                        <CreditCard class="w-5 h-5 text-primary" />
                        <h3 class="text-lg font-bold">{{ $t('isp.member.dashboard.billing_summary') }}</h3>
                    </div>
                    
                    <div class="mb-6">
                        <p class="text-xs text-muted-foreground font-bold opacity-70">{{ $t('isp.member.dashboard.unpaid_balance') }}</p>
                        <p class="text-3xl font-bold">Rp {{ formatNumber(data.unpaid_balance) }}</p>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div v-for="invoice in unpaidInvoices" :key="invoice.id" class="flex justify-between items-center text-sm p-3 bg-muted/40 rounded-lg border border-warning/20">
                            <div>
                                <p class="font-bold">{{ invoice.billing_period }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ $t('isp.member.dashboard.due', { date: formatDate(invoice.due_date) }) }}</p>
                            </div>
                            <Button size="sm" variant="outline" class="h-8 text-xs px-3" @click="handlePay(invoice)">{{ $t('isp.member.dashboard.pay_now') }}</Button>
                        </div>
                    </div>

                    <router-link :to="{ name: 'isp.member.invoices' }" class="block w-full text-center text-xs text-primary font-bold hover:underline">
                        {{ $t('isp.member.dashboard.view_history') }}
                    </router-link>
                </Card>

                <!-- Diagnostic Action Card -->
                <Card class="p-6 bg-gradient-to-br from-indigo-500 to-primary text-white relative overflow-hidden group border-none shadow-xl">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <Activity class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold">{{ t('isp.member.dashboard.troubleshoot_title') || 'Troubleshoot' }}</h3>
                        </div>
                        <p class="text-sm opacity-90 mb-6">{{ t('isp.member.dashboard.troubleshoot_desc') || 'Facing slow connection or no internet? Run a quick connectivity scan.' }}</p>
                        <Button 
                            variant="outline" 
                            class="w-full bg-white/10 border-white/20 hover:bg-white/20 text-white"
                            @click="$router.push({ name: 'isp.member.diagnostics' })"
                        >
                            <RefreshCw class="w-4 h-4 mr-2" />
                            {{ t('isp.member.dashboard.troubleshoot_now') || 'Run Diagnostics' }}
                        </Button>
                    </div>
                    <ShieldCheck class="absolute -right-4 -bottom-4 w-24 h-24 opacity-10 group-hover:scale-110 transition-transform" />
                </Card>

                <!-- Upgrade Request Card -->
                <Card class="p-6 bg-primary text-primary-foreground relative overflow-hidden group">
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold mb-2">{{ $t('isp.member.dashboard.need_speed') }}</h3>
                        <p class="text-sm opacity-90 mb-6">{{ $t('isp.member.dashboard.upgrade_desc') }}</p>
                        <Button 
                            variant="outline" 
                            class="w-full bg-white/10 border-white/20 hover:bg-white/20 text-white"
                            :loading="requesting"
                            @click="handleUpgradeRequest"
                        >
                            <ArrowUp class="w-4 h-4 mr-2" />
                            {{ $t('isp.member.dashboard.request_upgrade') }}
                        </Button>
                    </div>
                    <Zap class="absolute -right-4 -bottom-4 w-24 h-24 opacity-10 group-hover:rotate-12 transition-transform" />
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { logger } from '@/utils/logger';
import { Card, Badge, Button, Alert, AlertTitle, AlertDescription } from '@/components/ui';
import Wifi from 'lucide-vue-next/dist/esm/icons/wifi.js';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import ExternalLink from 'lucide-vue-next/dist/esm/icons/external-link.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import ArrowUp from 'lucide-vue-next/dist/esm/icons/arrow-up.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import LucideLoader from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import LineChart from '@/components/charts/LineChart.vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { ensureArray } from '@/utils/responseParser';
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';
import type { IspMemberDashboard, IspInvoice, IspTrafficData } from '@/types/isp';

const { t } = useI18n();
const toast = useToast();
const data = ref<IspMemberDashboard>({
    unpaid_balance: 0
});
const loading = ref(true);
const requesting = ref(false);

const chartData = computed(() => {
    const history = ensureArray<IspTrafficData>(data.value.traffic_history);
    if (history.length === 0) return [];
    
    return history.map(h => ({
        period: String(h.time || h.date || ''),
        visits: Number(h.in) + Number(h.out)
    }));
});

const unpaidInvoices = computed(() => {
    return data.value.invoices?.filter((i: IspInvoice) => i.status === 'unpaid') || [];
});

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('id-ID').format(num || 0);
};

const formatDate = (date: string) => {
    return dayjs(date).format('DD MMM YYYY');
};

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/member/dashboard');
        data.value = response.data?.data || response.data || {};
    } catch (error: unknown) {
        if (error instanceof Error && error.name !== 'CanceledError') {
            logger.error('Failed to load portal data:', error);
            toast.error.default(t('common.messages.error_load'));
        }
    } finally {
        loading.value = false;
    }
};

const handlePay = async (invoice: IspInvoice) => {
    try {
        await api.post(`/admin/janet/isp/billing/invoices/${invoice.id}/pay`);
        toast.success.action(t('isp.member.dashboard.success_pay'));
        fetchData();
    } catch (error: unknown) {
        toast.error.action(error as Error);
    }
};

const handleUpgradeRequest = async () => {
    requesting.value = true;
    try {
        await api.post('/admin/janet/isp/member/service-request', {
            type: 'Upgrade',
            details: {
                current_plan: data.value.device?.metadata?.plan_name,
                requested_at: new Date().toISOString()
            }
        });
        toast.success.action(t('isp.member.dashboard.success_upgrade'));
    } catch (error: unknown) {
        toast.error.action(error as Error);
    } finally {
        requesting.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
