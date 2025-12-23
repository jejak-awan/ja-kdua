<template>
    <div class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-card max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-foreground">
                    {{ form ? t('features.forms.modal.editTitle') : t('features.forms.modal.createTitle') }}
                </h2>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-muted-foreground"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="saveForm" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.forms.modal.formName') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.forms.modal.placeholders.name')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.forms.modal.slug') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.forms.modal.placeholders.slug')"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ t('features.forms.modal.description') }}
                    </label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="t('features.forms.modal.placeholders.description')"
                    />
                </div>

                <!-- Success Message & Redirect -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.forms.modal.successMessage') }}
                        </label>
                        <input
                            v-model="formData.success_message"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.forms.modal.placeholders.successMessage')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ t('features.forms.modal.redirectUrl') }}
                        </label>
                        <input
                            v-model="formData.redirect_url"
                            type="url"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            :placeholder="t('features.forms.modal.placeholders.redirectUrl')"
                        >
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <input
                        v-model="formData.is_active"
                        type="checkbox"
                        id="is_active"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-input rounded"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-foreground">
                        {{ t('features.forms.modal.isActive') }}
                    </label>
                </div>

                <!-- Fields Builder -->
                <div class="border-t border-border pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-foreground">{{ t('features.forms.modal.formFields') }}</h3>
                        <button
                            type="button"
                            @click="showFieldModal = true; editingField = null"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ t('features.forms.actions.addField') }}
                        </button>
                    </div>

                    <FieldBuilder
                        :fields="formData.fields || []"
                        @add-field="handleAddField"
                        @update-field="handleUpdateField"
                        @delete-field="handleDeleteField"
                        @reorder-fields="handleReorderFields"
                    />
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-border">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md shadow-sm text-sm font-medium text-foreground bg-card hover:bg-muted"
                    >
                        {{ t('features.forms.actions.cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? t('common.messages.loading.saving') : (form ? t('features.forms.actions.update') : t('features.forms.actions.createForm')) }}
                    </button>
                </div>
            </form>

            <!-- Field Modal -->
            <FieldModal
                v-if="showFieldModal"
                :field="editingField"
                @close="showFieldModal = false; editingField = null"
                @save="handleFieldSave"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import FieldBuilder from './FieldBuilder.vue';
import FieldModal from './FieldModal.vue';

const { t } = useI18n();

const props = defineProps({
    form: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const showFieldModal = ref(false);
const editingField = ref(null);

const formData = reactive({
    name: '',
    slug: '',
    description: '',
    success_message: '',
    redirect_url: '',
    is_active: true,
    fields: []
});

watch(() => props.form, (newForm) => {
    if (newForm) {
        formData.name = newForm.name || '';
        formData.slug = newForm.slug || '';
        formData.description = newForm.description || '';
        formData.success_message = newForm.success_message || '';
        formData.redirect_url = newForm.redirect_url || '';
        formData.is_active = newForm.is_active ?? true;
        formData.fields = newForm.fields ? [...newForm.fields] : [];
    } else {
        // Reset form
        formData.name = '';
        formData.slug = '';
        formData.description = '';
        formData.success_message = '';
        formData.redirect_url = '';
        formData.is_active = true;
        formData.fields = [];
    }
}, { immediate: true });

const saveForm = async () => {
    try {
        saving.value = true;
        
        // Prepare form data (without fields for update)
        const formPayload = {
            name: formData.name,
            slug: formData.slug,
            description: formData.description,
            success_message: formData.success_message,
            redirect_url: formData.redirect_url,
            is_active: formData.is_active
        };

        let response;
        if (props.form) {
            // Update form
            response = await api.put(`/admin/cms/forms/${props.form.id}`, formPayload);
            
            // Update fields separately
            await updateFields(response.data.id);
        } else {
            // Create form with fields
            const payload = {
                ...formPayload,
                fields: formData.fields.map((field, index) => ({
                    name: field.name,
                    label: field.label,
                    type: field.type,
                    placeholder: field.placeholder,
                    help_text: field.help_text,
                    options: field.options || [],
                    is_required: field.is_required || false,
                    sort_order: index
                }))
            };
            response = await api.post('/admin/cms/forms', payload);
        }

        // Reload form with fields
        const formResponse = await api.get(`/admin/cms/forms/${response.data.id}`);
        emit('saved', formResponse.data);
    } catch (error) {
        console.error('Error saving form:', error);
        alert(t('features.forms.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

const updateFields = async (formId) => {
    // Get existing fields from API
    const formResponse = await api.get(`/admin/cms/forms/${formId}`);
    const existingFields = formResponse.data.fields || [];
    
    // Delete fields that are no longer in formData.fields
    for (const existingField of existingFields) {
        if (!formData.fields.find(f => f.id === existingField.id)) {
            try {
                await api.delete(`/admin/cms/forms/${formId}/fields/${existingField.id}`);
            } catch (error) {
                console.error('Error deleting field:', error);
            }
        }
    }
    
    // Add or update fields
    for (let index = 0; index < formData.fields.length; index++) {
        const field = formData.fields[index];
        const fieldPayload = {
            name: field.name,
            label: field.label,
            type: field.type,
            placeholder: field.placeholder,
            help_text: field.help_text,
            options: field.options || [],
            is_required: field.is_required || false,
            sort_order: index
        };
        
        if (field.id && !field.id.toString().startsWith('temp-')) {
            // Update existing field
            try {
                await api.put(`/admin/cms/forms/${formId}/fields/${field.id}`, fieldPayload);
            } catch (error) {
                console.error('Error updating field:', error);
            }
        } else {
            // Add new field
            try {
                await api.post(`/admin/cms/forms/${formId}/fields`, fieldPayload);
            } catch (error) {
                console.error('Error adding field:', error);
            }
        }
    }
    
    // Reorder fields
    try {
        const reorderPayload = {
            fields: formData.fields
                .filter(f => f.id && !f.id.toString().startsWith('temp-'))
                .map((field, index) => ({
                    id: field.id,
                    sort_order: index
                }))
        };
        if (reorderPayload.fields.length > 0) {
            await api.post(`/admin/cms/forms/${formId}/reorder-fields`, reorderPayload);
        }
    } catch (error) {
        console.error('Error reordering fields:', error);
    }
};

const close = () => {
    emit('close');
};

const handleAddField = () => {
    showFieldModal.value = true;
    editingField.value = null;
};

const handleUpdateField = (field) => {
    editingField.value = { ...field };
    showFieldModal.value = true;
};

const handleDeleteField = (fieldId) => {
    formData.fields = formData.fields.filter(f => f.id !== fieldId);
};

const handleReorderFields = (fields) => {
    formData.fields = fields;
};

const handleFieldSave = (field) => {
    if (field.id) {
        // Update existing field
        const index = formData.fields.findIndex(f => f.id === field.id);
        if (index !== -1) {
            formData.fields[index] = field;
        }
    } else {
        // Add new field
        const newField = {
            ...field,
            id: `temp-${Date.now()}`,
            sort_order: formData.fields.length
        };
        formData.fields.push(newField);
    }
    showFieldModal.value = false;
    editingField.value = null;
};
</script>

