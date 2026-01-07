<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ $t('features.file_manager.modals.createFolder.title') }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        {{ $t('features.file_manager.labels.folderName') }} <span class="text-destructive">*</span>
                    </label>
                    <Input
                        v-model="folderName"
                        type="text"
                        required
                        :placeholder="$t('features.file_manager.placeholders.folderName')"
                        class="col-span-3"
                    />
                </div>
            </form>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('close')"
                    type="button"
                >
                    {{ $t('features.file_manager.actions.cancel') }}
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="creating || !isValid"
                    type="submit"
                >
                    {{ creating ? $t('features.file_manager.actions.creating') : $t('features.file_manager.actions.create') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Dialog from '../ui/dialog.vue';
import DialogContent from '../ui/dialog-content.vue';
import DialogHeader from '../ui/dialog-header.vue';
import DialogTitle from '../ui/dialog-title.vue';
import DialogFooter from '../ui/dialog-footer.vue';
import { useToast } from '../../composables/useToast';
import { useFormValidation } from '../../composables/useFormValidation';
import { folderSchema } from '../../schemas';

const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(folderSchema);

const props = defineProps({
    path: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close', 'created']);

const folderName = ref('');
const creating = ref(false);

const isValid = computed(() => {
    return !!folderName.value?.trim();
});

const handleSubmit = async () => {
    if (!validateWithZod({ name: folderName.value })) return;

    creating.value = true;
    clearErrors();
    try {
        await api.post('/admin/ja/file-manager/folder', {
            name: folderName.value,
            path: props.path,
        });
        toast.success.create('Folder');
        emit('created');
        emit('close');
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        creating.value = false;
    }
};
</script>

