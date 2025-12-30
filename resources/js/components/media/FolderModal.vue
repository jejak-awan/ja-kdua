<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">{{ $t('features.media.modals.folder.title') }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.folder.name') }} <span class="text-red-500">*</span>
                            </label>
                            <Input
                                v-model="form.name"
                                type="text"
                                required
                                :placeholder="$t('features.media.modals.folder.placeholder')"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ $t('features.media.modals.folder.parent') }}
                            </label>
                            <Select v-model="form.parent_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">{{ $t('features.media.modals.folder.noParent') }}</SelectItem>
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
                                id="folder_is_shared" 
                                v-model:checked="form.is_shared"
                                class="mt-0.5"
                            />
                            <div class="flex-1">
                                <label for="folder_is_shared" class="text-sm font-medium text-foreground cursor-pointer">
                                    {{ $t('features.media.modals.edit.isShared') }}
                                </label>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    {{ $t('features.media.modals.edit.isSharedHelp') }}
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end space-x-3 p-6 border-t">
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
                        {{ saving ? $t('features.media.modals.folder.creating') : $t('features.media.modals.folder.create') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMediaToast } from '../../composables/useMediaToast';
import { X } from 'lucide-vue-next';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Select from '../ui/select.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import Checkbox from '../ui/checkbox.vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const { t } = useI18n();
const mediaToast = useMediaToast();

const emit = defineEmits(['close', 'created']);

const saving = ref(false);
const folders = ref([]);

const form = ref({
    name: '',
    parent_id: null,
    is_shared: false,
});

const canManageMedia = authStore.hasPermission('manage media');

const fetchFolders = async () => {
    try {
        const response = await api.get('/admin/cms/media-folders');
        const data = response.data.data || response.data || [];
        folders.value = data.filter(f => !f.is_trashed);
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
        form.value = { name: '', parent_id: null, is_shared: false };
    } catch (error) {
        console.error('Failed to create folder:', error);
        mediaToast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchFolders();
});
</script>

