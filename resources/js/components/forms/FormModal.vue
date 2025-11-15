<template>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="close">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ form ? 'Edit Form' : 'Create New Form' }}
                </h2>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600"
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Form Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Contact Form"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="contact-form"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Form description..."
                    ></textarea>
                </div>

                <!-- Success Message & Redirect -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Success Message
                        </label>
                        <input
                            v-model="formData.success_message"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Thank you for your submission!"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Redirect URL
                        </label>
                        <input
                            v-model="formData.redirect_url"
                            type="url"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="https://example.com/thank-you"
                        />
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <input
                        v-model="formData.is_active"
                        type="checkbox"
                        id="is_active"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Form is active
                    </label>
                </div>

                <!-- Fields Builder -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Form Fields</h3>
                        <button
                            type="button"
                            @click="showFieldModal = true; editingField = null"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Field
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
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving...' : (form ? 'Update Form' : 'Create Form') }}
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
import api from '../../services/api';
import FieldBuilder from './FieldBuilder.vue';
import FieldModal from './FieldModal.vue';

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
        alert('Failed to save form. Please check the console for details.');
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

