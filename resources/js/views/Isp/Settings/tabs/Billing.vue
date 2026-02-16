<template>
    <div class="space-y-6">
        <div class="bg-card border border-border/40 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-4">{{ t('isp.settings.billing.title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.settings.billing.subtitle') }}
            </p>

            <Accordion type="single" class="w-full" collapsible :default-value="'general'">
                <!-- General Settings -->
                <AccordionItem value="general">
                    <AccordionTrigger>{{ t('isp.settings.billing.tabs.general') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.payment_type') }}</Label>
                                <Select :model-value="(formData.billing_payment_type as string)" @update:model-value="(v) => handleUpdate('billing_payment_type', v)">
                                    <SelectTrigger class="rounded-xl">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="prepaid">{{ t('isp.settings.billing.options.prepaid') }}</SelectItem>
                                        <SelectItem value="postpaid">{{ t('isp.settings.billing.options.postpaid') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.cycle_type') }}</Label>
                                <Select :model-value="(formData.billing_cycle_type as string)" @update:model-value="(v) => handleUpdate('billing_cycle_type', v)">
                                    <SelectTrigger class="rounded-xl">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fixed">{{ t('isp.settings.billing.options.fixed') }}</SelectItem>
                                        <SelectItem value="profile">{{ t('isp.settings.billing.options.profile') }}</SelectItem>
                                        <SelectItem value="monthly">{{ t('isp.settings.billing.options.monthly') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 border p-4 rounded-xl">
                            <Checkbox id="prorata" :checked="!!formData.billing_prorata" @update:checked="(v) => handleUpdate('billing_prorata', v)" />
                            <div class="space-y-1">
                                <Label for="prorata" class="cursor-pointer">{{ t('isp.settings.billing.fields.prorata') }}</Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('isp.settings.billing.fields.prorata_desc') }}
                                </p>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Tax Settings -->
                <AccordionItem value="tax">
                    <AccordionTrigger>{{ t('isp.settings.billing.tabs.tax') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                        <div class="flex items-center space-x-2 border p-4 rounded-xl mb-4">
                            <Checkbox id="tax_enabled" :checked="!!formData.billing_tax_enabled" @update:checked="(v) => handleUpdate('billing_tax_enabled', v)" />
                            <Label for="tax_enabled" class="cursor-pointer">{{ t('isp.settings.billing.fields.tax_enabled') }}</Label>
                        </div>
                        
                        <div v-if="formData.billing_tax_enabled" class="space-y-4 animate-in fade-in slide-in-from-top-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label>{{ t('isp.settings.billing.fields.tax_ppn') }} (%)</Label>
                                    <Input 
                                        type="number" 
                                        :model-value="Number((formData.billing_tax_ppn as number) * 100).toFixed(1)" 
                                        @update:model-value="(v) => handleUpdate('billing_tax_ppn', Number(v) / 100)" 
                                        step="0.1" 
                                        class="rounded-xl"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.settings.billing.fields.tax_bhp') }} (%)</Label>
                                    <Input 
                                        type="number" 
                                        :model-value="Number((formData.billing_tax_bhp as number) * 100).toFixed(2)" 
                                        @update:model-value="(v) => handleUpdate('billing_tax_bhp', Number(v) / 100)" 
                                        step="0.01" 
                                        class="rounded-xl"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>{{ t('isp.settings.billing.fields.tax_uso') }} (%)</Label>
                                    <Input 
                                        type="number" 
                                        :model-value="Number((formData.billing_tax_uso as number) * 100).toFixed(2)" 
                                        @update:model-value="(v) => handleUpdate('billing_tax_uso', Number(v) / 100)" 
                                        step="0.01" 
                                        class="rounded-xl"
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ t('isp.settings.billing.fields.tax_rate_help') }}
                            </p>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Isolation Settings -->
                <AccordionItem value="isolation">
                    <AccordionTrigger>{{ t('isp.settings.billing.tabs.isolation') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                         <div class="grid grid-cols-2 gap-4">
                             <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.suspend_behavior') }}</Label>
                                <Select :model-value="(formData.billing_suspend_behavior as string)" @update:model-value="(v) => handleUpdate('billing_suspend_behavior', v)">
                                    <SelectTrigger class="rounded-xl">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="payment_date">{{ t('isp.settings.billing.options.payment_date') }}</SelectItem>
                                        <SelectItem value="isolation_date">{{ t('isp.settings.billing.options.isolation_date') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.invoice_due_days') }}</Label>
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_invoice_due_days as string | number)" 
                                    @update:model-value="(v) => handleUpdate('billing_invoice_due_days', Number(v))" 
                                    min="1" 
                                    max="30" 
                                    class="rounded-xl"
                                />
                                <p class="text-xs text-muted-foreground">{{ t('isp.settings.billing.fields.invoice_due_days_help') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.invoice_date') }}</Label>
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_invoice_generation_date as string | number)" 
                                    @update:model-value="(v) => handleUpdate('billing_invoice_generation_date', Number(v))" 
                                    min="1" 
                                    max="28" 
                                    class="rounded-xl"
                                />
                                <p class="text-xs text-muted-foreground">{{ t('isp.settings.billing.fields.invoice_date_help') }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.settings.billing.fields.isolation_date') }}</Label>
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_isolation_date as string | number)" 
                                    @update:model-value="(v) => handleUpdate('billing_isolation_date', Number(v))" 
                                    min="1" 
                                    max="28" 
                                    class="rounded-xl"
                                />
                                <p class="text-xs text-muted-foreground">{{ t('isp.settings.billing.fields.isolation_date_help') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('isp.settings.billing.fields.isolation_time') }}</Label>
                            <Input 
                                type="time" 
                                :model-value="(formData.billing_isolation_time as string)" 
                                @update:model-value="(v) => handleUpdate('billing_isolation_time', v)" 
                                class="rounded-xl"
                            />
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>

            <div class="mt-6 pt-4 border-t border-border/40">
                <Button @click="$emit('save')" :disabled="saving" class="rounded-xl">
                    {{ saving ? t('common.actions.saving') : t('common.actions.save') }}
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Input,
    Button,
    Checkbox,
    Label,
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger
} from '@/components/ui'

interface Props {
    settings: unknown[];
    formData: Record<string, unknown>;
    saving: boolean;
}

const props = defineProps<Props>()
const { t } = useI18n();

const emit = defineEmits(['update:formData', 'save'])

const handleUpdate = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
