<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.media.modals.folder.title') }}</h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.folder.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="$t('features.media.modals.folder.placeholder')"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.folder.parent') }}
                            </label>
                            <select
                                v-model="form.parent_id"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">{{ $t('features.media.modals.folder.noParent') }}</option>
                                <option
                                    v-for="folder in folders"
                                    :key="folder.id"
                                    :value="folder.id"
                                >
                                    {{ folder.name }}
                                </option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ $t('features.media.actions.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? $t('features.media.modals.folder.creating') : $t('features.media.modals.folder.create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

const { t } = useI18n();

const emit = defineEmits(['close', 'created']);

const saving = ref(false);
const folders = ref([]);

const form = ref({
    name: '',
    parent_id: null,
});

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        folders.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch folders:', error);
    }
};

const handleSubmit = async () => {
    if (!form.value.name.trim()) return;

    saving.value = true;
    try {
        await api.post('/admin/cms/media-folders', form.value);
        emit('created');
        form.value = { name: '', parent_id: null };
    } catch (error) {
        console.error('Failed to create folder:', error);
        alert(t('features.media.messages.createFolderFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchFolders();
});
</script>

