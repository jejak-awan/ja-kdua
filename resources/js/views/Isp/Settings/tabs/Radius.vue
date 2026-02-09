<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ t('isp.network.settings.radius_title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.network.settings.radius_subtitle') }}
            </p>

            <div class="space-y-4 max-w-2xl">
                 <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>{{ t('isp.network.settings.fields.coa_port') }}</Label>
                        <Input 
                            type="number" 
                            :model-value="(formData.radius_coa_port as string | number)"
                            @update:model-value="(v) => updateField('radius_coa_port', Number(v))" 
                            placeholder="1700"
                        />
                        <p class="text-xs text-muted-foreground">Default CoA port for Mikrotik is 1700.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>{{ t('isp.network.settings.fields.default_speed') }}</Label>
                    <Input 
                        type="text" 
                        :model-value="(formData.radius_default_speed as string)"
                        @update:model-value="(v) => updateField('radius_default_speed', v)" 
                        placeholder="10M/10M"
                    />
                    <p class="text-xs text-muted-foreground">Fallback speed if plan has no limit defined.</p>
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
