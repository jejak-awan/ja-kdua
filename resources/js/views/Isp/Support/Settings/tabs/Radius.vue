<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ t('isp.settings.radius.title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.settings.radius.subtitle') }}
            </p>

            <div class="space-y-4 max-w-2xl">
                 <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>{{ t('isp.settings.radius.fields.coa_port') }}</Label>
                        <Input 
                            type="number" 
                            :model-value="(formData.radius_coa_port as string | number)"
                            @update:model-value="(v) => updateField('radius_coa_port', Number(v))" 
                            placeholder="1700"
                        />
                        <p class="text-xs text-muted-foreground">{{ t('isp.settings.radius.help.coa_port') }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>{{ t('isp.settings.radius.fields.default_speed') }}</Label>
                    <Input 
                        :model-value="(formData.radius_default_speed as string)"
                        @update:model-value="handleUpdate('radius_default_speed', $event)" 
                        placeholder="10M/10M"
                    />
                    <p class="text-xs text-muted-foreground">{{ t('isp.settings.radius.help.default_speed') }}</p>
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
    Input,
    Button,
    Label
} from '@/components/ui'

interface Props {
    settings: unknown[];
    formData: Record<string, unknown>;
    saving: boolean;
}

type SettingValue = string | number | boolean | null | undefined;

const props = defineProps<Props>()
const { t } = useI18n();

const emit = defineEmits(['update:formData', 'save'])

const handleUpdate = (key: string, value: SettingValue) => {
    emit('update:formData', { ...props.formData, [key]: value })
}

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
