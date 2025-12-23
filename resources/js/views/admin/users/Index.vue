<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.users.title') }}</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.users.createNew') }}
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-card shadow rounded-lg p-4 mb-4">
            <div class="flex items-center space-x-4">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="$t('features.users.search')"
                    class="flex-1 px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                <select
                    v-model="roleFilter"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">{{ $t('features.users.allRoles') }}</option>
                    <option
                        v-for="role in roles"
                        :key="role.id"
                        :value="role.name"
                    >
                        {{ role.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Users List -->
        <div v-if="loading" class="bg-card shadow rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.users.loading') }}</p>
        </div>

        <div v-else-if="users.length === 0" class="bg-card shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p class="mt-4 text-muted-foreground">{{ $t('features.users.empty') }}</p>
        </div>

        <div v-else class="bg-card shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $t('features.users.table.user') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $t('features.users.table.email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $t('features.users.table.roles') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $t('features.users.table.lastLogin') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ $t('features.users.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img
                                        v-if="user.avatar"
                                        :src="user.avatar"
                                        :alt="user.name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    >
                                    <div
                                        v-else
                                        class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center"
                                    >
                                        <span class="text-indigo-600 font-medium text-sm">
                                            {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-foreground">{{ user.name }}</div>
                                    <div v-if="user.phone" class="text-sm text-muted-foreground">{{ user.phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-foreground">{{ user.email }}</div>
                            <div v-if="user.email_verified_at" class="text-xs text-green-600">
                                {{ $t('features.users.status.verified') }}
                            </div>
                            <div v-else class="text-xs text-yellow-600">
                                {{ $t('features.users.status.unverified') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in (user.roles || [])"
                                    :key="role.id"
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800"
                                >
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-gray-400">
                                    {{ $t('features.users.status.noRoles') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            <div v-if="user.last_login_at">
                                {{ formatDate(user.last_login_at) }}
                            </div>
                            <div v-else class="text-gray-400">
                                {{ $t('features.users.status.never') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <button
                                    @click="editUser(user)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ $t('features.users.actions.edit') }}
                                </button>
                                <button
                                    @click="deleteUser(user)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ $t('features.users.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-border flex items-center justify-between">
                <div class="text-sm text-foreground">
                    {{ $t('common.pagination.showing', { from: pagination.from, to: pagination.to, total: pagination.total }) }}
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                    >
                        {{ $t('common.pagination.previous') }}
                    </button>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-2 border border-input bg-card text-foreground rounded-md text-sm disabled:opacity-50"
                    >
                        {{ $t('common.pagination.next') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <UserModal
            v-if="showCreateModal || showEditModal"
            @close="closeModal"
            @saved="handleUserSaved"
            :user="editingUser"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import UserModal from '../../../components/users/UserModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const { t } = useI18n();
const loading = ref(false);
const users = ref([]);
const roles = ref([]);
const search = ref('');
const roleFilter = ref('');
const pagination = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingUser = ref(null);

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
        };

        if (search.value) {
            params.search = search.value;
        }

        if (roleFilter.value) {
            params.role = roleFilter.value;
        }

        const response = await api.get('/admin/cms/users', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        // Ensure each user has roles array
        users.value = ensureArray(data).map(user => ({
            ...user,
            roles: user.roles || [],
        }));
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error) {
        console.error('Failed to fetch users:', error);
    } finally {
        loading.value = false;
    }
};

const fetchRoles = async () => {
    try {
        // Fetch roles from Spatie Permission
        // Note: You may need to create a roles endpoint or use a different approach
        const response = await api.get('/admin/cms/roles').catch(() => null);
        if (response) {
            const { data: rolesData } = parseResponse(response);
            roles.value = ensureArray(rolesData);
        } else {
            // Fallback: Extract unique roles from users
            const uniqueRoles = new Map();
            users.value.forEach(user => {
                user.roles?.forEach(role => {
                    if (!uniqueRoles.has(role.id)) {
                        uniqueRoles.set(role.id, role);
                    }
                });
            });
            roles.value = Array.from(uniqueRoles.values());
        }
    } catch (error) {
        console.error('Failed to fetch roles:', error);
    }
};

const changePage = (page) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchUsers();
    }
};

const editUser = (user) => {
    editingUser.value = user;
    showEditModal.value = true;
};

const deleteUser = async (user) => {
    if (!confirm(t('features.users.messages.deleteConfirm', { name: user.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/users/${user.id}`);
        await fetchUsers();
    } catch (error) {
        console.error('Failed to delete user:', error);
        const message = error.response?.data?.message || t('features.users.messages.deleteFailed');
        alert(message);
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingUser.value = null;
};

const handleUserSaved = () => {
    fetchUsers();
    fetchRoles();
    closeModal();
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([search, roleFilter], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchUsers();
});

onMounted(() => {
    fetchUsers();
    fetchRoles();
});
</script>
