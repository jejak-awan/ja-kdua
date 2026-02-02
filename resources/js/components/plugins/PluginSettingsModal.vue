<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ t('features.developer.plugins.modal.settings_title', { name: plugin?.name }) }}</h3>
                    <Button variant="ghost" size="icon" @click="$emit('close')">
                        <X class="w-5 h-5" />
                    </Button>
                </div>
                <div class="p-6">
                    <p class="text-muted-foreground">{{ t('features.developer.plugins.modal.settings_placeholder') }}</p>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <Button variant="outline" @click="$emit('close')">{{ t('features.developer.plugins.modal.close') }}</Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { Button } from '@/components/ui';

const { t } = useI18n();

interface Plugin {
    name: string;
    [key: string]: any;
}

defineProps<{
    plugin?: Plugin | null;
}>();

defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();
</script>

