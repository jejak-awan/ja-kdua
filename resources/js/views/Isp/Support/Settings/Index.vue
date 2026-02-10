<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('isp.settings.title') }}</h1>
                <p class="text-muted-foreground">{{ $t('isp.settings.subtitle') }}</p>
            </div>
        </div>

        <Tabs v-model="activeTab" class="w-full">
            <div class="mb-10 flex items-center justify-between">
                <TabsList class="bg-transparent p-0 h-auto gap-0">
                    <TabsTrigger 
                        value="whatsapp"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.whatsapp') }}
                    </TabsTrigger>
                    <TabsTrigger 
                        value="billing"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.billing') }}
                    </TabsTrigger>
                    <TabsTrigger 
                        value="network"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.network') }}
                    </TabsTrigger>
                    <TabsTrigger 
                        value="radius"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.radius') }}
                    </TabsTrigger>
                    <TabsTrigger 
                        value="branding"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.branding') }}
                    </TabsTrigger>
                    <TabsTrigger 
                        value="integrations"
                        class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
                    >
                        {{ $t('isp.settings.tabs.integrations') }}
                    </TabsTrigger>
                </TabsList>
            </div>

            <div class="">
                <!-- WhatsApp Tab -->
                <TabsContent value="whatsapp" class="space-y-4">
                    <WhatsAppSettings 
                        :settings="settings"
                        v-model:form-data="formData" 
                        :saving="saving"
                        @save="saveSettings"
                    />
                </TabsContent>

                <!-- Billing Tab -->
                <TabsContent value="billing" class="space-y-4">
                    <BillingSettings 
                        :settings="settings"
                        v-model:form-data="formData" 
                        :saving="saving"
                        @save="saveSettings"
                    />
                </TabsContent>

                <!-- Network Tab -->
                <TabsContent value="network" class="space-y-4">
                    <NetworkSettings 
                        :settings="settings"
                        v-model:form-data="formData" 
                        :saving="saving"
                        @save="saveSettings"
                    />
                </TabsContent>

                <!-- RADIUS Tab -->
                <TabsContent value="radius" class="space-y-4">
                    <RadiusSettings 
                        :settings="settings"
                        v-model:form-data="formData" 
                        :saving="saving"
                        @save="saveSettings"
                    />
                </TabsContent>

                <!-- Branding Tab -->
                <TabsContent value="branding" class="space-y-4">
                    <div class="bg-card border rounded-lg p-6">
                        <BrandingSettings 
                            :settings="settings"
                            v-model:form-data="formData" 
                            :errors="errors"
                        />
                    </div>
                </TabsContent>

                <!-- Integrations Tab -->
                <TabsContent value="integrations" class="space-y-4">
                    <div class="bg-card border rounded-lg p-6">
                        <IntegrationsSettings 
                            :settings="settings"
                            v-model:form-data="formData" 
                            :errors="errors"
                        />
                    </div>
                </TabsContent>
            </div>
        </Tabs>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui';
import WhatsAppSettings from './tabs/WhatsApp.vue'; 
import BillingSettings from './tabs/Billing.vue';
import NetworkSettings from './tabs/Network.vue';
import RadiusSettings from './tabs/Radius.vue';
import BrandingSettings from './tabs/Branding.vue';
import IntegrationsSettings from './tabs/Integrations.vue';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { parseResponse, ensureArray } from '@/utils/responseParser';
import type { SettingValue } from '@/types/settings';

interface Setting {
    key: string;
    value: SettingValue;
    group: string;
    type: string;
}

import { useRoute, useRouter } from 'vue-router';

// ...

const toast = useToast();
const route = useRoute();
const router = useRouter();
const validTabs = ['whatsapp', 'billing', 'network', 'radius', 'branding', 'integrations'];
const initialTab = validTabs.includes(route.query.tab as string) ? (route.query.tab as string) : 'whatsapp';
const activeTab = ref(initialTab);
const errors = ref<Record<string, string[]>>({});

// Watch active tab to update URL
watch(activeTab, (newTab) => {
    router.replace({ query: { ...route.query, tab: newTab } });
});

