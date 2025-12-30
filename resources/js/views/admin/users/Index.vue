<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.users.title') }}</h1>
            <router-link :to="{ name: 'users.create' }">
                <Button>
                    <Plus class="w-5 h-5 mr-2" />
                    {{ $t('features.users.createNew') }}
                </Button>
            </router-link>
        </div>

        <!-- Filters -->
        <Card class="p-4 mb-4">
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-xs">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        :placeholder="$t('features.users.search')"
                        class="pl-9"
                    />
                </div>
                <Select v-model="roleFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="$t('features.users.allRoles')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.users.allRoles') }}</SelectItem>
                        <SelectItem
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            
            
            <!-- Bulk Actions -->
            <div v-if="selectedIds.length > 0" class="mt-4 pt-4 border-t flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <Select
                        v-model="bulkActionSelection"
                        @update:model-value="handleBulkAction"
                    >
                        <SelectTrigger class="w-[160px] h-8 border-primary/20">
                            <SelectValue :placeholder="$t('features.content.list.bulkActions')" />
                        </SelectTrigger>
                        <SelectContent>
                             <SelectItem value="force_logout" class="text-amber-600 focus:text-amber-600">{{ $t('features.users.actions.forceLogout') }}</SelectItem>
                             <SelectItem value="delete" class="text-destructive focus:text-destructive">{{ $t('common.actions.delete') }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </Card>

        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <Loader2 class="mx-auto h-8 w-8 animate-spin text-muted-foreground" />
            <p class="mt-2 text-muted-foreground">{{ $t('features.users.loading') }}</p>
        </div>

        <div v-else-if="users.length === 0" class="bg-card border border-border rounded-lg p-12 text-center">
            <Users class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
            <p class="mt-4 text-muted-foreground">{{ $t('features.users.empty') }}</p>
        </div>

        <div v-else class="bg-card border border-border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 w-[50px]">
                            <Checkbox 
                                :checked="isAllSelected"
                                @update:checked="toggleSelectAll"
                            />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">
                            {{ $t('features.users.table.user') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">
                            {{ $t('features.users.table.email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">
                            {{ $t('features.users.table.roles') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">
                            {{ $t('features.users.table.lastLogin') }}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-muted-foreground">
                            {{ $t('features.users.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-muted">
                        <td class="px-6 py-4">
                            <Checkbox 
                                :checked="selectedIds.includes(user.id)"
                                @update:checked="toggleSelection(user.id)"
                            />
                        </td>
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
                                        class="h-10 w-10 rounded-full bg-muted flex items-center justify-center border border-border"
                                    >
                                        <span class="text-muted-foreground font-medium text-sm">
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
                            <div v-if="user.email_verified_at" class="text-xs text-primary font-medium">
                                {{ $t('features.users.status.verified') }}
                            </div>
                            <div v-else class="text-xs text-muted-foreground italic">
                                {{ $t('features.users.status.unverified') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in (user.roles || [])"
                                    :key="role.id"
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-secondary text-secondary-foreground border border-secondary"
                                >
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-muted-foreground">
                                    {{ $t('features.users.status.noRoles') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            <div v-if="user.last_login_at">
                                {{ formatDate(user.last_login_at) }}
                            </div>
                            <div v-else class="text-muted-foreground">
                                {{ $t('features.users.status.never') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex justify-center items-center space-x-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="forceLogoutUser(user)"
                                    class="h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10"
                                    :title="$t('features.users.actions.forceLogout')"
                                >
                                    <LogOut class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="editUser(user)"
                                    class="h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/10"
                                    :title="$t('common.actions.edit')"
                                >
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteUser(user)"
                                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    :title="$t('common.actions.delete')"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <Pagination
                v-if="pagination && pagination.total > 0"
                :current-page="pagination.current_page"
                :total-items="pagination.total"
                :per-page="Number(pagination.per_page || 10)"
                :show-page-numbers="true"
                @page-change="changePage"
                @update:per-page="changePerPage"
                class="mt-4"
            />
        </div>

        <!-- Create/Edit Modal Removed -->
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';
import toast from '../../../services/toast';
import Button from '../../../components/ui/button.vue';
import Pagination from '../../../components/ui/pagination.vue';
import Input from '../../../components/ui/input.vue';
import Card from '../../../components/ui/card.vue';
import Select from '../../../components/ui/select.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import { Plus, Search, Loader2, Users, LogOut, Pencil, Trash2 } from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const loading = ref(false);
const users = ref([]);
const roles = ref([]);
const search = ref('');
const roleFilter = ref('all');
const pagination = ref(null);

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value?.current_page || 1,
            per_page: pagination.value?.per_page || 10,
        };

        if (search.value) {
            params.search = search.value;
        }

        // Don't send 'all' role to API
        if (roleFilter.value && roleFilter.value !== 'all') {
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

const changePerPage = (perPage) => {
    if (pagination.value) {
        pagination.value.per_page = perPage;
        pagination.value.current_page = 1;
        fetchUsers();
    }
};

const editUser = (user) => {
    router.push({ name: 'users.edit', params: { id: user.id } });
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

const forceLogoutUser = async (user) => {
    if (!confirm(t('features.users.messages.forceLogoutConfirm', { name: user.name }))) {
        return;
    }

    try {
        const response = await api.post(`/admin/cms/users/${user.id}/force-logout`);
        const { data } = parseResponse(response);
        
        toast.success(
            t('features.users.messages.forceLogoutSuccess'),
            t('features.users.messages.forceLogoutSessions', { count: data.revoked_sessions || 0 })
        );
    } catch (error) {
        console.error('Failed to force logout user:', error);
        const message = error.response?.data?.message || t('features.users.messages.forceLogoutFailed');
        toast.error(t('features.users.messages.forceLogoutFailed'), message);
    }
};

const selectedIds = ref([]);

const isAllSelected = computed(() => {
    return users.value.length > 0 && selectedIds.value.length === users.value.length;
});

const toggleSelection = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedIds.value = users.value.map(u => u.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkActionSelection = ref('');

const handleBulkAction = async (value) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action) => {
    if (selectedIds.value.length === 0) return;
    
    let confirmMessage = '';
    if (action === 'delete') {
        confirmMessage = t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length });
    } else if (action === 'force_logout') {
        confirmMessage = t('features.users.messages.bulkForceLogoutConfirm', { count: selectedIds.value.length }) || `Force logout ${selectedIds.value.length} users?`;
    }

    if (!confirm(confirmMessage)) {
        bulkActionSelection.value = '';
        return;
    }

    try {
        const response = await api.post('/admin/cms/users/bulk-action', {
            ids: selectedIds.value,
            action: action
        });
        const { data } = parseResponse(response);

        selectedIds.value = [];
        await fetchUsers();
        
        toast.success(t('common.messages.success.action'), data.message);
    } catch (error) {
        console.error('Bulk action failed:', error);
        toast.error(t('common.messages.error.action'), error.response?.data?.message || 'Bulk action failed');
    }
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
