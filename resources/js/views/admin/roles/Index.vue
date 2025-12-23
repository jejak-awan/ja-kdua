<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.roles.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.roles.subtitle') }}</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.roles.create') }}
            </button>
        </div>

        <!-- Roles Grid -->
        <div v-if="loading" class="bg-card shadow rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.roles.list.loading') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="role in roles"
                :key="role.id"
                class="bg-card shadow rounded-lg overflow-hidden"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-foreground capitalize">{{ role.name }}</h3>
                        <span class="px-2 py-1 text-xs bg-indigo-500/20 text-indigo-400 rounded-full">
                            {{ $t('features.roles.list.permissionsCount', { count: role.permissions?.length || 0 }) }}
                        </span>
                    </div>
                    <p class="text-sm text-muted-foreground mb-4">
                        {{ $t('features.roles.list.usersCount', { count: role.users_count || 0 }) }}
                    </p>
                    <div class="flex flex-wrap gap-1 mb-4 max-h-20 overflow-y-auto">
                        <span
                            v-for="permission in (role.permissions || []).slice(0, 5)"
                            :key="permission.id"
                            class="px-2 py-1 text-xs bg-secondary text-muted-foreground rounded"
                        >
                            {{ permission.name }}
                        </span>
                        <span v-if="(role.permissions?.length || 0) > 5" class="px-2 py-1 text-xs text-muted-foreground">
                            {{ $t('features.roles.list.more', { count: role.permissions.length - 5 }) }}
                        </span>
                    </div>
                </div>
                <div class="bg-muted px-6 py-3 flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button
                            @click="editRole(role)"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                            :disabled="isProtectedRole(role.name)"
                        >
                            {{ $t('features.roles.actions.edit') }}
                        </button>
                        <button
                            @click="duplicateRole(role)"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            {{ $t('features.roles.actions.duplicate') }}
                        </button>
                    </div>
                    <button
                        v-if="!isProtectedRole(role.name)"
                        @click="deleteRole(role)"
                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                    >
                        {{ $t('features.roles.actions.delete') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal || selectedRole" class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50" @click.self="closeModal">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-card max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-foreground">
                        {{ selectedRole ? $t('features.roles.edit') : $t('features.roles.create') }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-muted-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Role Name -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ $t('features.roles.form.name') }}</label>
                    <input
                        v-model="form.name"
                        type="text"
                        :disabled="selectedRole && isProtectedRole(selectedRole.name)"
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-secondary"
                        :placeholder="$t('features.roles.form.namePlaceholder')"
                    >
                </div>

                <!-- Permissions Matrix -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-foreground mb-4">{{ $t('features.roles.permissions') }}</h4>
                    <div class="space-y-4">
                        <div v-for="(perms, category) in groupedPermissions" :key="category" class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-medium text-foreground capitalize">{{ category }}</h5>
                                <button
                                    @click="toggleCategory(category)"
                                    class="text-xs text-indigo-600 hover:text-indigo-800"
                                >
                                    {{ isCategorySelected(category) ? $t('features.roles.form.deselectAll') : $t('features.roles.form.selectAll') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                <label
                                    v-for="permission in perms"
                                    :key="permission.id"
                                    class="flex items-center space-x-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                        class="h-4 w-4 text-indigo-600 border-input rounded focus:ring-indigo-500"
                                    >
                                    <span class="text-foreground">{{ permission.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm font-medium text-foreground bg-card hover:bg-muted"
                    >
                        {{ $t('features.roles.form.cancel') }}
                    </button>
                    <button
                        @click="saveRole"
                        :disabled="saving"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? $t('features.roles.form.saving') : $t('features.roles.form.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const roles = ref([]);
const permissions = ref({});
const showCreateModal = ref(false);
const selectedRole = ref(null);
const form = ref({
    name: '',
    permissions: []
});

const protectedRoles = ['super-admin', 'admin'];

const isProtectedRole = (name) => protectedRoles.includes(name);

const groupedPermissions = computed(() => permissions.value);

const fetchRoles = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/roles');
        roles.value = response.data?.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch roles:', error);
    } finally {
        loading.value = false;
    }
};

const fetchPermissions = async () => {
    try {
        const response = await api.get('/admin/cms/roles/permissions');
        permissions.value = response.data?.data || response.data || {};
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    }
};

const editRole = (role) => {
    selectedRole.value = role;
    form.value = {
        name: role.name,
        permissions: (role.permissions || []).map(p => p.name)
    };
};

const closeModal = () => {
    showCreateModal.value = false;
    selectedRole.value = null;
    form.value = { name: '', permissions: [] };
};

const saveRole = async () => {
    if (!form.value.name) {
        alert(t('features.roles.messages.enterName'));
        return;
    }

    saving.value = true;
    try {
        if (selectedRole.value) {
            await api.put(`/admin/cms/roles/${selectedRole.value.id}`, form.value);
        } else {
            await api.post('/admin/cms/roles', form.value);
        }
        closeModal();
        await fetchRoles();
    } catch (error) {
        console.error('Failed to save role:', error);
        alert(error.response?.data?.message || t('features.roles.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

const deleteRole = async (role) => {
    if (!confirm(t('features.roles.messages.deleteConfirm', { name: role.name }))) return;

    try {
        await api.delete(`/admin/cms/roles/${role.id}`);
        await fetchRoles();
    } catch (error) {
        console.error('Failed to delete role:', error);
        alert(error.response?.data?.message || t('features.roles.messages.deleteFailed'));
    }
};

const duplicateRole = async (role) => {
    try {
        await api.post(`/admin/cms/roles/${role.id}/duplicate`);
        await fetchRoles();
    } catch (error) {
        console.error('Failed to duplicate role:', error);
        alert(t('features.roles.messages.duplicateFailed'));
    }
};

const toggleCategory = (category) => {
    const categoryPerms = permissions.value[category] || [];
    const categoryNames = categoryPerms.map(p => p.name);
    const allSelected = categoryNames.every(name => form.value.permissions.includes(name));

    if (allSelected) {
        form.value.permissions = form.value.permissions.filter(p => !categoryNames.includes(p));
    } else {
        const toAdd = categoryNames.filter(name => !form.value.permissions.includes(name));
        form.value.permissions.push(...toAdd);
    }
};

const isCategorySelected = (category) => {
    const categoryPerms = permissions.value[category] || [];
    return categoryPerms.every(p => form.value.permissions.includes(p.name));
};

onMounted(() => {
    fetchRoles();
    fetchPermissions();
});
</script>
