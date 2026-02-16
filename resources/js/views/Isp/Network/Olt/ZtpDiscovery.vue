<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">{{ $t('isp.infra.olt.discovery.title') }}</h2>
                <p class="text-muted-foreground">{{ $t('isp.infra.olt.discovery.subtitle') }}</p>
            </div>
            <Button @click="scanAll" :disabled="scanning" class="rounded-xl">
                <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': scanning }" />
                {{ $t('isp.infra.olt.discovery.scan') }}
            </Button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <RefreshCw class="w-10 h-10 animate-spin text-primary opacity-20" />
        </div>

        <div v-else-if="oltsWithPending.length === 0" class="flex flex-col items-center justify-center py-20 bg-muted/30 rounded-xl border-2 border-dashed border-border/40 shadow-sm">
            <Box class="w-16 h-16 text-muted-foreground mb-4 opacity-20" />
            <h3 class="text-xl font-semibold">{{ $t('isp.infra.olt.discovery.no_equipment') }}</h3>
            <p class="text-muted-foreground">{{ $t('isp.infra.olt.discovery.no_equipment_desc') }}</p>
        </div>

        <div v-else class="grid gap-6">
            <Card v-for="olt in oltsWithPending" :key="olt.id" class="rounded-xl overflow-hidden shadow-sm border border-border/40 bg-card">
                <div class="bg-primary/5 p-4 border-b border-border/40 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary/10 rounded-xl text-primary font-bold text-xs">
                            {{ olt.type }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-none">{{ olt.name }}</h3>
                            <span class="text-xs text-muted-foreground font-mono">{{ olt.ip_address }}</span>
                        </div>
                    </div>
                    <Badge variant="warning" class="rounded-xl">{{ olt.discovery_pending?.length || 0 }} {{ $t('isp.infra.olt.discovery.discovered_badge') }}</Badge>
                </div>
                
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ $t('isp.infra.olt.fields.sn') }}</TableHead>
                            <TableHead>{{ $t('isp.infra.olt.fields.interface') }}</TableHead>
                            <TableHead class="text-right">{{ $t('isp.infra.nodes.fields.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="onu in olt.discovery_pending" :key="onu.sn">
                            <TableCell class="font-mono font-medium">{{ onu.sn }}</TableCell>
                            <TableCell>{{ onu.interface }}</TableCell>
                            <TableCell class="text-right">
                                <Button size="sm" variant="default" @click="provision(olt, onu)" class="rounded-xl">
                                    {{ $t('isp.infra.olt.discovery.provision') }}
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>

        <!-- Provisioning Placeholder Modal -->
        <Dialog v-model:open="showProvisionModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Provision Request</DialogTitle>
                    <DialogDescription>
                        You are about to provision ONU <b>{{ selectedOnu?.sn }}</b> on <b>{{ selectedOlt?.name }}</b>.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <Alert variant="default" class="bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/30 dark:text-blue-200 dark:border-blue-900">
                        <Info class="w-4 h-4" />
                        <AlertTitle>Manual Redirection</AlertTitle>
                        <AlertDescription>
                            ZTP Auto-registration interface is currently being integrated with CRM Customer Profiles. Redirecting to Customer Registration...
                        </AlertDescription>
                    </Alert>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showProvisionModal = false" class="rounded-xl">{{ $t('common.actions.cancel') }}</Button>
                    <Button @click="goToRegistration" class="rounded-xl">Go to Registration</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
    Button, Card, Badge, Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Alert, AlertTitle, AlertDescription
} from '@/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Box from 'lucide-vue-next/dist/esm/icons/box.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useRouter } from 'vue-router';
import type { Olt, OltDiscoveryResult } from '@/types/isp';
import { useI18n } from 'vue-i18n';

const toast = useToast();
const router = useRouter();
const { t } = useI18n();

const loading = ref(false);
const scanning = ref(false);
const oltsWithPending = ref<Olt[]>([]);
const showProvisionModal = ref(false);
const selectedOlt = ref<Olt | null>(null);
const selectedOnu = ref<OltDiscoveryResult | null>(null);

const fetchDiscovery = async () => {
    loading.value = true;
    try {
        const res = await api.get('/admin/janet/isp/olts', { 
            params: { with_discovery: 1 } 
        });
        const allOlts = res.data.data as Olt[];
        oltsWithPending.value = allOlts.filter(o => o.discovery_pending && o.discovery_pending.length > 0);
    } catch (_e) {
        toast.error.action('Failed to fetch discovery results');
    } finally {
        loading.value = false;
    }
};

const scanAll = async () => {
    scanning.value = true;
    try {
        await api.get('/admin/janet/isp/olts/discover');
        toast.success.action(t('features.isp.olt.messages.discovery_started'));
        setTimeout(fetchDiscovery, 3000);
    } catch (_e) {
        toast.error.action('Failed to start scan');
    } finally {
        scanning.value = false;
    }
};

const provision = (olt: Olt, onu: OltDiscoveryResult) => {
    selectedOlt.value = olt;
    selectedOnu.value = onu;
    showProvisionModal.value = true;
};

const goToRegistration = () => {
    router.push({ 
        name: 'isp-subscription-customers', 
        query: { 
            provision_sn: selectedOnu.value?.sn,
            provision_olt: selectedOlt.value?.id,
            provision_port: selectedOnu.value?.interface
        } 
    });
};

onMounted(fetchDiscovery);
</script>
