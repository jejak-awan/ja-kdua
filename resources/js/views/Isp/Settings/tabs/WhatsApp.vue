<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ t('isp.settings.whatsapp.title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.settings.whatsapp.subtitle') }}
            </p>

            <div class="space-y-4 max-w-2xl">
                <!-- Driver Selection -->
                <div class="grid gap-2">
                    <label class="text-sm font-medium">{{ t('isp.settings.whatsapp.fields.driver') }}</label>
                    <Select :model-value="(formData.whatsapp_driver as string)" @update:model-value="(value) => handleUpdate('whatsapp_driver', value)">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('isp.settings.whatsapp.fields.driver')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="log">{{ t('isp.settings.whatsapp.drivers.log') }}</SelectItem>
                            <SelectItem value="http">{{ t('isp.settings.whatsapp.drivers.http') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- API URL -->
                <div class="grid gap-2" v-if="formData.whatsapp_driver === 'http'">
                    <label class="text-sm font-medium">{{ t('isp.settings.whatsapp.fields.api_url') }}</label>
                    <Input 
                        :model-value="(formData.whatsapp_api_url as string)"
                        @update:model-value="(value) => handleUpdate('whatsapp_api_url', value)"
                        placeholder="https://api.provider.com/send"
                    />
                </div>

                <!-- API Key -->
                <div class="grid gap-2" v-if="formData.whatsapp_driver === 'http'">
                    <label class="text-sm font-medium">{{ t('isp.settings.whatsapp.fields.api_key') }}</label>
                    <Input 
                        type="password"
                        :model-value="(formData.whatsapp_api_key as string)"
                        @update:model-value="(value) => handleUpdate('whatsapp_api_key', value)"
                        placeholder="Secret API Key"
                    />
                </div>

                <div class="pt-4">
                    <Button @click="$emit('save')" :disabled="saving">
                        {{ saving ? t('common.actions.saving') : t('common.actions.save') }}
                    </Button>
                </div>
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
    Button
} from '@/components/ui'
import type { SettingValue } from '@/types/settings'

interface Props {
    settings: unknown[];
    formData: Record<string, unknown>;
    saving: boolean;
}

const props = defineProps<Props>()
const { t } = useI18n();
const emit = defineEmits(['update:formData', 'save'])

const handleUpdate = (key: string, value: SettingValue) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
