<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.file_manager.modals.createFolder.title') }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </Button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.file_manager.labels.folderName') }} <span class="text-red-500">*</span>
                        </label>
                        <Input
                            v-model="folderName"
                            type="text"
                            required
                            :placeholder="$t('features.file_manager.placeholders.folderName')"
                        />
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        {{ $t('features.file_manager.actions.cancel') }}
                    </Button>
                    <Button
                        @click="handleSubmit"
                        :disabled="creating || !folderName"
                    >
                        {{ creating ? $t('features.file_manager.actions.creating') : $t('features.file_manager.actions.create') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';

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

