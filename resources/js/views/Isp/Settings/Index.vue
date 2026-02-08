<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">ISP Settings</h1>
                <p class="text-muted-foreground">Manage configuration for ISP-related features and integrations.</p>
            </div>
        </div>

        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="w-full justify-start border-b rounded-none h-auto p-0 bg-transparent">
                <TabsTrigger 
                    value="whatsapp"
                    class="data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none px-4 py-2"
                >
                    WhatsApp & Notifications
                </TabsTrigger>
                <TabsTrigger 
                    value="billing"
                    class="data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none px-4 py-2"
                >
                    Billing Configuration
                </TabsTrigger>
                <TabsTrigger 
                    value="network"
                    class="data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none px-4 py-2"
                >
                    Network & Map
                </TabsTrigger>
            </TabsList>

            <div class="mt-6">
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
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { parseResponse, ensureArray } from '@/utils/responseParser';

interface Setting {
    key: string;
    value: unknown;
    group: string;
    type: string;
}

import { useRoute, useRouter } from 'vue-router';

// ...

const toast = useToast();
const route = useRoute();
const router = useRouter();
const activeTab = ref((route.query.tab as string) || 'whatsapp');

// Watch active tab to update URL
watch(activeTab, (newTab) => {
    router.replace({ query: { ...route.query, tab: newTab } });
});

const loading = ref(false);
const saving = ref(false);
const settings = ref<Setting[]>([]);
const formData = ref<Record<string, unknown>>({});

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/ja/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data) as Setting[];

        // Inject WhatsApp Defaults if missing (logic reused from General Settings)
        const ensureSetting = (key: string, value: unknown, group: string) => {
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
        ensureSetting('billing_suspend_behavior', 'isolation_date', 'isp_billing');

        // Network Defaults (Group: isp_network)
        ensureSetting('network_map_default_lat', -6.200000, 'isp_network');
        ensureSetting('network_map_default_lng', 106.816666, 'isp_network');
        ensureSetting('network_map_default_zoom', 12, 'isp_network');

        // Initialize form data
        formData.value = {};
        settings.value.forEach(s => {
            formData.value[s.key] = s.value;
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
        // Filter only relevant settings for the active tab (or all for simplicity in this dedicated view)
        const settingsToUpdate = Object.keys(formData.value)
            .filter(key => 
                key.startsWith('whatsapp_') || 
                key.startsWith('billing_') || 
                key.startsWith('network_')
            ) 
            .map(key => {
                let group = 'whatsapp';
                if (key.startsWith('billing_')) group = 'isp_billing';
                if (key.startsWith('network_')) group = 'isp_network';
                
                return {
                    key,
                    value: formData.value[key] as unknown,
                    group, 
                    type: key.includes('password') || key.includes('key') ? 'password' : 'string'
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
