<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ t('isp.member.diagnostics.title') }}</h1>
                <p class="text-sm text-muted-foreground">{{ t('isp.member.diagnostics.description') }}</p>
            </div>
            <Button @click="runDiagnostics" :disabled="loading" class="rounded-xl gap-2 shadow-lg shadow-primary/20">
                <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
                {{ loading ? t('isp.member.diagnostics.running') : t('isp.member.diagnostics.run_now') }}
            </Button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card class="p-6 md:col-span-3">
                <div class="space-y-8">
                    <div v-for="(step, index) in steps" :key="index" class="relative pl-8 animate-in slide-in-from-left-4" :style="{ animationDelay: `${index * 150}ms` }">
                        <!-- Connector Line -->
                        <div v-if="index < steps.length - 1" class="absolute left-3 top-8 bottom-0 w-px bg-border/40"></div>
                        
                        <!-- Step Icon -->
                        <div
class="absolute left-0 top-0 w-6 h-6 rounded-full flex items-center justify-center z-10"
                             :class="step.status === 'success' ? 'bg-success/20 text-success' : step.status === 'pending' ? 'bg-muted text-muted-foreground' : 'bg-destructive/20 text-destructive'"
>
                            <Check v-if="step.status === 'success'" class="w-3.5 h-3.5" />
                            <LucideLoader v-else-if="step.status === 'pending'" class="w-3.5 h-3.5 animate-spin" />
                            <X v-else class="w-3.5 h-3.5" />
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-sm">{{ step.name }}</h3>
                                <Badge v-if="step.latency" variant="secondary" class="text-[10px] py-0 h-4">{{ step.latency }}</Badge>
                            </div>
                            <p class="text-xs text-muted-foreground">{{ step.message }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="overallStatus === 'healthy'" class="mt-12 p-6 rounded-2xl bg-success/5 border border-success/20 animate-in zoom-in-95 duration-500">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-success/20 rounded-full text-success">
                            <ShieldCheck class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="font-bold text-success">{{ t('isp.member.diagnostics.healthy_title') }}</h4>
                            <p class="text-sm text-success/80">{{ t('isp.member.diagnostics.healthy_desc') }}</p>
                        </div>
                    </div>
                </div>
            </Card>

            <div class="space-y-4">
                <Card class="p-4 bg-muted/30 border border-border/40 shadow-none rounded-xl">
                    <h4 class="text-xs font-bold tracking-tight text-muted-foreground mb-4">{{ t('isp.member.diagnostics.quick_actions') }}</h4>
                    <div class="space-y-2">
                        <Button variant="outline" class="w-full justify-start rounded-xl text-xs h-9 bg-card border-border/40" @click="resetConnection">
                            <Wifi class="w-3.5 h-3.5 mr-2 text-primary" />
                            {{ t('isp.member.diagnostics.reset_wifi') }}
                        </Button>
                        <Button variant="outline" class="w-full justify-start rounded-xl text-xs h-9 bg-card border-border/40" @click="$router.push({ name: 'isp.member.support' })">
                            <MessageSquare class="w-3.5 h-3.5 mr-2 text-primary" />
                            {{ t('isp.member.diagnostics.contact_support') }}
                        </Button>
                    </div>
                </Card>

                <Alert class="rounded-2xl border-none bg-primary/5">
                    <Info class="w-4 h-4 text-primary" />
                    <AlertTitle class="text-[10px] font-bold tracking-tight text-primary mb-1">{{ t('common.did_you_know') }}</AlertTitle>
                    <AlertDescription class="text-xs text-muted-foreground">
                        {{ t('isp.member.diagnostics.did_you_know') }}
                    </AlertDescription>
                </Alert>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Card, Button, Badge, Alert, AlertTitle, AlertDescription } from '@/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Wifi from 'lucide-vue-next/dist/esm/icons/wifi.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import LucideLoader from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const overallStatus = ref<string | null>(null);

const steps = ref([
    { name: 'Local Network', status: 'pending', message: 'Checking local interface status...', latency: '' },
    { name: 'ISP Session', status: 'pending', message: 'Authenticating with ISP router...', latency: '' },
    { name: 'Interface Link', status: 'pending', message: 'Verifying physical link...', latency: '' },
    { name: 'Global Signal', status: 'pending', message: 'Checking OLT signal levels...', latency: '' },
    { name: 'Internet Access', status: 'pending', message: 'Testing global route...', latency: '' }
]);

const runDiagnostics = async () => {
    loading.value = true;
    overallStatus.value = null;
    
    // Reset steps
    steps.value.forEach(s => {
        s.status = 'pending';
        s.latency = '';
    });

    try {
        const response = await api.get('/admin/janet/isp/member/diagnostics');
        const results = response.data.data;

        // Map backend report to frontend steps
        // Backend key index: 0(local), 1(session), 2(interface), 3(signal), 4(internet)
        const stepMap = [
            { stepIdx: 0, backendKey: 'local' },
            { stepIdx: 1, backendKey: 'session' },
            { stepIdx: 2, backendKey: 'interface' },
            { stepIdx: 3, backendKey: 'signal' },
            { stepIdx: 4, backendKey: 'internet' }
        ];

        for (const map of stepMap) {
            await new Promise(resolve => setTimeout(resolve, 600)); // Smooth transition
            const report = results.report[map.backendKey];
            const step = steps.value[map.stepIdx];
            
            step.status = report.status;
            step.message = report.message;
            if (report.latency && typeof report.latency === 'number') {
                step.latency = `${report.latency}ms`;
            } else if (report.status === 'success' && map.backendKey === 'interface' && results.report.interface.details) {
                // Special display for interface speed
                const details = results.report.interface.details;
                step.latency = `${details.speed} ${details.duplex}`;
            }
        }

        overallStatus.value = results.overall_status === 'healthy' ? 'healthy' : 'issue';
        
        if (overallStatus.value === 'healthy') {
            toast.success.default(t('isp.member.diagnostics.complete'));
        } else {
            toast.warning(t('common.messages.toast.warning'), t('isp.member.diagnostics.issue_detected'));
        }
    } catch (_e) {
        toast.error.default(t('isp.member.diagnostics.error'));
        steps.value.forEach(s => { if (s.status === 'pending') s.status = 'error'; });
    } finally {
        loading.value = false;
    }
};

const resetConnection = async () => {
    try {
        toast.info(t('isp.member.diagnostics.resetting'), t('isp.member.diagnostics.resetting_desc'));
        await api.post('/admin/janet/isp/member/reset-connection');
        toast.success.default(t('isp.member.diagnostics.reset_success'));
        // Re-run diagnostics after reset with a delay to allow session to stabilize
        setTimeout(() => runDiagnostics(), 15000);
    } catch (_e) {
        toast.error.default(t('isp.member.diagnostics.reset_error'));
    }
};

onMounted(() => {
    runDiagnostics();
});
</script>
