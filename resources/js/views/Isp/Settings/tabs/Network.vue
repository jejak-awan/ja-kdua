<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">{{ t('isp.infra.settings.title', 'Network Configuration') }}</h3>
            <p class="text-sm text-muted-foreground mb-6">
                {{ t('isp.infra.settings.subtitle', 'Configure default map location and other network preferences.') }}
            </p>

            <div class="space-y-4 max-w-2xl">
                 <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Default Map Latitude</Label>
                        <Input 
                            type="number" 
                            step="0.000001"
                            :model-value="(formData.network_map_default_lat as string | number)"
                            @update:model-value="(v) => updateField('network_map_default_lat', Number(v))" 
                            placeholder="-6.200000"
                        />
                        <p class="text-xs text-muted-foreground">Default center latitude for new maps.</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Default Map Longitude</Label>
                        <Input 
                            type="number" 
                            step="0.000001"
                            :model-value="(formData.network_map_default_lng as string | number)"
                            @update:model-value="(v) => updateField('network_map_default_lng', Number(v))" 
                            placeholder="106.816666"
                        />
                        <p class="text-xs text-muted-foreground">Default center longitude for new maps.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Default Map Zoom Level</Label>
                    <Input 
                        type="number" 
                        min="1" 
                        max="20"
                        :model-value="(formData.network_map_default_zoom as string | number)"
                        @update:model-value="(v) => updateField('network_map_default_zoom', Number(v))" 
                        placeholder="13"
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