const loading = ref(false);
const saving = ref(false);
const settings = ref<Setting[]>([]);
const formData = ref<Record<string, SettingValue>>({});

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data) as Setting[];

        // Inject WhatsApp Defaults if missing (logic reused from General Settings)
        const ensureSetting = (key: string, value: SettingValue, group: string) => {
            if (!settings.value.find(s => s.key === key)) {
                settings.value.push({ key, value, group, type: 'string' });
            }
        };

        ensureSetting('whatsapp_driver', 'log', 'whatsapp');
        ensureSetting('whatsapp_api_url', '', 'whatsapp');
        ensureSetting('whatsapp_api_key', '', 'whatsapp');

        // Billing Defaults (Group: isp_billing)
        ensureSetting('billing_payment_type', 'prepaid', 'isp_billing');
        ensureSetting('billing_cycle_type', 'fixed', 'isp_billing');
        ensureSetting('billing_invoice_generation_date', 1, 'isp_billing');
        ensureSetting('billing_isolation_date', 20, 'isp_billing');
        ensureSetting('billing_isolation_time', '23:59', 'isp_billing');
        ensureSetting('billing_prorata', false, 'isp_billing');
        ensureSetting('billing_tax_enabled', false, 'isp_billing');
        ensureSetting('billing_tax_rate', 11, 'isp_billing');
        ensureSetting('billing_tax_ppn', 0.11, 'isp_billing');
        ensureSetting('billing_tax_bhp', 0.005, 'isp_billing');
        ensureSetting('billing_tax_uso', 0.0125, 'isp_billing');
        ensureSetting('billing_invoice_due_days', 7, 'isp_billing');
        ensureSetting('billing_suspend_behavior', 'isolation_date', 'isp_billing');

        // Network Defaults (Group: isp_network)
        ensureSetting('network_map_default_lat', -6.200000, 'isp_network');
        ensureSetting('network_map_default_lng', 106.816666, 'isp_network');
        ensureSetting('network_map_default_zoom', 12, 'isp_network');

        // RADIUS Defaults (Group: isp_radius)
        ensureSetting('radius_coa_port', 1700, 'isp_radius');
        ensureSetting('radius_default_speed', '10M/10M', 'isp_radius');

        // Branding Defaults
        ensureSetting('company_name', 'WIFIKU', 'isp_branding');
        ensureSetting('company_slogan', 'Solusi Internet Hebat', 'isp_branding');
        ensureSetting('company_address', 'BANDUNG', 'isp_branding');
        ensureSetting('company_whatsapp', '628000000000', 'isp_branding');
        ensureSetting('invoice_logo_size', '20', 'isp_branding');
        ensureSetting('invoice_kop', '', 'isp_branding');
        ensureSetting('invoice_watermark', '', 'isp_branding');
        ensureSetting('invoice_signature_name', 'Esa Nurjanah', 'isp_branding');
        ensureSetting('invoice_signature_image', '', 'isp_branding');
        ensureSetting('invoice_signature_title', 'Finance', 'isp_branding');
        ensureSetting('invoice_show_bank_account', true, 'isp_branding');
        ensureSetting('invoice_note_unpaid', '', 'isp_branding');
        ensureSetting('invoice_note_paid', '', 'isp_branding');
        ensureSetting('invoice_footer', 'SUPPORT BY : K2NET', 'isp_branding');
        ensureSetting('invoice_logo', '', 'isp_branding');

        // Integration Defaults
        ensureSetting('custom_domain', '', 'isp_integrations');
        ensureSetting('mutasibank_api_key', '', 'isp_integrations');
        ensureSetting('moota_api_key', '', 'isp_integrations');

        // Initialize form data
        formData.value = {};
        settings.value.forEach(s => {
            formData.value[s.key] = s.value as SettingValue;
        });

    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        // Prepare settings for bulk update
        const settingsToUpdate = Object.keys(formData.value)
            .filter(key => 
                key.startsWith('whatsapp_') || 
                key.startsWith('billing_') || 
                key.startsWith('network_') ||
                key.startsWith('radius_') ||
                [
                    'company_name', 'company_slogan', 'company_address', 'company_whatsapp',
                    'invoice_logo_size', 'invoice_kop', 'invoice_watermark',
                    'invoice_signature_name', 'invoice_signature_image', 'invoice_signature_title',
                    'invoice_show_bank_account', 'invoice_note_unpaid', 'invoice_note_paid', 'invoice_footer',
                    'invoice_logo',
                    'custom_domain', 'mutasibank_api_key', 'moota_api_key'
                ].includes(key)
            ) 
            .map(key => {
                let group = 'whatsapp';
                if (key.startsWith('billing_')) group = 'isp_billing';
                if (key.startsWith('network_')) group = 'isp_network';
                if (key.startsWith('radius_')) group = 'isp_radius';
                
                // Branding Group
                if ([
                    'company_name', 'company_slogan', 'company_address', 'company_whatsapp',
                    'invoice_logo_size', 'invoice_kop', 'invoice_watermark',
                    'invoice_signature_name', 'invoice_signature_image', 'invoice_signature_title',
                    'invoice_show_bank_account', 'invoice_note_unpaid', 'invoice_note_paid', 'invoice_footer',
                    'invoice_logo'
                ].includes(key)) {
                    group = 'isp_branding';
                }

                // Integrations Group
                if (['custom_domain', 'mutasibank_api_key', 'moota_api_key'].includes(key)) {
                    group = 'isp_integrations';
                }
                
                let type = 'string';
                if (key.includes('password') || key.includes('key')) type = 'password';
                if (key === 'invoice_logo_size') type = 'integer';
                if (key === 'invoice_show_bank_account') type = 'boolean';
                if (['invoice_kop', 'invoice_watermark', 'invoice_signature_image', 'invoice_logo'].includes(key)) type = 'image';

                return {
                    key,
                    value: formData.value[key],
                    group, 
                    type
                };
            });

        await api.post('/admin/ja/settings/bulk-update', {
            settings: settingsToUpdate,
        });

        toast.success.save();
        await fetchSettings(); // Refresh
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>
