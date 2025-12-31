<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ t('features.menus.form.createTitle') }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label>
                        {{ t('features.menus.form.name') }} <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        v-model="form.name"
                        type="text"
                        required
                        :placeholder="t('features.menus.form.placeholders.name')"
                    />
                </div>
                <div class="space-y-2">
                    <Label>
                        {{ t('features.menus.form.location') }}
                    </Label>
                    <Input
                        v-model="form.location"
                        type="text"
                        :placeholder="t('features.menus.form.placeholders.location')"
                    />
                </div>
            </form>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('close')"
                >
                    {{ t('features.menus.actions.cancel') }}
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="saving"
                >
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? t('features.menus.actions.creating') : t('features.menus.actions.createAction') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Dialog from '../ui/dialog.vue';
import DialogContent from '../ui/dialog-content.vue';
import DialogHeader from '../ui/dialog-header.vue';
import DialogTitle from '../ui/dialog-title.vue';
import DialogFooter from '../ui/dialog-footer.vue';
import Button from '../ui/button.vue';
import Input from '../ui/input.vue';
import Label from '../ui/label.vue';
import { Loader2 } from 'lucide-vue-next';
import { useToast } from '../../composables/useToast';

const { t } = useI18n();
const toast = useToast();
const errors = ref({});

const emit = defineEmits(['close', 'saved']);
const router = useRouter();

const saving = ref(false);
const form = ref({
    name: '',
    location: '',
});

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};
    try {
        const response = await api.post('/admin/cms/menus', form.value);
        const menu = response.data.data || response.data;
        toast.success.create('Menu');
        emit('saved');
        router.push({ name: 'menus.edit', params: { id: menu.id } });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>

