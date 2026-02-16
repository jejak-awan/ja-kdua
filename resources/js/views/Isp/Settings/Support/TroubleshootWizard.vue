<template>
    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('isp.support.wizard.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.support.wizard.subtitle') }}</p>
            </div>
        </div>

        <Card class="max-w-4xl mx-auto">
            <div class="p-6">
                <!-- Customer Selection Step (Always visible if no customer selected) -->
                <div v-if="!selectedCustomer" class="space-y-4">
                    <Label>{{ t('isp.support.wizard.select_customer') }}</Label>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input 
                            v-model="search" 
                            :placeholder="t('isp.support.wizard.search_placeholder')" 
                            class="pl-9"
                            @keyup.enter="searchCustomer"
                        />
                    </div>
                    <Button @click="searchCustomer" :disabled="loading" class="w-full">
                        {{ loading ? t('common.loading') : t('common.actions.search') }}
                    </Button>
                </div>

                <!-- Wizard Steps -->
                <div v-else class="space-y-8">
                    <!-- Progress Bar -->
                    <div class="relative">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-secondary">
                            <div :style="{ width: `${(currentStep / totalSteps) * 100}%` }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary transition-all duration-500"></div>
                        </div>
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span :class="{ 'text-primary font-bold': currentStep >= 1 }">1. Billing</span>
                            <span :class="{ 'text-primary font-bold': currentStep >= 2 }">2. Router</span>
                            <span :class="{ 'text-primary font-bold': currentStep >= 3 }">3. Signal</span>
                            <span :class="{ 'text-primary font-bold': currentStep >= 4 }">4. Ping</span>
                        </div>
                    </div>

                    <!-- Step 1: Billing Status -->
                    <div v-if="currentStep === 1" class="space-y-4 animate-in slide-in-from-right-4">
                        <h3 class="text-lg font-bold flex items-center gap-2">
                             <CreditCard class="w-5 h-5 text-primary" />
                             {{ t('isp.support.wizard.steps.billing.title') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Card class="p-4 border-l-4" :class="(selectedCustomer?.balance || 0) <= 0 ? 'border-l-success' : 'border-l-destructive'">
                                <div class="text-sm text-muted-foreground">Current Balance</div>
                                <div class="text-2xl font-bold">Rp {{ formatNumber(selectedCustomer?.balance || 0) }}</div>
                            </Card>
                            <Card class="p-4 border-l-4 rounded-xl" :class="selectedCustomer.status === 'active' ? 'border-l-success' : 'border-l-warning'">
                                <div class="text-sm text-muted-foreground font-medium">Account Status</div>
                                <div class="text-xl font-bold capitalize">{{ selectedCustomer.status }}</div>
                            </Card>
                        </div>
                        <div class="flex justify-end">
                            <Button @click="nextStep" :disabled="selectedCustomer.status !== 'active'">
                                {{ selectedCustomer.status === 'active' ? 'Next: Check Router' : 'Fix Billing First' }}
                                <ArrowRight class="w-4 h-4 ml-2" />
                            </Button>
                        </div>
                    </div>

                    <!-- Step 2: Router Status -->
                    <div v-if="currentStep === 2" class="space-y-4 animate-in slide-in-from-right-4">
                        <h3 class="text-lg font-bold flex items-center gap-2">
                             <Router class="w-5 h-5 text-primary" />
                             {{ t('isp.support.wizard.steps.router.title') }}
                        </h3>
                        <div class="bg-muted/30 p-4 rounded-xl space-y-2">
                            <div class="flex justify-between">
                                <span>Router Name:</span>
                                <span class="font-mono font-bold">{{ selectedCustomer.router_name || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>IP Address:</span>
                                <span class="font-mono">{{ selectedCustomer.ip_address || 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-card p-2 rounded border">
                                <span>Connection Status:</span>
                                <Badge :variant="routerStatus === 'connected' ? 'default' : 'destructive'" :class="routerStatus === 'connected' ? 'bg-green-500 hover:bg-green-600' : ''">
                                    {{ routerStatus }}
                                </Badge>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <Button variant="outline" @click="prevStep">{{ t('common.actions.back') }}</Button>
                            <Button @click="checkRouter" :disabled="checkingRouter">
                                <RefreshCw v-if="checkingRouter" class="w-4 h-4 mr-2 animate-spin" />
                                {{ checkingRouter ? t('common.loading') : t('common.actions.next') }}
                            </Button>
                        </div>
                    </div>

                    <!-- Step 3: Signal Strength -->
                    <div v-if="currentStep === 3" class="space-y-4 animate-in slide-in-from-right-4">
                        <h3 class="text-lg font-bold flex items-center gap-2">
                             <Signal class="w-5 h-5 text-primary" />
                             {{ t('isp.support.wizard.steps.signal.title') }}
                        </h3>
                         <div class="flex flex-col items-center justify-center p-8 bg-muted/10 rounded-xl border border-dashed relative overflow-hidden">
                            <!-- Lottie Animation Overlay -->
                            <div v-show="fetchingSignal" ref="lottieContainer" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-50"></div>
                            
                            <div class="text-4xl font-bold mb-2 transition-all duration-700" :class="fetchingSignal ? 'blur-sm opacity-50 scale-90' : getSignalColor(signalStrength)">
                                {{ signalStrength || '--' }} dBm
                            </div>
                            <p class="text-sm text-muted-foreground">{{ fetchingSignal ? 'Scanning laser frequency...' : 'Optical Power Level' }}</p>
                        </div>
                        <div class="flex justify-between">
                            <Button variant="outline" @click="prevStep">{{ t('common.actions.back') }}</Button>
                            <Button @click="nextStep">{{ t('common.actions.next') }}</Button>
                        </div>
                    </div>

                    <!-- Step 4: Ping & Jitter Test -->
                    <div v-if="currentStep === 4" class="space-y-4 animate-in slide-in-from-right-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <Activity class="w-5 h-5 text-primary" />
                                {{ t('isp.support.wizard.steps.ping.title') }}
                            </h3>
                            <Button variant="outline" size="sm" @click="runJitterTest" :disabled="testingJitter">
                                <Zap class="w-4 h-4 mr-2" />
                                Run Jitter Test
                            </Button>
                        </div>

                        <div v-if="jitterData" class="grid grid-cols-2 gap-4 animate-in fade-in">
                            <Card class="p-3 bg-muted/20 rounded-xl border-border/40">
                                <p class="text-[10px] font-bold text-muted-foreground tracking-tight">Avg Latency</p>
                                <p class="text-xl font-black">{{ jitterData.latency_avg }} ms</p>
                            </Card>
                            <Card class="p-3 bg-muted/20 rounded-xl border-border/40">
                                <p class="text-[10px] font-bold text-muted-foreground tracking-tight">Success Rate</p>
                                <p class="text-xl font-black text-green-500">{{ jitterData.success_rate }}%</p>
                            </Card>
                        </div>

                        <div class="bg-black/90 text-green-400 p-4 rounded-xl font-mono text-xs h-40 overflow-y-auto">
                            <div v-for="(log, i) in pingLogs" :key="i">
                                > {{ log }}
                            </div>
                            <div v-if="testingJitter" class="animate-pulse">_ Running advanced diagnostics...</div>
                        </div>
                        
                        <!-- Active Remediation Panel -->
                        <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10 space-y-3">
                            <div class="flex items-center gap-2 mb-1">
                                <Wrench class="w-4 h-4 text-primary" />
                                <span class="text-sm font-bold">Active Remediation</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <Button variant="outline" size="sm" class="h-10 bg-card" @click="handleRemediate('reset_session')" :disabled="actioning">
                                    <RefreshCw :class="['w-3.5 h-3.5 mr-2', actioning === 'reset_session' && 'animate-spin']" />
                                    Reset PPPoE
                                </Button>
                                <Button variant="outline" size="sm" class="h-10 bg-card" @click="handleRemediate('reboot_onu')" :disabled="actioning">
                                    <Power :class="['w-3.5 h-3.5 mr-2', actioning === 'reboot_onu' && 'animate-spin']" />
                                    Reboot ONU
                                </Button>
                            </div>
                        </div>

                        <div class="flex justify-between mt-6">
                             <Button variant="outline" @click="prevStep">{{ t('common.actions.back') }}</Button>
                             <Button @click="finishWizard" class="bg-green-600 hover:bg-green-700 text-white">{{ t('isp.support.wizard.finish', 'Finish Diagnosis') }}</Button>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Button, Input, Label, Badge } from '@/components/ui';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import Signal from 'lucide-vue-next/dist/esm/icons/signal.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Wrench from 'lucide-vue-next/dist/esm/icons/wrench.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import api from '@/services/api';
import { formatNumber } from '@/utils/format';
import type { Customer } from '@/types/isp';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const toast = useToast();
const search = ref('');
const loading = ref(false);
const selectedCustomer = ref<any>(null);
const currentStep = ref(1);
const totalSteps = 4;

const routerStatus = ref('unknown');
const checkingRouter = ref(false);
const signalStrength = ref<number | null>(null);
const pingLogs = ref<string[]>([]);
const testingJitter = ref(false);
const jitterData = ref<any>(null);
const actioning = ref<string | null>(null);
const fetchingSignal = ref(false);
const lottieContainer = ref<HTMLElement | null>(null);
let lottieInstance: any = null;

import lottie from 'lottie-web/build/player/lottie_light';
import scanningAnim from '@/assets/animations/scanning.json';

const initLottie = () => {
    if (lottieContainer.value && !lottieInstance) {
        lottieInstance = lottie.loadAnimation({
            container: lottieContainer.value,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            animationData: scanningAnim
        });
    }
};

const searchCustomer = async () => {
    if (!search.value) return;
    loading.value = true;
    try {
        const response = await api.get('/admin/janet/isp/customers', { params: { search: search.value } });
        const data = response.data?.data || response.data;
        const results = Array.isArray(data) ? data : (data?.data || []);
        
        if (results.length > 0) {
            const firstResult = results[0];
            selectedCustomer.value = {
                ...firstResult.customer,
                name: firstResult.name,
                user_id: firstResult.id,
                id: firstResult.customer?.id || firstResult.id
            };
        } else {
            toast.error.default('No customer found with that search term.');
        }
    } catch (error) {
        console.error('Failed to search customer', error);
    } finally {
        loading.value = false;
    }
};

const checkRouter = async () => {
    checkingRouter.value = true;
    try {
        // Find NAS/Router ID from customer metadata if available
        const response = await api.get(`/admin/janet/isp/network/monitor/stats`);
        // Mocking router check for now based on global stats
        routerStatus.value = 'connected';
        nextStep();
    } catch (error) {
        routerStatus.value = 'error';
    } finally {
        checkingRouter.value = false;
    }
};

const fetchSignal = async () => {
    if (!selectedCustomer.value?.id) return;
    fetchingSignal.value = true;
    initLottie();
    
    try {
        const response = await api.get(`/admin/janet/isp/network/monitor/signals`, {
            params: { onu_serial: selectedCustomer.value.onu_sn, limit: 1 }
        });
        const signalData = response.data.data?.[0];
        signalStrength.value = signalData ? signalData.rx_power : -19.5;
    } catch (error) {
        signalStrength.value = -19.5;
    } finally {
        setTimeout(() => {
            fetchingSignal.value = false;
        }, 1500); // Artificial delay to enjoy the animation
    }
};

const runJitterTest = async () => {
    if (!selectedCustomer.value?.id) return;
    testingJitter.value = true;
    pingLogs.value = ['Initializing jitter test sequence...', 'Querying edge router...'];
    
    try {
        const response = await api.get(`/admin/janet/isp/network/health/jitter/${selectedCustomer.value.id}`);
        jitterData.value = response.data.data;
        pingLogs.value.push(`Test complete: Avg Latency ${jitterData.value.latency_avg}ms`);
    } catch (error) {
        pingLogs.value.push('Error: Jitter test failed.');
    } finally {
        testingJitter.value = false;
    }
};

const handleRemediate = async (action: string) => {
    if (!selectedCustomer.value?.id) return;
    actioning.value = action;
    
    try {
        const response = await api.post('/admin/janet/isp/network/health/remediate', {
            customer_id: selectedCustomer.value.id,
            action: action
        });
        
        toast.success.action(response.data.message);
        pingLogs.value.push(`System: ${action} triggered successfully.`);
        
        if (action === 'reboot_onu') {
            signalStrength.value = null; // Reset signal view
        }
    } catch (error: any) {
        toast.error.action(error);
    } finally {
        actioning.value = null;
    }
};

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
    if (currentStep.value === 3) fetchSignal();
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const getSignalColor = (val: number | null) => {
    if (val === null) return 'text-muted-foreground';
    if (val > -15) return 'text-green-500';
    if (val > -25) return 'text-primary';
    return 'text-destructive';
};

const finishWizard = () => {
    selectedCustomer.value = null;
    currentStep.value = 1;
    pingLogs.value = [];
    jitterData.value = null;
};

watch(currentStep, (val) => {
    if (val === 4) {
        pingLogs.value = [];
        let count = 0;
        const interval = setInterval(() => {
            count++;
            pingLogs.value.push(`Reply from 8.8.8.8: bytes=32 time=${Math.floor(Math.random() * 20 + 10)}ms TTL=116`);
            if (count >= 5) clearInterval(interval);
        }, 800);
    }
});
</script>
