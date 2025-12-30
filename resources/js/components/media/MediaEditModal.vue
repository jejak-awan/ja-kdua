<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-4xl w-full">
                <div class="flex items-center justify-between px-5 py-3 border-b">
                    <h3 class="text-base font-semibold">{{ $t('features.media.modals.edit.title') }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <!-- Content -->
                <div class="px-5 py-4">
                    <div v-if="loading" class="text-center py-8">
                        <p class="text-muted-foreground">{{ $t('features.media.loading') }}</p>
                    </div>

                    <form v-else @submit.prevent="handleSubmit">
                        <!-- Grid Layout: Image Left, Fields Right -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Left: Image Preview -->
                            <div class="flex flex-col">
                                <div v-if="form.url && form.mime_type?.startsWith('image/')" class="bg-secondary rounded-lg overflow-hidden flex items-center justify-center h-64 md:h-full">
                                    <img :src="form.url" :alt="form.alt" class="w-full h-full object-contain">
                                </div>
                                <div v-else class="bg-secondary rounded-lg flex items-center justify-center h-64 md:h-full">
                                    <FileIcon class="w-16 h-16 text-muted-foreground opacity-50" />
                                </div>

                                <!-- Media Info (below image on desktop) -->
                                <div class="grid grid-cols-2 gap-3 mt-3 p-2.5 bg-muted/50 rounded-lg">
                                    <div>
                                        <p class="text-xs text-muted-foreground">{{ $t('features.media.modals.edit.type') }}</p>
                                        <p class="text-sm text-foreground font-medium">{{ form.mime_type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">{{ $t('features.media.modals.edit.size') }}</p>
                                        <p class="text-sm text-foreground font-medium">{{ formatFileSize(form.size) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Form Fields -->
                            <div class="space-y-3">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1.5">
                                        {{ $t('features.media.modals.edit.name') }}
                                    </label>
                                    <Input
                                        v-model="form.name"
                                        type="text"
                                        required
                                    />
                                </div>

                                <!-- Alt Text -->
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1.5">
                                        {{ $t('features.media.modals.edit.altText') }}
                                    </label>
                                    <Input
                                        v-model="form.alt"
                                        type="text"
                                        :placeholder="$t('features.media.modals.edit.altPlaceholder')"
                                    />
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1.5">
                                        {{ $t('features.media.modals.edit.description') }}
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                                    />
                                </div>

                                <!-- Folder -->
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1.5">
                                        {{ $t('features.media.modals.edit.folder') }}
                                    </label>
                                    <Select v-model="form.folder_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue :placeholder="$t('features.media.modals.edit.noFolder')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">{{ $t('features.media.modals.edit.noFolder') }}</SelectItem>
                                            <SelectItem
                                                v-for="folder in folders"
                                                :key="folder.id"
                                                :value="folder.id"
                                            >
                                                {{ folder.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Shared Status (Admin Only) -->
                                <div v-if="canManageMedia" class="flex items-start space-x-2 p-3 bg-blue-50/50 border border-blue-200 rounded-lg">
                                    <Checkbox 
                                        id="is_shared" 
                                        v-model:checked="form.is_shared"
                                        class="mt-0.5"
                                    />
                                    <div class="flex-1">
                                        <label for="is_shared" class="text-sm font-medium text-foreground cursor-pointer">
                                            {{ $t('features.media.modals.edit.isShared') }}
                                        </label>
                                        <p class="text-xs text-muted-foreground mt-0.5">
                                            {{ $t('features.media.modals.edit.isSharedHelp') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 px-5 py-3 border-t">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        {{ $t('features.media.actions.cancel') }}
                    </Button>
                    <Button
                        @click="handleSubmit"
                        :disabled="saving"
                    >
                        {{ saving ? $t('features.media.modals.edit.saving') : $t('features.media.actions.save') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/composables/useToast.js';
import { X, FileText as FileIcon } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Select from '../ui/select.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import Checkbox from '../ui/checkbox.vue';

const authStore = useAuthStore();
const { t } = useI18n();
const toast = useToast();

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
    is_shared: false,
});

const canManageMedia = authStore.hasPermission('manage media');

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        folders.value = response.data.data || response.data || [];
    } catch (error) {
        // console.error('Failed to fetch folders:', error);
    }
};

const loadMedia = () => {
    // Use props.media directly since data is already passed from parent
    if (props.media) {
        form.value = {
            name: props.media.name || '',
            alt: props.media.alt || '',
            description: props.media.description || '',
            folder_id: props.media.folder_id || null,
            url: props.media.url || '',
            mime_type: props.media.mime_type || '',
            size: props.media.size || 0,
            is_shared: props.media.is_shared || false,
        };
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
            is_shared: form.value.is_shared,
        });
        
        emit('updated');
    } catch (error) {
        // console.error('Failed to update media:', error);
        toast.error.fromResponse(error);
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
