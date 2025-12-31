<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
                <DialogTitle>
                    {{ widget ? $t('features.widgets.modals.widget.titleEdit') : $t('features.widgets.modals.widget.titleCreate') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.title') }} <span class="text-red-500">*</span></Label>
                    <Input v-model="form.title" type="text" required />
                    <span v-if="errors.title" class="text-xs text-destructive">{{ errors.title }}</span>
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.type') }} <span class="text-red-500">*</span></Label>
                    <Select v-model="form.type">
                        <SelectTrigger>
                            <SelectValue :placeholder="$t('features.widgets.modals.widget.type')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="text">Text</SelectItem>
                            <SelectItem value="html">HTML</SelectItem>
                            <SelectItem value="recent_posts">Recent Posts</SelectItem>
                            <SelectItem value="categories">Categories</SelectItem>
                            <SelectItem value="custom">Custom</SelectItem>
                        </SelectContent>
                    </Select>
                    <span v-if="errors.type" class="text-xs text-destructive">{{ errors.type }}</span>
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.location') }}</Label>
                    <Input v-model="form.location" type="text" :placeholder="$t('features.widgets.modals.widget.positionPlaceholder')" />
                    <span v-if="errors.location" class="text-xs text-destructive">{{ errors.location }}</span>
                </div>
                <div class="space-y-2">
                    <Label>{{ $t('features.widgets.modals.widget.content') }}</Label>
                    <Textarea v-model="form.content" :rows="6" />
                    <span v-if="errors.content" class="text-xs text-destructive">{{ errors.content }}</span>
                </div>
                <div class="flex items-center space-x-2 pt-2">
                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                    <Label for="is_active" class="text-sm font-normal cursor-pointer">
                        {{ $t('features.widgets.modals.widget.active') }}
                    </Label>
                </div>
            </form>

            <DialogFooter>
                <Button variant="outline" @click="$emit('close')">
                    {{ $t('common.actions.cancel') }}
                </Button>
                <Button @click="handleSubmit" :disabled="isSubmitting">
                    <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                    {{ isSubmitting ? $t('features.widgets.modals.widget.saving') : (widget ? $t('common.actions.update') : $t('common.actions.create')) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { onMounted, ref } from 'vue';
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
import Textarea from '../ui/textarea.vue';
import Checkbox from '../ui/checkbox.vue';
import Select from '../ui/select.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';
import { useToast } from '../../composables/useToast';
import { useFormValidation } from '../../composables/useFormValidation';
import { widgetSchema } from '../../schemas/common';

const props = defineProps({ widget: { type: Object, default: null } });
const emit = defineEmits(['close', 'saved']);

const { t } = useI18n();
const toast = useToast();
const isSubmitting = ref(false);

const { errors, validateWithZod, setErrors } = useFormValidation(widgetSchema);

const form = ref({
    title: '',
    type: 'text',
    location: '',
    content: '',
    is_active: true
});

const loadWidget = () => {
    if (props.widget) {
        form.value = { ...props.widget, is_active: !!props.widget.is_active };
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;
    isSubmitting.value = true;

    try {
        if (props.widget) {
            await api.put(`/admin/cms/widgets/${props.widget.id}`, form.value);
            toast.success.update(t('features.widgets.title'));
        } else {
            await api.post('/admin/cms/widgets', form.value);
            toast.success.create(t('features.widgets.title'));
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
    loadWidget();
});
</script>

