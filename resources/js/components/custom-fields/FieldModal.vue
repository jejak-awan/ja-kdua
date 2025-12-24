<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-card">
                    <h3 class="text-lg font-semibold">
                        {{ field ? t('features.developer.custom_fields.fields.modal.title_edit') : t('features.developer.custom_fields.fields.modal.title_create') }}
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-muted-foreground hover:text-muted-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.label_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.label"
                            type="text"
                            required
                            @input="generateName"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.custom_fields.fields.modal.label_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.name_label') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.developer.custom_fields.fields.modal.name_placeholder')"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.developer.custom_fields.fields.modal.name_help') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.type_label') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.type"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="url">URL</option>
                            <option value="date">Date</option>
                            <option value="datetime">DateTime</option>
                            <option value="boolean">Boolean</option>
                            <option value="select">Select</option>
                            <option value="multiselect">Multi Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="file">File</option>
                            <option value="image">Image</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.group_label') }}
                        </label>
                        <select
                            v-model="form.field_group_id"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">{{ t('features.developer.custom_fields.fields.modal.group_none') }}</option>
                            <option
                                v-for="group in fieldGroups"
                                :key="group.id"
                                :value="group.id"
                            >
                                {{ group.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.default_label') }}
                        </label>
                        <input
                            v-model="form.default_value"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.custom_fields.fields.modal.default_placeholder')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.options_label') }}
                        </label>
                        <textarea
                            v-model="form.options"
                            rows="3"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                            :placeholder="t('features.developer.custom_fields.fields.modal.options_placeholder')"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">{{ t('features.developer.custom_fields.fields.modal.options_help') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.developer.custom_fields.fields.modal.instructions_label') }}
                        </label>
                        <textarea
                            v-model="form.instructions"
                            rows="2"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.developer.custom_fields.fields.modal.instructions_placeholder')"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input
                                v-model="form.is_required"
                                type="checkbox"
                                id="is_required"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                            >
                            <label for="is_required" class="ml-2 block text-sm text-foreground">
                                {{ t('features.developer.custom_fields.fields.modal.required_label') }}
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="form.is_searchable"
                                type="checkbox"
                                id="is_searchable"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                            >
                            <label for="is_searchable" class="ml-2 block text-sm text-foreground">
                                {{ t('features.developer.custom_fields.fields.modal.searchable_label') }}
                            </label>
                        </div>
                    </div>
                </form>

                <div class="flex items-center justify-end space-x-3 p-6 border-t sticky bottom-0 bg-card">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted"
                    >
                        {{ t('features.developer.custom_fields.fields.modal.cancel') }}
                    </button>
                    <button
                        @click="handleSubmit"
                        :disabled="saving"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 disabled:opacity-50"
                    >
                        {{ saving ? t('features.developer.custom_fields.fields.modal.saving') : (field ? t('features.developer.custom_fields.fields.modal.update') : t('features.developer.custom_fields.fields.modal.create')) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';

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

