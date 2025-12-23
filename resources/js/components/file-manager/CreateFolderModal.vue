<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.file_manager.modals.createFolder.title') }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.file_manager.labels.folderName') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="folderName"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="$t('features.file_manager.placeholders.folderName')"
                        >
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ $t('features.file_manager.actions.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="creating || !folderName"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ creating ? $t('features.file_manager.actions.creating') : $t('features.file_manager.actions.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

const props = defineProps({
    path: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close', 'created']);

const folderName = ref('');
const creating = ref(false);

const handleSubmit = async () => {
    if (!folderName.value) return;

    creating.value = true;
    try {
        await api.post('/admin/cms/file-manager/folder', {
            name: folderName.value,
            path: props.path,
        });
        emit('created');
    } catch (error) {
        console.error('Failed to create folder:', error);
        alert(t('features.file_manager.messages.createFolderFailed'));
    } finally {
        creating.value = false;
    }
};
</script>

