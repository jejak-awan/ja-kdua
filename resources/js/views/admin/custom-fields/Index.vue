<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.developer.custom_fields.title') }}</h1>
            <div class="space-x-2">
                <button
                    @click="showCreateGroupModal = true"
                    class="inline-flex items-center px-4 py-2 border border-input bg-card text-foreground rounded-md hover:bg-muted"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    {{ t('features.developer.custom_fields.create_group') }}
                </button>
                <button
                    @click="showCreateFieldModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('features.developer.custom_fields.create_field') }}
                </button>
            </div>
        </div>

        <div class="bg-card shadow rounded-lg">
            <div class="border-b border-border">
                <nav class="-mb-px flex px-6" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        @click="currentTab = tab.name"
                        :class="[
                            currentTab === tab.name
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Field Groups Tab -->
            <div v-if="currentTab === 'groups'" class="p-6">
                <div v-if="loadingGroups" class="text-center">
                    <p class="text-muted-foreground">{{ t('features.developer.webhooks.loading') }}</p>
                </div>
                <div v-else-if="fieldGroups.length === 0" class="text-center">
                    <p class="text-muted-foreground">{{ t('features.developer.custom_fields.groups.empty') }}</p>
                </div>
                <table v-else class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.groups.table.name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.groups.table.fields') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.groups.table.attached_to') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.groups.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        <tr v-for="group in fieldGroups" :key="group.id" class="hover:bg-muted">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-foreground">{{ group.name }}</div>
                                <div class="text-sm text-muted-foreground">{{ group.description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ group.fields_count || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ group.attachable_type ? group.attachable_type.split('\\').pop() : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="editGroup(group)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ t('features.developer.custom_fields.groups.actions.edit') }}
                                    </button>
                                    <button
                                        @click="deleteGroup(group)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        {{ t('features.developer.custom_fields.groups.actions.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Custom Fields Tab -->
            <div v-else class="p-6">
                <!-- Filters -->
                <div class="mb-4">
                    <input
                        v-model="fieldSearch"
                        type="text"
                        :placeholder="t('features.developer.custom_fields.fields.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-64"
                    >
                </div>

                <div v-if="loadingFields" class="text-center">
                    <p class="text-muted-foreground">{{ t('features.developer.webhooks.loading') }}</p>
                </div>
                <div v-else-if="filteredFields.length === 0" class="text-center">
                    <p class="text-muted-foreground">{{ t('features.developer.custom_fields.fields.empty') }}</p>
                </div>
                <table v-else class="min-w-full divide-y divide-border">
                    <thead class="bg-muted">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.fields.table.label') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.fields.table.name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.fields.table.type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.fields.table.group') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {{ t('features.developer.custom_fields.fields.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        <tr v-for="field in filteredFields" :key="field.id" class="hover:bg-muted">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                                {{ field.label }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-muted-foreground">
                                {{ field.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground capitalize">
                                {{ field.type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                {{ field.field_group?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="editField(field)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ t('features.developer.custom_fields.fields.actions.edit') }}
                                    </button>
                                    <button
                                        @click="deleteField(field)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        {{ t('features.developer.custom_fields.fields.actions.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modals -->
        <FieldGroupModal
            v-if="showCreateGroupModal || showEditGroupModal"
            @close="closeGroupModal"
            @saved="handleGroupSaved"
            :field-group="editingGroup"
        />

        <FieldModal
            v-if="showCreateFieldModal || showEditFieldModal"
            @close="closeFieldModal"
            @saved="handleFieldSaved"
            :field="editingField"
            :field-groups="fieldGroups"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import FieldGroupModal from '../../../components/custom-fields/FieldGroupModal.vue';
import FieldModal from '../../../components/custom-fields/FieldModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();

const currentTab = ref('groups');
const tabs = computed(() => [
    { name: 'groups', label: t('features.developer.custom_fields.tabs.groups') },
    { name: 'fields', label: t('features.developer.custom_fields.tabs.fields') },
]);

const fieldGroups = ref([]);
const customFields = ref([]);
const loadingGroups = ref(false);
const loadingFields = ref(false);
const fieldSearch = ref('');

// Modals state
const showCreateGroupModal = ref(false);
const showEditGroupModal = ref(false);
const editingGroup = ref(null);
const showCreateFieldModal = ref(false);
const showEditFieldModal = ref(false);
const editingField = ref(null);

const filteredFields = computed(() => {
    if (!fieldSearch.value) return customFields.value;
    
    const searchLower = fieldSearch.value.toLowerCase();
    return customFields.value.filter(field => 
        field.label.toLowerCase().includes(searchLower) ||
        field.name.toLowerCase().includes(searchLower)
    );
});

const fetchFieldGroups = async () => {
    loadingGroups.value = true;
    try {
        const response = await api.get('/admin/cms/field-groups');
        const { data } = parseResponse(response);
        fieldGroups.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch field groups:', error);
    } finally {
        loadingGroups.value = false;
    }
};

const fetchCustomFields = async () => {
    loadingFields.value = true;
    try {
        const response = await api.get('/admin/cms/custom-fields');
        const { data } = parseResponse(response);
        customFields.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch custom fields:', error);
    } finally {
        loadingFields.value = false;
    }
};

// Group Actions
const editGroup = (group) => {
    editingGroup.value = group;
    showEditGroupModal.value = true;
};

const deleteGroup = async (group) => {
    if (!confirm(t('features.developer.custom_fields.groups.confirm.delete', { name: group.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/field-groups/${group.id}`);
        await fetchFieldGroups();
    } catch (error) {
        console.error('Failed to delete field group:', error);
        alert(t('features.developer.custom_fields.groups.messages.delete_failed'));
    }
};

const closeGroupModal = () => {
    showCreateGroupModal.value = false;
    showEditGroupModal.value = false;
    editingGroup.value = null;
};

const handleGroupSaved = () => {
    fetchFieldGroups();
    closeGroupModal();
};

// Field Actions
const editField = (field) => {
    editingField.value = field;
    showEditFieldModal.value = true;
};

const deleteField = async (field) => {
    if (!confirm(t('features.developer.custom_fields.fields.confirm.delete', { label: field.label }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/custom-fields/${field.id}`);
        await fetchCustomFields();
    } catch (error) {
        console.error('Failed to delete custom field:', error);
        alert(t('features.developer.custom_fields.fields.messages.delete_failed'));
    }
};

const closeFieldModal = () => {
    showCreateFieldModal.value = false;
    showEditFieldModal.value = false;
    editingField.value = null;
};

const handleFieldSaved = () => {
    fetchCustomFields();
    closeFieldModal();
};

onMounted(() => {
    fetchFieldGroups();
    fetchFields();
});
</script>

