<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('isp.billing.gateway.title') }}</h1>
                <p class="text-muted-foreground">{{ t('isp.billing.gateway.subtitle') }}</p>
            </div>
        </div>

        <!-- Gateway Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Midtrans -->
            <Card :class="{ 'border-primary': activeGateway === 'midtrans' }">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <CreditCard class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <CardTitle>Midtrans</CardTitle>
                                <CardDescription>{{ t('isp.billing.gateway.midtrans_desc') }}</CardDescription>
                            </div>
                        </div>
                        <Switch
                            :checked="midtrans.enabled"
                            @update:checked="toggleGateway('midtrans', $event)"
                        />
                    </div>
                </CardHeader>
                <CardContent v-if="midtrans.enabled">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.merchant_id') }}</Label>
                            <Input v-model="midtrans.merchant_id" type="text" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.server_key') }}</Label>
                            <Input v-model="midtrans.server_key" type="password" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.client_key') }}</Label>
                            <Input v-model="midtrans.client_key" type="password" />
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox id="midtrans-sandbox" :checked="midtrans.sandbox" @update:checked="midtrans.sandbox = $event" />
                            <Label for="midtrans-sandbox">{{ t('isp.billing.gateway.sandbox_mode') }}</Label>
                        </div>
                    </div>
                </CardContent>
                <CardFooter v-if="midtrans.enabled">
                    <Button @click="saveGateway('midtrans')" :disabled="saving">
                        {{ t('common.actions.save') }}
                    </Button>
                </CardFooter>
            </Card>

            <!-- Xendit -->
            <Card :class="{ 'border-primary': activeGateway === 'xendit' }">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                <Wallet class="w-6 h-6 text-purple-600" />
                            </div>
                            <div>
                                <CardTitle>Xendit</CardTitle>
                                <CardDescription>{{ t('isp.billing.gateway.xendit_desc') }}</CardDescription>
                            </div>
                        </div>
                        <Switch
                            :checked="xendit.enabled"
                            @update:checked="toggleGateway('xendit', $event)"
                        />
                    </div>
                </CardHeader>
                <CardContent v-if="xendit.enabled">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.api_key') }}</Label>
                            <Input v-model="xendit.api_key" type="password" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.callback_token') }}</Label>
                            <Input v-model="xendit.callback_token" type="password" />
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox id="xendit-sandbox" :checked="xendit.sandbox" @update:checked="xendit.sandbox = $event" />
                            <Label for="xendit-sandbox">{{ t('isp.billing.gateway.sandbox_mode') }}</Label>
                        </div>
                    </div>
                </CardContent>
                <CardFooter v-if="xendit.enabled">
                    <Button @click="saveGateway('xendit')" :disabled="saving">
                        {{ t('common.actions.save') }}
                    </Button>
                </CardFooter>
            </Card>

            <!-- Manual Payment -->
            <Card :class="{ 'border-primary': activeGateway === 'manual' }">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <Banknote class="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <CardTitle>{{ t('isp.billing.gateway.manual') }}</CardTitle>
                                <CardDescription>{{ t('isp.billing.gateway.manual_desc') }}</CardDescription>
                            </div>
                        </div>
                        <Switch
                            :checked="manual.enabled"
                            @update:checked="toggleGateway('manual', $event)"
                        />
                    </div>
                </CardHeader>
                <CardContent v-if="manual.enabled">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.bank_name') }}</Label>
                            <Input v-model="manual.bank_name" type="text" placeholder="BCA, Mandiri, BNI..." />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.account_number') }}</Label>
                            <Input v-model="manual.account_number" type="text" />
                        </div>
                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.gateway.account_name') }}</Label>
                            <Input v-model="manual.account_name" type="text" />
                        </div>
                    </div>
                </CardContent>
                <CardFooter v-if="manual.enabled">
                    <Button @click="saveGateway('manual')" :disabled="saving">
                        {{ t('common.actions.save') }}
                    </Button>
                </CardFooter>
            </Card>
        </div>

        <!-- Webhook Info -->
        <Card>
            <CardHeader>
                <CardTitle>{{ t('isp.billing.gateway.webhook_config') }}</CardTitle>
                <CardDescription>{{ t('isp.billing.gateway.webhook_desc') }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Midtrans Notification URL</Label>
                        <div class="flex gap-2">
                            <Input :value="webhookUrls.midtrans" readonly class="font-mono text-sm" />
                            <Button variant="outline" size="icon" @click="copyToClipboard(webhookUrls.midtrans)">
                                <Copy class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Xendit Callback URL</Label>
                        <div class="flex gap-2">
                            <Input :value="webhookUrls.xendit" readonly class="font-mono text-sm" />
                            <Button variant="outline" size="icon" @click="copyToClipboard(webhookUrls.xendit)">
                                <Copy class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import Wallet from 'lucide-vue-next/dist/esm/icons/wallet.js';
import Banknote from 'lucide-vue-next/dist/esm/icons/banknote.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import {
    Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter,
    Button, Input, Label, Switch, Checkbox
} from '@/components/ui';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const toastService = useToast();

interface MidtransConfig {
    enabled: boolean;
    merchant_id: string;
    server_key: string;
    client_key: string;
    sandbox: boolean;
}

interface XenditConfig {
    enabled: boolean;
    api_key: string;
    callback_token: string;
    sandbox: boolean;
}

interface ManualConfig {
    enabled: boolean;
    bank_name: string;
    account_number: string;
    account_name: string;
}

const saving = ref(false);
const activeGateway = ref<string | null>(null);

const midtrans = reactive<MidtransConfig>({
    enabled: false,
    merchant_id: '',
    server_key: '',
    client_key: '',
    sandbox: true
});

const xendit = reactive<XenditConfig>({
    enabled: false,
    api_key: '',
    callback_token: '',
    sandbox: true
});

const manual = reactive<ManualConfig>({
    enabled: true,
    bank_name: '',
    account_number: '',
    account_name: ''
});

const webhookUrls = computed(() => ({
    midtrans: `${window.location.origin}/api/webhooks/midtrans`,
    xendit: `${window.location.origin}/api/webhooks/xendit`
}));

const loadGateways = async () => {
    try {
        const response = await api.get('/admin/janet/isp/payment-gateways');
        if (response.data.success && response.data.data) {
            if (response.data.data.midtrans) Object.assign(midtrans, response.data.data.midtrans);
            if (response.data.data.xendit) Object.assign(xendit, response.data.data.xendit);
            if (response.data.data.manual) Object.assign(manual, response.data.data.manual);
        }
    } catch (error) {
        console.error('Failed to load gateways:', error);
    }
};

const toggleGateway = (gateway: string, enabled: boolean) => {
    if (gateway === 'midtrans') midtrans.enabled = enabled;
    else if (gateway === 'xendit') xendit.enabled = enabled;
    else if (gateway === 'manual') manual.enabled = enabled;
    
    if (enabled) activeGateway.value = gateway;
};

const saveGateway = async (gateway: string) => {
    saving.value = true;
    try {
        const data = gateway === 'midtrans' ? midtrans : gateway === 'xendit' ? xendit : manual;
        await api.post(`/admin/janet/isp/payment-gateways/${gateway}`, data);
        toastService.service.success(t('common.messages.saved'));
    } catch (_error) {
        toastService.service.error(t('common.messages.error'));
    } finally {
        saving.value = false;
    }
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toastService.service.success(t('common.messages.copied'));
};

onMounted(() => {
    loadGateways();
});
</script>
