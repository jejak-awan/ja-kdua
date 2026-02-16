<template>
    <div class="space-y-6">
        <div class="bg-card border border-border/40 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-4">{{ t('isp.settings.network.title') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.settings.network.subtitle') }}
            </p>

            <div class="space-y-4 max-w-2xl">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>{{ t('isp.settings.network.fields.map_lat') }}</Label>
                        <Input 
                            type="number" 
                            step="0.000001"
                            :model-value="(formData.network_map_default_lat as string | number)"
                            @update:model-value="(v) => handleUpdate('network_map_default_lat', Number(v))" 
                            placeholder="-6.200000"
                            class="rounded-xl"
                        />
                        <p class="text-xs text-muted-foreground">{{ t('isp.settings.network.help.map_lat') }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('isp.settings.network.fields.map_lng') }}</Label>
                        <Input 
                            type="number" 
                            step="0.000001"
                            :model-value="(formData.network_map_default_lng as string | number)"
                            @update:model-value="(v) => handleUpdate('network_map_default_lng', Number(v))" 
                            placeholder="106.816666"
                            class="rounded-xl"
                        />
                        <p class="text-xs text-muted-foreground">{{ t('isp.settings.network.help.map_lng') }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>{{ t('isp.settings.network.fields.map_zoom') }}</Label>
                    <Input 
                        type="number" 
                        min="1" 
                        max="20"
                        :model-value="(formData.network_map_default_zoom as string | number)"
                        @update:model-value="(v) => handleUpdate('network_map_default_zoom', Number(v))" 
                        placeholder="13"
                        class="rounded-xl"
                    />
                </div>

                <div class="pt-4">
                    <Button @click="$emit('save')" :disabled="saving" class="rounded-xl">
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
