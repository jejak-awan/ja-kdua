<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Custom Fields</h1>
            <div class="flex items-center space-x-2">
                <button
                    @click="showFieldGroupModal = true"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Field Group
                </button>
                <button
                    @click="showFieldModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Field
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'groups'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'groups'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Field Groups
                    </button>
                    <button
                        @click="activeTab = 'fields'"
                        :class="[
                            'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'fields'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Custom Fields
                    </button>
                </nav>
            </div>

            <!-- Field Groups Tab -->
            <div v-if="activeTab === 'groups'" class="p-6">
                <div v-if="loading" class="text-center py-8">
                    <p class="text-gray-500">Loading...</p>
                </div>
                <div v-else-if="fieldGroups.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No field groups found</p>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fields
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Attached To
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="group in fieldGroups" :key="group.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ group.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ group.fields_count || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ group.attachable_type || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="editFieldGroup(group)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteFieldGroup(group)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Custom Fields Tab -->
            <div v-if="activeTab === 'fields'" class="p-6">
                <div class="mb-4">
                    <input
                        v-model="fieldSearch"
                        type="text"
                        placeholder="Search fields..."
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>
                <div v-if="loading" class="text-center py-8">
                    <p class="text-gray-500">Loading...</p>
                </div>
                <div v-else-if="filteredFields.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No custom fields found</p>
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Label
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Group
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="field in filteredFields" :key="field.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ field.label }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">{{ field.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ field.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ field.field_group?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="editField(field)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteField(field)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Field Group Modal -->
        <FieldGroupModal
            v-if="showFieldGroupModal || showEditFieldGroupModal"
            @close="closeFieldGroupModal"
            @saved="handleFieldGroupSaved"
            :field-group="editingFieldGroup"
        />

        <!-- Field Modal -->
        <FieldModal
            v-if="showFieldModal || showEditFieldModal"
            @close="closeFieldModal"
            @saved="handleFieldSaved"
            :field="editingField"
            :field-groups="fieldGroups"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../../services/api';
import FieldGroupModal from '../../../components/custom-fields/FieldGroupModal.vue';
import FieldModal from '../../../components/custom-fields/FieldModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const fieldGroups = ref([]);
const fields = ref([]);
const loading = ref(false);
const activeTab = ref('groups');
const fieldSearch = ref('');
const showFieldGroupModal = ref(false);
const showEditFieldGroupModal = ref(false);
const editingFieldGroup = ref(null);
const showFieldModal = ref(false);
const showEditFieldModal = ref(false);
const editingField = ref(null);

const filteredFields = computed(() => {
    if (!Array.isArray(fields.value)) {
        return [];
    }
    if (!fieldSearch.value) return fields.value;
    
    const searchLower = fieldSearch.value.toLowerCase();
    return fields.value.filter(field => 
        field?.label?.toLowerCase().includes(searchLower) ||
        field?.name?.toLowerCase().includes(searchLower) ||
        field?.type?.toLowerCase().includes(searchLower)
    );
});

const fetchFieldGroups = async () => {
    try {
        const response = await api.get('/admin/cms/field-groups');
        const { data } = parseResponse(response);
        fieldGroups.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch field groups:', error);
        fieldGroups.value = [];
    }
};

const fetchFields = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/custom-fields');
        const { data } = parseResponse(response);
        fields.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch fields:', error);
    } finally {
        loading.value = false;
    }
};

const editFieldGroup = (group) => {
    editingFieldGroup.value = group;
    showEditFieldGroupModal.value = true;
};

const deleteFieldGroup = async (group) => {
    if (!confirm(`Are you sure you want to delete field group "${group.name}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/field-groups/${group.id}`);
        await fetchFieldGroups();
    } catch (error) {
        console.error('Failed to delete field group:', error);
        alert('Failed to delete field group');
    }
};

const editField = (field) => {
    editingField.value = field;
    showEditFieldModal.value = true;
};

const deleteField = async (field) => {
    if (!confirm(`Are you sure you want to delete field "${field.label}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/custom-fields/${field.id}`);
        await fetchFields();
    } catch (error) {
        console.error('Failed to delete field:', error);
        alert('Failed to delete field');
    }
};

const closeFieldGroupModal = () => {
    showFieldGroupModal.value = false;
    showEditFieldGroupModal.value = false;
    editingFieldGroup.value = null;
};

const handleFieldGroupSaved = () => {
    fetchFieldGroups();
    closeFieldGroupModal();
};

const closeFieldModal = () => {
    showFieldModal.value = false;
    showEditFieldModal.value = false;
    editingField.value = null;
};

const handleFieldSaved = () => {
    fetchFields();
    closeFieldModal();
};

onMounted(() => {
    fetchFieldGroups();
    fetchFields();
});
</script>

