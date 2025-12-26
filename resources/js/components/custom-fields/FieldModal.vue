<template>
    <Dialog :open="true" @update:open="$emit('close')">
        <DialogContent class="max-w-2xl max-h-[90vh] flex flex-col p-0">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle>
                    {{ field ? t('features.developer.custom_fields.fields.modal.title_edit') : t('features.developer.custom_fields.fields.modal.title_create') }}
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="flex-1 overflow-y-auto p-6 pt-2 space-y-4">
                <div class="space-y-2">
                    <Label for="label">
                        {{ t('features.developer.custom_fields.fields.modal.label_label') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="label"
                        v-model="form.label"
                        required
                        @input="generateName"
                        :placeholder="t('features.developer.custom_fields.fields.modal.label_placeholder')"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="name">
                        {{ t('features.developer.custom_fields.fields.modal.name_label') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        class="font-mono text-xs"
                        :placeholder="t('features.developer.custom_fields.fields.modal.name_placeholder')"
                    />
                    <p class="text-[10px] text-muted-foreground">{{ t('features.developer.custom_fields.fields.modal.name_help') }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="type">
                        {{ t('features.developer.custom_fields.fields.modal.type_label') }} <span class="text-destructive">*</span>
                    </Label>
                    <Select v-model="form.type" required>
                        <SelectTrigger id="type">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="text">Text</SelectItem>
                            <SelectItem value="textarea">Textarea</SelectItem>
                            <SelectItem value="number">Number</SelectItem>
                            <SelectItem value="email">Email</SelectItem>
                            <SelectItem value="url">URL</SelectItem>
                            <SelectItem value="date">Date</SelectItem>
                            <SelectItem value="datetime">DateTime</SelectItem>
                            <SelectItem value="boolean">Boolean</SelectItem>
                            <SelectItem value="select">Select</SelectItem>
                            <SelectItem value="multiselect">Multi Select</SelectItem>
                            <SelectItem value="radio">Radio</SelectItem>
                            <SelectItem value="checkbox">Checkbox</SelectItem>
                            <SelectItem value="file">File</SelectItem>
                            <SelectItem value="image">Image</SelectItem>
                            <SelectItem value="json">JSON</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label for="field_group_id">
                        {{ t('features.developer.custom_fields.fields.modal.group_label') }}
                    </Label>
                    <Select v-model="form.field_group_id">
                        <SelectTrigger id="field_group_id">
                            <SelectValue :placeholder="t('features.developer.custom_fields.fields.modal.group_none')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">{{ t('features.developer.custom_fields.fields.modal.group_none') }}</SelectItem>
                            <SelectItem
                                v-for="group in fieldGroups"
                                :key="group.id"
                                :value="group.id"
                            >
                                {{ group.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label for="default_value">
                        {{ t('features.developer.custom_fields.fields.modal.default_label') }}
                    </Label>
                    <Input
                        id="default_value"
                        v-model="form.default_value"
                        :placeholder="t('features.developer.custom_fields.fields.modal.default_placeholder')"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="options">
                        {{ t('features.developer.custom_fields.fields.modal.options_label') }}
                    </Label>
                    <Textarea
                        id="options"
                        v-model="form.options"
                        rows="3"
                        class="font-mono text-xs"
                        :placeholder="t('features.developer.custom_fields.fields.modal.options_placeholder')"
                    />
                    <p class="text-[10px] text-muted-foreground">{{ t('features.developer.custom_fields.fields.modal.options_help') }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="instructions">
                        {{ t('features.developer.custom_fields.fields.modal.instructions_label') }}
                    </Label>
                    <Textarea
                        id="instructions"
                        v-model="form.instructions"
                        rows="2"
                        :placeholder="t('features.developer.custom_fields.fields.modal.instructions_placeholder')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_required" :checked="form.is_required" @update:checked="v => form.is_required = v" />
                        <Label for="is_required" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ t('features.developer.custom_fields.fields.modal.required_label') }}
                        </Label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_searchable" :checked="form.is_searchable" @update:checked="v => form.is_searchable = v" />
                        <Label for="is_searchable" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ t('features.developer.custom_fields.fields.modal.searchable_label') }}
                        </Label>
                    </div>
                </div>
            </form>

            <DialogFooter class="p-6 pt-0">
                <Button variant="outline" type="button" @click="$emit('close')">
                    {{ t('features.developer.custom_fields.fields.modal.cancel') }}
                </Button>
                <Button type="submit" :disabled="saving" @click="handleSubmit">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? t('features.developer.custom_fields.fields.modal.saving') : (field ? t('features.developer.custom_fields.fields.modal.update') : t('features.developer.custom_fields.fields.modal.create')) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
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
import Checkbox from '../../components/ui/checkbox.vue';
import { Loader2 } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    field: {
        type: Object,
        default: null,
    },
    fieldGroups: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);

const form = ref({
    label: '',
    name: '',
    type: 'text',
    field_group_id: null,
    default_value: '',
    options: '',
    instructions: '',
    is_required: false,
    is_searchable: false,
});

const generateName = () => {
    if (!form.value.name || form.value.name === slugify(form.value.label)) {
        form.value.name = slugify(form.value.label);
    }
};

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '_')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '_')
        .replace(/^_+/, '')
        .replace(/_+$/, '');
};

const loadField = () => {
    if (props.field) {
        form.value = {
            label: props.field.label || '',
            name: props.field.name || '',
            type: props.field.type || 'text',
            field_group_id: props.field.field_group_id || null,
            default_value: props.field.default_value || '',
            options: Array.isArray(props.field.options) ? props.field.options.join(',') : (props.field.options || ''),
            instructions: props.field.instructions || '',
            is_required: props.field.is_required || false,
            is_searchable: props.field.is_searchable || false,
        };
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            ...form.value,
            options: form.value.options ? form.value.options.split(',').map(o => o.trim()) : [],
        };
        
        if (props.field) {
            await api.put(`/admin/cms/custom-fields/${props.field.id}`, payload);
        } else {
            await api.post('/admin/cms/custom-fields', payload);
        }
        emit('saved');
    } catch (error) {
        console.error('Failed to save field:', error);
        alert(error.response?.data?.message || t('features.developer.custom_fields.fields.messages.save_failed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadField();
});
</script>

