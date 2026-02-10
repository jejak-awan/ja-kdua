<template>
    <div class="space-y-6">
        <Accordion type="single" class="w-full" collapsible :default-value="'domain'">
            <!-- Custom Domain Section -->
            <AccordionItem value="domain">
                <AccordionTrigger class="text-lg font-semibold">
                    {{ $t('isp.settings.integrations.custom_domain') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <SettingField
                        :model-value="formData.custom_domain"
                        @update:model-value="handleUpdate('custom_domain', $event)"
                        field-key="custom_domain"
                        :label="$t('isp.settings.integrations.labels.custom_domain')"
                        :description="$t('isp.settings.integrations.descriptions.custom_domain')"
                        :error="errors.custom_domain"
                    />
                </AccordionContent>
            </AccordionItem>

            <!-- MutasiBank Section -->
            <AccordionItem value="mutasibank">
                <AccordionTrigger class="text-lg font-semibold">
                    {{ $t('isp.settings.integrations.mutasibank') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <SettingField
                        :model-value="formData.mutasibank_api_key"
                        @update:model-value="handleUpdate('mutasibank_api_key', $event)"
                        field-key="mutasibank_api_key"
                        type="password"
                        :label="$t('isp.settings.integrations.labels.mutasibank_api_key')"
                        :description="$t('isp.settings.integrations.descriptions.mutasibank_api_key')"
                        :error="errors.mutasibank_api_key"
                    />
                </AccordionContent>
            </AccordionItem>

            <!-- Moota Section -->
            <AccordionItem value="moota">
                <AccordionTrigger class="text-lg font-semibold">
                    {{ $t('isp.settings.integrations.moota') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <SettingField
                        :model-value="formData.moota_api_key"
                        @update:model-value="handleUpdate('moota_api_key', $event)"
                        field-key="moota_api_key"
                        type="password"
                        :label="$t('isp.settings.integrations.labels.moota_api_key')"
                        :description="$t('isp.settings.integrations.descriptions.moota_api_key')"
                        :error="errors.moota_api_key"
                    />
                </AccordionContent>
            </AccordionItem>

            <!-- Payment Gateways Section -->
            <AccordionItem value="payment_gateways">
                <AccordionTrigger class="text-lg font-semibold border-t pt-4 uppercase">
                    {{ $t('isp.settings.integrations.payment_gateways') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 p-6 bg-muted/30 border rounded-xl">
                        <div class="flex-1 space-y-2">
                            <h4 class="font-bold text-lg flex items-center gap-2">
                                <LucideIcon name="Wallet" class="w-5 h-5 text-primary" />
                                {{ $t('isp.settings.integrations.gateway_title') }}
                            </h4>
                            <p class="text-sm text-muted-foreground leading-relaxed">
                                {{ $t('isp.settings.integrations.descriptions.integrations_payment_gateways') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <router-link to="/admin/isp/billing/gateway">
                                <Button variant="default" class="shadow-sm hover:shadow-md transition-all gap-2 px-6">
                                    <LucideIcon name="Settings" class="w-4 h-4" />
                                    {{ $t('isp.settings.integrations.labels.integrations_payment_gateways_manage') }}
                                </Button>
                            </router-link>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>

<script setup lang="ts">
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
    Button,
    LucideIcon
} from '@/components/ui'
import SettingField from '@/components/settings/SettingField.vue'
import type { SettingValue } from '@/types/settings'

interface IspSetting {
    key: string;
    value: SettingValue;
    [key: string]: unknown;
}

interface Props {
    settings: IspSetting[];
    formData: Record<string, SettingValue>;
    errors: Record<string, string[]>;
}

const props = defineProps<Props>()
const emit = defineEmits(['update:formData'])

const handleUpdate = (key: string, value: SettingValue) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
