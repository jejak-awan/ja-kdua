<template>
    <div class="space-y-6">
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">WhatsApp Configuration</h3>
            <p class="text-sm text-muted-foreground mb-6">
                Configure the WhatsApp driver and credentials for sending notifications.
            </p>

            <div class="space-y-4 max-w-2xl">
                <!-- Driver Selection -->
                <div class="grid gap-2">
                    <label class="text-sm font-medium">Driver</label>
                    <Select :model-value="(formData.whatsapp_driver as string)" @update:model-value="(value) => updateField('whatsapp_driver', value)">
                        <SelectTrigger>
                            <SelectValue placeholder="Select Driver" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="log">Log (Testing via laravel.log)</SelectItem>
                            <SelectItem value="http">HTTP Api (Wablas, Fonnte, etc)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- API URL -->
                <div class="grid gap-2" v-if="formData.whatsapp_driver === 'http'">
                    <label class="text-sm font-medium">API URL</label>
                    <Input 
                        :model-value="(formData.whatsapp_api_url as string)"
                        @update:model-value="(value) => updateField('whatsapp_api_url', value)"
                        placeholder="https://api.provider.com/send"
                    />
                </div>

                <!-- API Key -->
                <div class="grid gap-2" v-if="formData.whatsapp_driver === 'http'">
                    <label class="text-sm font-medium">API Key</label>
                    <Input 
                        type="password"
                        :model-value="(formData.whatsapp_api_key as string)"
                        @update:model-value="(value) => updateField('whatsapp_api_key', value)"
                        placeholder="Secret API Key"
                    />
                </div>

                <div class="pt-4">
                    <Button @click="$emit('save')" :disabled="saving">
                        {{ saving ? 'Saving...' : 'Save Configuration' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
    Input,
    Button
} from '@/components/ui'

interface Props {
    settings: unknown[];
    formData: Record<string, unknown>;
    saving: boolean;
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
    (e: 'save'): void;
}>()

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value })
}
</script>
