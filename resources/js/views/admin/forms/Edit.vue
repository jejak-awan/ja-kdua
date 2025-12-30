<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.forms.modal.editTitle') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.forms.title') }}</p>
            </div>
            <router-link
                :to="{ name: 'forms' }"
            >
                <Button variant="ghost" size="sm">
                    ← {{ $t('common.actions.back') }}
                </Button>
            </router-link>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('common.messages.loading.default') }}</p>
        </div>

        <!-- Form -->
        <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
            <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.formName') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="formData.name"
                            type="text"
                            required
                            :placeholder="$t('features.forms.modal.placeholders.name')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.slug') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="formData.slug"
                            type="text"
                            required
                            :placeholder="$t('features.forms.modal.placeholders.slug')"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">
                        {{ $t('features.forms.modal.description') }}
                    </label>
                    <Textarea
                        v-model="formData.description"
                        rows="3"
                        :placeholder="$t('features.forms.modal.placeholders.description')"
                    />
                </div>

                <!-- Success Message & Redirect -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.successMessage') }}
                        </label>
                        <Input
                            v-model="formData.success_message"
                            type="text"
                            :placeholder="$t('features.forms.modal.placeholders.successMessage')"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">
                            {{ $t('features.forms.modal.redirectUrl') }}
                        </label>
                        <Input
                            v-model="formData.redirect_url"
                            type="url"
                            :placeholder="$t('features.forms.modal.placeholders.redirectUrl')"
                        />
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center space-x-2">
                        <Checkbox
                            id="is_active"
                            v-model:checked="formData.is_active"
                        />
                        <label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ $t('features.forms.modal.isActive') }}
                        </label>
                    </div>

                <!-- Fields Builder -->
                <div class="border-t border-border pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-foreground">{{ $t('features.forms.modal.formFields') }}</h3>
                        <Button
                            type="button"
                            @click="showFieldModal = true; editingField = null"
                            size="sm"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ $t('features.forms.actions.addField') }}
                        </Button>
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
                    >
                        <Button variant="outline">
                            {{ $t('common.actions.cancel') }}
                        </Button>
                    </router-link>
                    <Button
                        type="submit"
                        :disabled="saving"
                    >
                        <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ saving ? $t('common.messages.loading.saving') : $t('features.forms.actions.update') }}
                    </Button>
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
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '../../../services/api';
import toast from '../../../services/toast';
import FieldBuilder from '../../../components/forms/FieldBuilder.vue';
import FieldModal from '../../../components/forms/FieldModal.vue';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Textarea from '../../../components/ui/textarea.vue';
import Checkbox from '../../../components/ui/checkbox.vue';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const loading = ref(true);
const saving = ref(false);
const showFieldModal = ref(false);
const editingField = ref(null);
const formId = ref(null);

const formData = reactive({
    name: '',
    slug: '',
    description: '',
    success_message: '',
    redirect_url: '',
    is_active: true,
    fields: []
});

const fetchForm = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/forms/${route.params.id}`);
        const data = response.data?.data || response.data;
        formId.value = data.id;
        Object.assign(formData, {
            name: data.name,
            slug: data.slug,
            description: data.description,
            success_message: data.success_message,
            redirect_url: data.redirect_url,
            is_active: data.is_active,
            fields: data.fields || []
        });
    } catch (error) {
        console.error('Failed to fetch form:', error);
        toast.error('Error', t('features.forms.messages.loadFailed'));
        router.push({ name: 'forms.index' });
    } finally {
        loading.value = false;
    }
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
                id: field.id.toString().startsWith('temp-') ? null : field.id,
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
        await api.put(`/admin/cms/forms/${route.params.id}`, payload);
        toast.success(t('features.forms.messages.updateSuccess'));
        router.push({ name: 'forms.index' });
    } catch (error) {
        console.error('Failed to update form:', error);
        toast.error('Error', error.response?.data?.message || t('features.forms.messages.saveFailed'));
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

onMounted(() => {
    fetchForm();
});
</script>
