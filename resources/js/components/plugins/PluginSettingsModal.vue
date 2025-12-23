<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ t('features.developer.plugins.modal.settings_title', { name: plugin?.name }) }}</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-muted-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
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

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ plugin: { type: Object, default: null } });
defineEmits(['close', 'saved']);
</script>

