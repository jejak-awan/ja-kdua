<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.forms.title') }}</h1>
            <button
                @click="showFormModal = true; editingForm = null"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('features.forms.actions.create') }}
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-card shadow rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="t('features.forms.filters.search')"
                    class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <select
                    v-model="statusFilter"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">{{ t('features.forms.filters.status') }}</option>
                    <option value="active">{{ t('features.forms.filters.active') }}</option>
                    <option value="inactive">{{ t('features.forms.filters.inactive') }}</option>
                </select>
            </div>
        </div>

        <!-- Forms List -->
        <div v-if="loading" class="bg-card shadow rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ t('features.forms.messages.loading') }}</p>
        </div>

        <div v-else-if="filteredForms.length === 0" class="bg-card shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ t('features.forms.messages.empty') }}</p>
            <button
                @click="showFormModal = true; editingForm = null"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                {{ t('features.forms.actions.createFirst') }}
            </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="form in filteredForms"
                :key="form.id"
                class="bg-card shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
            >
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-foreground">{{ form.name }}</h3>
                            <p class="text-sm text-muted-foreground mt-1">{{ form.slug }}</p>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-medium rounded-full',
                                form.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'
                            ]"
                        >
                            {{ form.is_active ? t('features.forms.filters.active') : t('features.forms.filters.inactive') }}
                        </span>
                    </div>

                    <p v-if="form.description" class="text-sm text-muted-foreground mb-4 line-clamp-2">
                        {{ form.description }}
                    </p>

                    <div class="flex items-center justify-between text-sm text-muted-foreground mb-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            {{ t('features.forms.stats.fields', { count: form.fields?.length || 0 }) }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ t('features.forms.stats.submissions', { count: form.submission_count || 0 }) }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-4 border-t border-border">
                        <button
                            @click="editForm(form)"
                            class="flex-1 px-3 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-500/20 rounded-md transition-colors"
                        >
                            {{ t('features.forms.actions.edit') }}
                        </button>
                        <button
                            @click="viewSubmissions(form)"
                            class="flex-1 px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors"
                        >
                            {{ t('features.forms.actions.submissions') }}
                        </button>
                        <button
                            @click="toggleFormStatus(form)"
                            class="px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="form.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button
                            @click="deleteForm(form)"
                            class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-500/20 rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <FormModal
            v-if="showFormModal"
            :form="editingForm"
            @close="showFormModal = false; editingForm = null"
            @saved="handleFormSaved"
        />

        <!-- Submissions View -->
        <Submissions
            v-if="selectedForm"
            :form="selectedForm"
            @close="selectedForm = null"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import FormModal from '../../../components/forms/FormModal.vue';
import Submissions from './Submissions.vue';

const { t } = useI18n();
const forms = ref([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const showFormModal = ref(false);
const editingForm = ref(null);
const selectedForm = ref(null);

const filteredForms = computed(() => {
    let result = forms.value;

    if (search.value) {
        const query = search.value.toLowerCase();
        result = result.filter(form =>
            form.name.toLowerCase().includes(query) ||
            form.slug.toLowerCase().includes(query) ||
            (form.description && form.description.toLowerCase().includes(query))
        );
    }

    if (statusFilter.value) {
        const isActive = statusFilter.value === 'active';
        result = result.filter(form => form.is_active === isActive);
    }

    return result;
});

const fetchForms = async () => {
    try {
        loading.value = true;
        const response = await api.get('/admin/cms/forms');
        const { data } = parseResponse(response);
        forms.value = ensureArray(data);
    } catch (error) {
        console.error('Error fetching forms:', error);
        forms.value = [];
    } finally {
        loading.value = false;
    }
};

const editForm = (form) => {
    editingForm.value = form;
    showFormModal.value = true;
};

const viewSubmissions = (form) => {
    selectedForm.value = form;
};

const toggleFormStatus = async (form) => {
    try {
        const response = await api.put(`/admin/cms/forms/${form.id}`, {
            is_active: !form.is_active
        });
        const updatedForm = response.data;
        const index = forms.value.findIndex(f => f.id === form.id);
        if (index !== -1) {
            forms.value[index] = updatedForm;
        }
    } catch (error) {
        console.error('Error toggling form status:', error);
        alert('Failed to update form status');
    }
};

const deleteForm = async (form) => {
    if (!confirm(t('features.forms.messages.deleteConfirm', { name: form.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/forms/${form.id}`);
        forms.value = forms.value.filter(f => f.id !== form.id);
    } catch (error) {
        console.error('Error deleting form:', error);
        alert('Failed to delete form');
    }
};

const handleFormSaved = (form) => {
    const index = forms.value.findIndex(f => f.id === form.id);
    if (index !== -1) {
        forms.value[index] = form;
    } else {
        forms.value.unshift(form);
    }
    showFormModal.value = false;
    editingForm.value = null;
};

onMounted(() => {
    fetchForms();
});
</script>

