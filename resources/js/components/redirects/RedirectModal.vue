<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>
                    {{ redirect ? $t('features.redirects.modals.redirect.titleEdit') : $t('features.redirects.modals.redirect.titleCreate') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label>
                        {{ $t('features.redirects.modals.redirect.from') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        v-model="form.from_url"
                        required
                        :placeholder="$t('features.redirects.modals.redirect.fromPlaceholder')"
                    />
                    <span v-if="errors.from_url" class="text-xs text-destructive">{{ errors.from_url[0] }}</span>
                    <p class="text-xs text-muted-foreground">{{ $t('features.redirects.modals.redirect.fromHint') }}</p>
                </div>

                <div class="space-y-2">
                    <Label>
                        {{ $t('features.redirects.modals.redirect.to') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        v-model="form.to_url"
                        required
                        :placeholder="$t('features.redirects.modals.redirect.toPlaceholder')"
                    />
                    <span v-if="errors.to_url" class="text-xs text-destructive">{{ errors.to_url[0] }}</span>
                    <p class="text-xs text-muted-foreground">{{ $t('features.redirects.modals.redirect.toHint') }}</p>
                </div>

                <div class="space-y-2">
                    <Label>
                        {{ $t('features.redirects.modals.redirect.code') }} <span class="text-destructive">*</span>
                    </Label>
                    <Select v-model="form.status_code">
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="301">{{ $t('features.redirects.modals.redirect.codes.p301') }}</SelectItem>
                            <SelectItem :value="302">{{ $t('features.redirects.modals.redirect.codes.t302') }}</SelectItem>
                            <SelectItem :value="307">{{ $t('features.redirects.modals.redirect.codes.t307') }}</SelectItem>
                            <SelectItem :value="308">{{ $t('features.redirects.modals.redirect.codes.p308') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <Checkbox
                        v-model:checked="form.is_active"
                        id="is_active"
                    />
                    <Label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        {{ $t('features.redirects.modals.redirect.active') }}
                    </Label>
                </div>
            </form>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="$emit('close')"
                >
                    {{ $t('common.actions.cancel') }}
                </Button>
                <Button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                >
                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                    {{ isSubmitting ? $t('features.redirects.modals.redirect.saving') : (redirect ? $t('common.actions.update') : $t('common.actions.create')) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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
import Checkbox from '../ui/checkbox.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';
import { useToast } from '../../composables/useToast';
import { useFormValidation } from '../../composables/useFormValidation';
import { redirectSchema } from '../../schemas/common';

const props = defineProps({
    redirect: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);
const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(redirectSchema);

const form = ref({
    from_url: '',
    to_url: '',
    status_code: 301,
    is_active: true,
});

const loadRedirect = () => {
    if (props.redirect) {
        form.value = {
            from_url: props.redirect.from_url || '',
            to_url: props.redirect.to_url || '',
            status_code: props.redirect.status_code || 301,
            is_active: props.redirect.is_active !== undefined ? props.redirect.is_active : true,
        };
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    isSubmitting.value = true;
    try {
        if (props.redirect) {
            await api.put(`/admin/cms/redirects/${props.redirect.id}`, form.value);
            toast.success.update(t('features.redirects.title'));
        } else {
            await api.post('/admin/cms/redirects', form.value);
            toast.success.create(t('features.redirects.title'));
        }
        emit('saved');
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors);
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    loadRedirect();
});
</script>

