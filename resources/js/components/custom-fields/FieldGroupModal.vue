<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>
                    {{ fieldGroup ? t('features.developer.custom_fields.groups.modal.title_edit') : t('features.developer.custom_fields.groups.modal.title_create') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">
                        {{ t('features.developer.custom_fields.groups.modal.name_label') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        :placeholder="t('features.developer.custom_fields.groups.modal.name_placeholder')"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="description">
                        {{ t('features.developer.custom_fields.groups.modal.description_label') }}
                    </Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        :placeholder="t('features.developer.custom_fields.groups.modal.description_placeholder')"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="attachable_type">
                        {{ t('features.developer.custom_fields.groups.modal.attach_label') }}
                    </Label>
                    <Select v-model="form.attachable_type">
                        <SelectTrigger id="attachable_type">
                            <SelectValue :placeholder="t('features.developer.custom_fields.groups.modal.attach_options.none')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">{{ t('features.developer.custom_fields.groups.modal.attach_options.none') }}</SelectItem>
                            <SelectItem value="App\\Models\\Content">{{ t('features.developer.custom_fields.groups.modal.attach_options.content') }}</SelectItem>
                            <SelectItem value="App\\Models\\Category">{{ t('features.developer.custom_fields.groups.modal.attach_options.category') }}</SelectItem>
                            <SelectItem value="App\\Models\\Media">{{ t('features.developer.custom_fields.groups.modal.attach_options.media') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DialogFooter>
                    <Button variant="outline" type="button" @click="$emit('close')">
                        {{ t('features.developer.custom_fields.groups.modal.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="saving">
                        <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ saving ? t('features.developer.custom_fields.groups.modal.saving') : (fieldGroup ? t('features.developer.custom_fields.groups.modal.update') : t('features.developer.custom_fields.groups.modal.create')) }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import Dialog from '../../components/ui/dialog.vue';
import DialogContent from '../../components/ui/dialog-content.vue';
import DialogHeader from '../../components/ui/dialog-header.vue';
import DialogTitle from '../../components/ui/dialog-title.vue';
import DialogFooter from '../../components/ui/dialog-footer.vue';
import Button from '../../components/ui/button.vue';
import Input from '../../components/ui/input.vue';
import Label from '../../components/ui/label.vue';
import Textarea from '../../components/ui/textarea.vue';
import Select from '../../components/ui/select.vue';
import SelectTrigger from '../../components/ui/select-trigger.vue';
import SelectValue from '../../components/ui/select-value.vue';
import SelectContent from '../../components/ui/select-content.vue';
import SelectItem from '../../components/ui/select-item.vue';
import { Loader2 } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    fieldGroup: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    name: '',
    description: '',
    attachable_type: null,
});

const loadFieldGroup = () => {
    if (props.fieldGroup) {
        form.value = {
            name: props.fieldGroup.name || '',
            description: props.fieldGroup.description || '',
            attachable_type: props.fieldGroup.attachable_type || null,
        };
    }
};

const toast = useToast();

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.fieldGroup) {
            await api.put(`/admin/cms/field-groups/${props.fieldGroup.id}`, form.value);
            toast.success.update(t('features.developer.custom_fields.tabs.groups'));
        } else {
            await api.post('/admin/cms/field-groups', form.value);
            toast.success.create(t('features.developer.custom_fields.tabs.groups'));
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save field group:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadFieldGroup();
});
</script>

