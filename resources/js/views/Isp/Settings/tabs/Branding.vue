<template>
    <div class="space-y-6">
        <Accordion type="single" class="w-full" collapsible :default-value="'document_identity'">
            <!-- Document Identity Section -->
            <AccordionItem value="document_identity">
                <AccordionTrigger class="text-lg font-semibold">
                    {{ $t('isp.settings.branding.document_identity') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="space-y-6">
                            <SettingField
                                :model-value="formData.invoice_logo"
                                @update:model-value="handleUpdate('invoice_logo', $event)"
                                field-key="invoice_logo"
                                type="image"
                                :label="$t('isp.settings.branding.labels.invoice_logo')"
                                :description="$t('isp.settings.branding.descriptions.invoice_logo')"
                                :error="errors.invoice_logo"
                            />
                        </div>
                        <div class="bg-muted/30 border border-border/40 rounded-xl p-6 flex flex-col items-center justify-center space-y-4">
                            <div class="text-[10px] font-bold text-muted-foreground mb-2">{{ $t('isp.settings.branding.doc_preview') }}</div>
                            
                            <!-- Main Logo Preview container -->
                            <div class="p-8 bg-background border border-border/40 rounded-xl shadow-sm w-full flex items-center justify-center min-h-[120px]">
                                <img 
                                    v-if="formData.invoice_logo" 
                                    :src="(formData.invoice_logo as string)" 
                                    alt="Invoice Logo Preview" 
                                    class="max-h-20 w-auto object-contain"
                                />
                                <div v-else class="text-muted-foreground/30 flex flex-col items-center">
                                    <LucideIcon name="Image" class="w-8 h-8 mb-2" />
                                    <span class="text-xs italic">{{ $t('isp.settings.branding.no_doc_logo') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Company Info Section -->
            <AccordionItem value="company">
                <AccordionTrigger class="text-lg font-semibold border-t pt-4">
                    {{ $t('isp.settings.branding.company_info') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <SettingField
                                :model-value="formData.company_name"
                                @update:model-value="handleUpdate('company_name', $event)"
                                field-key="company_name"
                                :label="$t('isp.settings.branding.labels.company_name')"
                                :description="$t('isp.settings.branding.descriptions.company_name')"
                                :error="errors.company_name"
                            />
                            <SettingField
                                :model-value="formData.company_slogan"
                                @update:model-value="handleUpdate('company_slogan', $event)"
                                field-key="company_slogan"
                                :label="$t('isp.settings.branding.labels.company_slogan')"
                                :description="$t('isp.settings.branding.descriptions.company_slogan')"
                                :error="errors.company_slogan"
                            />
                            <SettingField
                                :model-value="formData.company_address"
                                @update:model-value="handleUpdate('company_address', $event)"
                                field-key="company_address"
                                type="text"
                                :label="$t('isp.settings.branding.labels.company_address')"
                                :description="$t('isp.settings.branding.descriptions.company_address')"
                                :error="errors.company_address"
                            />
                            <SettingField
                                :model-value="formData.company_whatsapp"
                                @update:model-value="handleUpdate('company_whatsapp', $event)"
                                field-key="company_whatsapp"
                                :label="$t('isp.settings.branding.labels.company_whatsapp')"
                                :description="$t('isp.settings.branding.descriptions.company_whatsapp')"
                                :error="errors.company_whatsapp"
                            />
                        </div>

                        <!-- Combined Preview (Logo + Slogan) mirrored from screenshot logic -->
                        <div class="bg-muted/30 border border-border/40 rounded-xl p-8 flex flex-col items-center justify-center space-y-6 text-center">
                            <div class="text-[10px] font-bold text-muted-foreground mb-4">{{ $t('isp.settings.branding.invoice_preview') }}</div>
                            
                            <div class="w-full space-y-3 pb-6 border-b">
                                <div class="flex justify-between items-center text-xs text-muted-foreground">
                                    <Label>{{ $t('isp.settings.branding.logo_size') }}</Label>
                                    <span class="font-mono text-primary">{{ formData.invoice_logo_size }}%</span>
                                </div>
                                <input 
                                    type="range" 
                                    :value="formData.invoice_logo_size" 
                                    @input="(e: any) => handleUpdate('invoice_logo_size', Number(e.target.value))"
                                    min="10" 
                                    max="100" 
                                    step="1"
                                    class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-primary"
                                />
                            </div>

                            <div class="space-y-2 pt-2 transition-all duration-300">
                                <img 
                                    v-if="formData.invoice_logo" 
                                    :src="(formData.invoice_logo as string)" 
                                    :style="{ width: formData.invoice_logo_size + '%' }"
                                    alt="Logo Branding" 
                                    class="h-auto object-contain mx-auto shadow-sm p-1 bg-background"
                                />
                                <div class="text-base font-bold text-foreground mt-4">{{ formData.company_name || 'BRAND NAME' }}</div>
                                <div class="text-xs text-muted-foreground italic max-w-xs mx-auto">{{ formData.company_slogan }}</div>
                            </div>
                        </div>
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Invoice Header & Layout Section -->
            <AccordionItem value="invoice_layout">
                <AccordionTrigger class="text-lg font-semibold border-t pt-4">
                    {{ $t('isp.settings.branding.invoice_layout') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <SettingField
                            :model-value="formData.invoice_kop"
                            @update:model-value="handleUpdate('invoice_kop', $event)"
                            field-key="invoice_kop"
                            type="image"
                            :label="$t('isp.settings.branding.labels.invoice_kop')"
                            :description="$t('isp.settings.branding.descriptions.invoice_kop')"
                            :error="errors.invoice_kop"
                        />
                        <SettingField
                            :model-value="formData.invoice_watermark"
                            @update:model-value="handleUpdate('invoice_watermark', $event)"
                            field-key="invoice_watermark"
                            type="image"
                            :label="$t('isp.settings.branding.labels.invoice_watermark')"
                            :description="$t('isp.settings.branding.descriptions.invoice_watermark')"
                            :error="errors.invoice_watermark"
                        />
                    </div>
                    
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-8">
                        <SettingField
                            :model-value="formData.invoice_signature_name"
                            @update:model-value="handleUpdate('invoice_signature_name', $event)"
                            field-key="invoice_signature_name"
                            :label="$t('isp.settings.branding.labels.invoice_signature_name')"
                            :description="$t('isp.settings.branding.descriptions.invoice_signature_name')"
                            :error="errors.invoice_signature_name"
                        />
                        <SettingField
                            :model-value="formData.invoice_signature_image"
                            @update:model-value="handleUpdate('invoice_signature_image', $event)"
                            field-key="invoice_signature_image"
                            type="image"
                            :label="$t('isp.settings.branding.labels.invoice_signature_image')"
                            :description="$t('isp.settings.branding.descriptions.invoice_signature_image')"
                            :error="errors.invoice_signature_image"
                        />
                        <SettingField
                            :model-value="formData.invoice_signature_title"
                            @update:model-value="handleUpdate('invoice_signature_title', $event)"
                            field-key="invoice_signature_title"
                            :label="$t('isp.settings.branding.labels.invoice_signature_title')"
                            :description="$t('isp.settings.branding.descriptions.invoice_signature_title')"
                            :error="errors.invoice_signature_title"
                        />
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Invoice Notes Section -->
            <AccordionItem value="invoice_notes">
                <AccordionTrigger class="text-lg font-semibold border-t pt-4">
                    {{ $t('isp.settings.branding.invoice_notes') }}
                </AccordionTrigger>
                <AccordionContent class="pt-6 space-y-8">
                    <div class="flex items-center space-x-2 bg-muted/20 p-4 rounded-xl border border-border/40 border-dashed">
                        <SettingField
                            :model-value="formData.invoice_show_bank_account"
                            @update:model-value="handleUpdate('invoice_show_bank_account', $event)"
                            field-key="invoice_show_bank_account"
                            type="boolean"
                            :label="$t('isp.settings.branding.labels.invoice_show_bank_account')"
                            :description="$t('isp.settings.branding.descriptions.invoice_show_bank_account')"
                            :error="errors.invoice_show_bank_account"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <SettingField
                            :model-value="formData.invoice_note_unpaid"
                            @update:model-value="handleUpdate('invoice_note_unpaid', $event)"
                            field-key="invoice_note_unpaid"
                            type="text"
                            :label="$t('isp.settings.branding.labels.invoice_note_unpaid')"
                            :description="$t('isp.settings.branding.descriptions.invoice_note_unpaid')"
                            :error="errors.invoice_note_unpaid"
                        />
                        <SettingField
                            :model-value="formData.invoice_note_paid"
                            @update:model-value="handleUpdate('invoice_note_paid', $event)"
                            field-key="invoice_note_paid"
                            type="text"
                            :label="$t('isp.settings.branding.labels.invoice_note_paid')"
                            :description="$t('isp.settings.branding.descriptions.invoice_note_paid')"
                            :error="errors.invoice_note_paid"
                        />
                    </div>

                    <div class="border-t pt-8">
                        <SettingField
                            :model-value="formData.invoice_footer"
                            @update:model-value="handleUpdate('invoice_footer', $event)"
                            field-key="invoice_footer"
                            :label="$t('isp.settings.branding.labels.invoice_footer')"
                            :description="$t('isp.settings.branding.descriptions.invoice_footer')"
                            :error="errors.invoice_footer"
                        />
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
    Label,
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
