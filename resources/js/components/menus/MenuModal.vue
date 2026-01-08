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

<script setup>
import { ref, computed, onMounted } from 'vue';

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
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';
import { useToast } from '../../composables/useToast';
import { useFormValidation } from '../../composables/useFormValidation';
import { menuSchema } from '../../schemas';

const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(menuSchema);

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const form = ref({
    name: '',
    location: '',
});

const locationOptions = ref([]);
const loadingLocations = ref(false);

const fetchLocations = async () => {
    loadingLocations.value = true;
    try {
        const response = await api.get('/admin/ja/themes/active/locations');
        const data = response.data?.data || response.data || {};
        
        // Transform { key: label } to [{ value: key, label: label }]
        locationOptions.value = Object.entries(data).map(([key, label]) => ({
            value: key,
            label: label
        }));
    } catch (error) {
        console.error('Failed to fetch menu locations:', error);
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
        const response = await api.post('/admin/ja/menus', form.value);
        const menu = response.data.data || response.data;
        toast.success.create('Menu');
        emit('saved', menu);
        emit('close');
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>

