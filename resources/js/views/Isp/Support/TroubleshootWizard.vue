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
                            <Card class="p-4 border-l-4" :class="selectedCustomer.balance <= 0 ? 'border-l-success' : 'border-l-destructive'">
                                <div class="text-sm text-muted-foreground">Current Balance</div>
                                <div class="text-2xl font-bold">Rp {{ formatNumber(selectedCustomer.balance) }}</div>
                            </Card>
                            <Card class="p-4 border-l-4" :class="selectedCustomer.status === 'active' ? 'border-l-success' : 'border-l-warning'">
                                <div class="text-sm text-muted-foreground">Account Status</div>
                                <div class="text-xl font-bold uppercase">{{ selectedCustomer.status }}</div>
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
                            <div class="flex justify-between items-center bg-white p-2 rounded border">
                                <span>Connection Status:</span>
                                <Badge :variant="routerStatus === 'connected' ? 'default' : 'destructive'" :class="routerStatus === 'connected' ? 'bg-green-500 hover:bg-green-600' : ''">
                                    {{ routerStatus }}
                                </Badge>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <Button variant="outline" @click="prevStep">Back</Button>
                            <Button @click="checkRouter" :disabled="checkingRouter">
                                <RefreshCw v-if="checkingRouter" class="w-4 h-4 mr-2 animate-spin" />
                                {{ checkingRouter ? 'Checking...' : 'Re-check & Next' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Step 3: Signal Strength -->
                    <div v-if="currentStep === 3" class="space-y-4 animate-in slide-in-from-right-4">
                        <h3 class="text-lg font-bold flex items-center gap-2">
                             <Signal class="w-5 h-5 text-primary" />
                             {{ t('isp.support.wizard.steps.signal.title') }}
                        </h3>
                         <div class="flex flex-col items-center justify-center p-8 bg-muted/10 rounded-xl border border-dashed">
                            <div class="text-4xl font-bold mb-2" :class="getSignalColor(signalStrength)">
                                {{ signalStrength }} dBm
                            </div>
                            <p class="text-sm text-muted-foreground">Optical Power Level</p>
                        </div>
                        <div class="flex justify-between">
                            <Button variant="outline" @click="prevStep">Back</Button>
                            <Button @click="nextStep">Next: Ping Test</Button>
                        </div>
                    </div>

                    <!-- Step 4: Ping Test -->
                    <div v-if="currentStep === 4" class="space-y-4 animate-in slide-in-from-right-4">
                        <h3 class="text-lg font-bold flex items-center gap-2">
                             <Activity class="w-5 h-5 text-primary" />
                             {{ t('isp.support.wizard.steps.ping.title') }}
                        </h3>
                        <div class="bg-black/90 text-green-400 p-4 rounded-xl font-mono text-xs h-40 overflow-y-auto">
                            <div v-for="(log, i) in pingLogs" :key="i">
                                > {{ log }}
                            </div>
                        </div>
                        <div class="flex justify-between">
                             <Button variant="outline" @click="prevStep">Back</Button>
                             <Button @click="finishWizard" class="bg-green-600 hover:bg-green-700 text-white">Finish Diagnosis</Button>
                        </div>
                    </div>

                </div>
            </div>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, Button, Input, Label, Badge } from '@/components/ui';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Router from 'lucide-vue-next/dist/esm/icons/router.js';
import Signal from 'lucide-vue-next/dist/esm/icons/signal.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';

const { t } = useI18n();
const search = ref('');
const loading = ref(false);
const selectedCustomer = ref<any>(null);
const currentStep = ref(1);
const totalSteps = 4;

// Mock Data
const routerStatus = ref('connected');
const checkingRouter = ref(false);
const signalStrength = ref(-18.5);
const pingLogs = ref<string[]>([]);

const formatNumber = (num: number) => num.toLocaleString('id-ID');

const searchCustomer = () => {
    loading.value = true;
    setTimeout(() => {
        selectedCustomer.value = {
            id: 1,
            name: 'John Doe',
            balance: 0,
            status: 'active',
            router_name: 'Mikrotik-Home-1',
            ip_address: '192.168.88.10'
        };
        loading.value = false;
    }, 1000);
};

const nextStep = () => {
    if (currentStep.value < totalSteps) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const checkRouter = () => {
    checkingRouter.value = true;
    setTimeout(() => {
        checkingRouter.value = false;
        nextStep();
    }, 1500);
};

const getSignalColor = (val: number) => {
    if (val > -15) return 'text-success';
    if (val > -25) return 'text-primary';
    return 'text-destructive';
};

const finishWizard = () => {
    selectedCustomer.value = null;
    currentStep.value = 1;
    pingLogs.value = [];
};

// Simulate Ping on moount step 4
import { watch } from 'vue';
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
