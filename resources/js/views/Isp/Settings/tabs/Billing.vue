<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ t('isp.billing.settings.title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.billing.settings.subtitle') }}
            </p>

            <Accordion type="single" class="w-full" collapsible :default-value="'general'">
                
                <!-- General Settings -->
                <AccordionItem value="general">
                    <AccordionTrigger>{{ t('isp.billing.settings.tabs.general') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.payment_type') }}</Label>
                                <Select :model-value="(formData.billing_payment_type as string)" @update:model-value="(v) => updateField('billing_payment_type', v)">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="prepaid">Prepaid (Prabayar)</SelectItem>
                                        <SelectItem value="postpaid">Postpaid (Pascabayar)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.cycle_type') }}</Label>
                                <Select :model-value="(formData.billing_cycle_type as string)" @update:model-value="(v) => updateField('billing_cycle_type', v)">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="fixed">Fixed Date (e.g. 1st of Month)</SelectItem>
                                        <SelectItem value="profile">Profile Duration (30 Days)</SelectItem>
                                        <SelectItem value="monthly">Anniversary (Installation Date)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 border p-4 rounded-xl">
                            <Checkbox id="prorata" :checked="!!formData.billing_prorata" @update:checked="(v) => updateField('billing_prorata', v)" />
                            <div class="space-y-1">
                                <Label for="prorata" class="cursor-pointer">{{ t('isp.billing.settings.fields.prorata') }}</Label>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('isp.billing.settings.fields.prorata_desc') }}
                                </p>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Tax Settings -->
                <AccordionItem value="tax">
                    <AccordionTrigger>{{ t('isp.billing.settings.tabs.tax') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                        <div class="flex items-center space-x-2 border p-4 rounded-xl mb-4">
                            <Checkbox id="tax_enabled" :checked="!!formData.billing_tax_enabled" @update:checked="(v) => updateField('billing_tax_enabled', v)" />
                            <Label for="tax_enabled" class="cursor-pointer">{{ t('isp.billing.settings.fields.tax_enabled') }}</Label>
                        </div>
                        
                        <div v-if="formData.billing_tax_enabled" class="space-y-2 animate-in fade-in slide-in-from-top-2">
                            <Label>{{ t('isp.billing.settings.fields.tax_rate') }} (%)</Label>
                            <div class="relative">
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_tax_rate as string | number)" 
                                    @update:model-value="(v) => updateField('billing_tax_rate', Number(v))" 
                                    step="0.1" 
                                    min="0" 
                                    max="100" 
                                    class="pr-8" 
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">%</span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ t('isp.billing.settings.fields.tax_rate_help') }}
                            </p>
                        </div>
                    </AccordionContent>
                </AccordionItem>

                <!-- Isolation Settings -->
                <AccordionItem value="isolation">
                    <AccordionTrigger>{{ t('isp.billing.settings.tabs.isolation') }}</AccordionTrigger>
                    <AccordionContent class="space-y-4 pt-4">
                         <div class="space-y-2">
                            <Label>{{ t('isp.billing.settings.fields.suspend_behavior') }}</Label>
                            <Select :model-value="(formData.billing_suspend_behavior as string)" @update:model-value="(v) => updateField('billing_suspend_behavior', v)">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="payment_date">On Due Date (Jatuh Tempo)</SelectItem>
                                    <SelectItem value="isolation_date">On Specific Isolation Date</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.invoice_date') }}</Label>
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_invoice_generation_date as string | number)" 
                                    @update:model-value="(v) => updateField('billing_invoice_generation_date', Number(v))" 
                                    min="1" 
                                    max="28" 
                                />
                                <p class="text-xs text-muted-foreground">{{ t('isp.billing.settings.fields.invoice_date_help') }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('isp.billing.settings.fields.isolation_date') }}</Label>
                                <Input 
                                    type="number" 
                                    :model-value="(formData.billing_isolation_date as string | number)" 
                                    @update:model-value="(v) => updateField('billing_isolation_date', Number(v))" 
                                    min="1" 
                                    max="28" 
                                />
                                <p class="text-xs text-muted-foreground">{{ t('isp.billing.settings.fields.isolation_date_help') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>{{ t('isp.billing.settings.fields.isolation_time') }}</Label>
                            <Input 
                                type="time" 
                                :model-value="(formData.billing_isolation_time as string)" 
                                @update:model-value="(v) => updateField('billing_isolation_time', v)" 
                            />
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>

            <div class="mt-6 pt-4 border-t border-border">
                <Button @click="$emit('save')" :disabled="saving">
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
    settings: any[];
    formData: Record<string, unknown>;
    saving: boolean;
}

const props = defineProps<Props>()
const { t } = useI18n();

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
    (e: 'save'): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
