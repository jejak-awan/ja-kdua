<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.forms.modal.createTitle') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.forms.title') }}</p>
            </div>
            <router-link
                :to="{ name: 'forms' }"
                class="inline-flex items-center px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
            >
                {{ $t('common.actions.back') }}
            </router-link>
        </div>

        <!-- Form -->
        <div class="bg-card border border-border rounded-lg overflow-hidden">
            <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.formName') }} <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="formData.name"
                            type="text"
                            required
                            @input="generateSlug"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.forms.modal.placeholders.name')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.slug') }} <span class="text-destructive">*</span>
                        </label>
                        <input
                            v-model="formData.slug"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.forms.modal.placeholders.slug')"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.modal.description') }}
                    </label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.forms.modal.placeholders.description')"
                    />
                </div>

                <!-- Success Message & Redirect -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.successMessage') }}
                        </label>
                        <input
                            v-model="formData.success_message"
                            type="text"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.forms.modal.placeholders.successMessage')"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.redirectUrl') }}
                        </label>
                        <input
                            v-model="formData.redirect_url"
                            type="url"
                            class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            :placeholder="$t('features.forms.modal.placeholders.redirectUrl')"
                        >
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <input
                        v-model="formData.is_active"
                        type="checkbox"
                        id="is_active"
                        class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-foreground">
                        {{ $t('features.forms.modal.isActive') }}
                    </label>
                </div>

                <!-- Fields Builder -->
                <div class="border-t border-border pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-foreground">{{ $t('features.forms.modal.formFields') }}</h3>
                        <button
                            type="button"
                            @click="showFieldModal = true; editingField = null"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ $t('features.forms.actions.addField') }}
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
                    <router-link
                        :to="{ name: 'forms' }"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium hover:bg-muted"
                    >
                        {{ $t('common.actions.cancel') }}
                    </router-link>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-primary-foreground bg-primary hover:bg-primary/80 disabled:opacity-50"
                    >
                        <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ saving ? $t('common.messages.loading.creating') : $t('features.forms.actions.createForm') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Field Modal -->
        <FieldModal
            v-if="showFieldModal"
            :field="editingField"
            @close="showFieldModal = false; editingField = null"
            @save="handleFieldSave"
        />
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import FieldBuilder from '../../../components/forms/FieldBuilder.vue';
import FieldModal from '../../../components/forms/FieldModal.vue';

const { t } = useI18n();
const router = useRouter();

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

const generateSlug = () => {
    if (!formData.slug || formData.slug === slugify(formData.name)) {
        formData.slug = slugify(formData.name);
    }
};

const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const payload = {
            name: formData.name,
            slug: formData.slug,
            description: formData.description,
            success_message: formData.success_message,
            redirect_url: formData.redirect_url,
            is_active: formData.is_active,
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
        await api.post('/admin/cms/forms', payload);
        router.push({ name: 'forms' });
    } catch (error) {
        console.error('Failed to create form:', error);
        alert(error.response?.data?.message || t('features.forms.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
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
        const index = formData.fields.findIndex(f => f.id === field.id);
        if (index !== -1) {
            formData.fields[index] = field;
        }
    } else {
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
