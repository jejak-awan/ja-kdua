<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ t('features.developer.plugins.modal.settings_title', { name: plugin?.name }) }}</h3>
                    <button @click="$emit('close')" class="text-muted-foreground hover:text-muted-foreground">
                        <X class="w-6 h-6" />
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-muted-foreground">{{ t('features.developer.plugins.modal.settings_placeholder') }}</p>
                </div>
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button @click="$emit('close')" class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted">{{ t('features.developer.plugins.modal.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import X from 'lucide-vue-next/dist/esm/icons/x.js';

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

