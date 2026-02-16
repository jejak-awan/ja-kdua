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
                    <Select v-model="form.location">
                        <SelectTrigger>
                            <SelectValue :placeholder="t('features.menus.form.placeholders.location')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="loc in locationOptions" 
                                :key="loc.value" 
                                :value="loc.value"
                            >
                                {{ loc.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
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
                    :disabled="saving || !isValid"
                >
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? t('features.menus.actions.creating') : t('features.menus.actions.createAction') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, onMounted } from 'vue';

import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Dialog from '../ui/Dialog.vue';
import DialogContent from '../ui/DialogContent.vue';
import DialogHeader from '../ui/DialogHeader.vue';
import DialogTitle from '../ui/DialogTitle.vue';
import DialogFooter from '../ui/DialogFooter.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Label from '../ui/Label.vue';
import Select from '../ui/Select.vue';
import SelectTrigger from '../ui/SelectTrigger.vue';
import SelectValue from '../ui/SelectValue.vue';
import SelectContent from '../ui/SelectContent.vue';
import SelectItem from '../ui/SelectItem.vue';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { useToast } from '../../composables/useToast';
import { useFormValidation } from '../../composables/useFormValidation';
import { menuSchema } from '../../schemas';

const { t } = useI18n();
const toast = useToast();
const { validateWithZod, setErrors, clearErrors } = useFormValidation(menuSchema);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved', menu: { id?: string | number }): void;
}>();

const saving = ref(false);
const form = ref({
    name: '',
    location: '',
});

interface LocationOption {
    value: string;
    label: string;
}

const locationOptions = ref<LocationOption[]>([]);
const loadingLocations = ref(false);

const fetchLocations = async () => {
    loadingLocations.value = true;
    try {
        const response = await api.get('/admin/janet/themes/active/locations');
        const data = response.data?.data || response.data || {};
        
        // Transform { key: label } to [{ value: key, label: label }]
        locationOptions.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label as string
        }));
    } catch (error) {
        logger.error('Failed to fetch menu locations:', error);
        toast.error.load(error);
    } finally {
        loadingLocations.value = false;
    }
};

onMounted(() => {
    fetchLocations();
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        const response = await api.post('/admin/janet/menus', form.value);
        const menu = response.data.data || response.data;
        toast.success.create('Menu');
        emit('saved', menu);
        emit('close');
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { status?: number, data?: { errors?: Record<string, string[]>, message?: string } } };
            if (err.response?.status === 422) {
                setErrors((err.response?.data?.errors as Record<string, string[]>) || {});
            } else {
                toast.error.fromResponse(error);
            }
        } else {
            toast.error.action('Failed to save menu');
        }
    } finally {
        saving.value = false;
    }
};
</script>
