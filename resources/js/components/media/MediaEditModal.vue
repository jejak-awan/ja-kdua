<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg shadow-xl max-w-2xl w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.media.modals.edit.title') }}</h3>
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
                    <div v-if="loading" class="text-center py-8">
                        <p class="text-muted-foreground">{{ $t('features.media.loading') }}</p>
                    </div>

                    <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                        <!-- Preview -->
                        <div v-if="form.url && form.mime_type?.startsWith('image/')" class="mb-4">
                            <img :src="form.url" :alt="form.alt" class="w-full h-64 object-contain bg-secondary rounded-lg">
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.edit.name') }}
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>

                        <!-- Alt Text -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.edit.altText') }}
                            </label>
                            <input
                                v-model="form.alt"
                                type="text"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="$t('features.media.modals.edit.altPlaceholder')"
                            >
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.edit.description') }}
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>

                        <!-- Folder -->
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.edit.folder') }}
                            </label>
                            <select
                                v-model="form.folder_id"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">{{ $t('features.media.modals.edit.noFolder') }}</option>
                                <option
                                    v-for="folder in folders"
                                    :key="folder.id"
                                    :value="folder.id"
                                >
                                    {{ folder.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Media Info -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                            <div>
                                <p class="text-xs text-muted-foreground">{{ $t('features.media.modals.edit.type') }}</p>
                                <p class="text-sm text-foreground">{{ form.mime_type }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">{{ $t('features.media.modals.edit.size') }}</p>
                                <p class="text-sm text-foreground">{{ formatFileSize(form.size) }}</p>
                            </div>
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
                        {{ saving ? $t('features.media.modals.edit.saving') : $t('features.media.actions.save') }}
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

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'updated']);

const loading = ref(false);
const saving = ref(false);
const folders = ref([]);

const form = ref({
    name: '',
    alt: '',
    description: '',
    folder_id: null,
    url: '',
    mime_type: '',
    size: 0,
});

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        folders.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch folders:', error);
    }
};

const loadMedia = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/media/${props.media.id}`);
        const media = response.data;
        
        form.value = {
            name: media.name || '',
            alt: media.alt || '',
            description: media.description || '',
            folder_id: media.folder_id || null,
            url: media.url || '',
            mime_type: media.mime_type || '',
            size: media.size || 0,
        };
    } catch (error) {
        console.error('Failed to load media:', error);
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/media/${props.media.id}`, {
            name: form.value.name,
            alt: form.value.alt,
            description: form.value.description,
            folder_id: form.value.folder_id,
        });
        
        emit('updated');
    } catch (error) {
        console.error('Failed to update media:', error);
        alert(t('features.media.messages.updateFailed'));
    } finally {
        saving.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    loadMedia();
    fetchFolders();
});
</script>

