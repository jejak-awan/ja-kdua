<template>
    <div>
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.users.title') }}</h1>
            <router-link :to="{ name: 'users.create' }" v-if="authStore.hasPermission('manage users')">
                <Button>
                    <Plus class="w-5 h-5 mr-2" />
                    {{ $t('features.users.createNew') }}
                </Button>
            </router-link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <Card
                class="p-4 cursor-pointer transition-all hover:shadow-md hover:border-primary/50"
                :class="{ 'border-primary ring-2 ring-primary/20': verificationFilter === 'all' && !activeStatFilter }"
                @click="clearFilters"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.total') }}</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
                    </div>
                    <Users class="w-8 h-8 text-primary opacity-80" />
                </div>
            </Card>
            <Card
                class="p-4 cursor-pointer transition-all hover:shadow-md hover:border-primary/50"
                :class="{ 'border-primary ring-2 ring-primary/20': verificationFilter === 'verified' }"
                @click="setVerificationFilter('verified')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.verified') }}</p>
                        <p class="text-2xl font-bold text-primary">{{ stats.verified }}</p>
                    </div>
                    <CheckCircle class="w-8 h-8 text-primary opacity-80" />
                </div>
            </Card>
            <Card
                class="p-4 cursor-pointer transition-all hover:shadow-md hover:border-amber-500/50"
                :class="{ 'border-amber-500 ring-2 ring-amber-500/20': verificationFilter === 'unverified' }"
                @click="setVerificationFilter('unverified')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.unverified') }}</p>
                        <p class="text-2xl font-bold text-amber-500">{{ stats.unverified }}</p>
                    </div>
                    <AlertCircle class="w-8 h-8 text-amber-500 opacity-80" />
                </div>
            </Card>
            <Card
                class="p-4 cursor-pointer transition-all hover:shadow-md hover:border-emerald-500/50"
                :class="{ 'border-emerald-500 ring-2 ring-emerald-500/20': activeStatFilter === 'recent' }"
                @click="setStatFilter('recent')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.recent') }}</p>
                        <p class="text-2xl font-bold text-emerald-500">{{ stats.recent }}</p>
                    </div>
                    <UserPlus class="w-8 h-8 text-emerald-500 opacity-80" />
                </div>
            </Card>
            <Card
                class="p-4 cursor-pointer transition-all hover:shadow-md hover:border-blue-500/50"
                :class="{ 'border-blue-500 ring-2 ring-blue-500/20': activeStatFilter === 'active' }"
                @click="setStatFilter('active')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.active') }}</p>
                        <p class="text-2xl font-bold text-blue-500">{{ stats.active }}</p>
                    </div>
                    <Activity class="w-8 h-8 text-blue-500 opacity-80" />
                </div>
            </Card>
        </div>

        <!-- Filters -->
        <Card class="p-4 mb-4">
            <div class="flex flex-wrap items-center gap-4">
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
                <Select v-model="verificationFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue :placeholder="$t('features.users.filters.all')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{{ $t('features.users.filters.all') }}</SelectItem>
                        <SelectItem value="verified">{{ $t('features.users.filters.verifiedOnly') }}</SelectItem>
                        <SelectItem value="unverified">{{ $t('features.users.filters.unverifiedOnly') }}</SelectItem>
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
                             <SelectItem value="verify" class="text-primary focus:text-primary">{{ $t('features.users.actions.verify') }}</SelectItem>
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
                                    v-if="!user.email_verified_at"
                                    variant="ghost"
                                    size="icon"
                                    @click="verifyUser(user)"
                                    class="h-8 w-8 text-primary hover:text-primary hover:bg-primary/10"
                                    :title="$t('features.users.actions.verify')"
                                    :disabled="!canManage(user)"
                                >
                                    <CheckCheck class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="forceLogoutUser(user)"
                                    class="h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10"
                                    :title="$t('features.users.actions.forceLogout')"
                                    :disabled="!canManage(user)"
                                >
                                    <LogOut class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="editUser(user)"
                                    class="h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/10"
                                    :title="$t('common.actions.edit')"
                                    :disabled="!canManage(user)"
                                >
                                    <Pencil class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="deleteUser(user)"
                                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    :title="$t('common.actions.delete')"
                                    :disabled="!canDelete(user)"
                                >
                                    <Trash2 class="w-4 h-4" :class="{ 'opacity-50': !canDelete(user) }" />
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
import { Plus, Search, Loader2, Users, LogOut, Pencil, Trash2, CheckCheck, CheckCircle, AlertCircle, UserPlus, Activity } from 'lucide-vue-next';
import { useAuthStore } from '../../../stores/auth';

const { t } = useI18n();
const router = useRouter();
const loading = ref(false);
const users = ref([]);
const roles = ref([]);
const search = ref('');
const roleFilter = ref('all');
const verificationFilter = ref('all');
const activeStatFilter = ref(null);
const pagination = ref(null);
const authStore = useAuthStore();
const stats = ref({
    total: 0,
    verified: 0,
    unverified: 0,
    recent: 0,
    active: 0,
    by_role: {},
});

const isSuperAdmin = (u) => u.roles?.some(r => r.name === 'super-admin');

const canManage = (targetUser) => {
    // Self management is always allowed (for basic edits)
    if (targetUser.id === authStore.user?.id) return true;
    
    // Super Admin (Rank 100) can manage anyone
    if (authStore.getRoleRank() >= 100) return true;

    // Others must strictly be higher rank
    return authStore.isHigherThan(targetUser);
};

const canDelete = (targetUser) => {
    // Cannot delete self
    if (targetUser.id === authStore.user?.id) return false;
    
    const myRank = authStore.getRoleRank();
    
    // Non-Super Admins can only delete users with strictly lower rank
    if (myRank < 100) {
        if (!authStore.isHigherThan(targetUser)) return false;
    }
    
    // Super Admin protection for last one
    if (isSuperAdmin(targetUser)) {
        const superAdminCount = users.value.filter(u => isSuperAdmin(u)).length;
        // This is only a frontend check, backend will re-verify
        if (superAdminCount <= 1 && pagination.value?.total <= 1) return false;
    }
    
    return true;
};

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

        // Add verification filter
        if (verificationFilter.value === 'verified') {
            params.verified = 1;
        } else if (verificationFilter.value === 'unverified') {
            params.verified = 0;
        }

        // Add stat filters
        if (activeStatFilter.value === 'recent') {
            params.recent = 1;
        } else if (activeStatFilter.value === 'active') {
            params.active = 1;
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

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/cms/users/stats');
        // The BaseApiController returns { success: true, data: { ... }, message: ... }
        // parseResponse returns { data: [...], pagination: ... } which is for lists.
        // We just need the raw data object here.
        if (response.data && response.data.data) {
            stats.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch stats:', error);
    }
};

const setVerificationFilter = (filter) => {
    activeStatFilter.value = null;
    verificationFilter.value = filter;
};

const setStatFilter = (filter) => {
    verificationFilter.value = 'all';
    activeStatFilter.value = activeStatFilter.value === filter ? null : filter;
    fetchUsers();
};

const clearFilters = () => {
    verificationFilter.value = 'all';
    activeStatFilter.value = null;
    roleFilter.value = 'all';
    search.value = '';
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

const verifyUser = async (user) => {
    try {
        await api.post(`/admin/cms/users/${user.id}/verify`);
        toast.success(t('features.users.messages.verifySuccess'));
        await fetchUsers();
    } catch (error) {
        console.error('Failed to verify user:', error);
        const message = error.response?.data?.message || t('common.messages.error.action');
        toast.error(t('common.messages.error.action'), message);
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
    } else if (action === 'verify') {
        confirmMessage = t('features.users.messages.bulkVerifyConfirm', { count: selectedIds.value.length }) || `Verify ${selectedIds.value.length} users?`;
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

watch([search, roleFilter, verificationFilter, activeStatFilter], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchUsers();
});

onMounted(() => {
    fetchUsers();
    fetchRoles();
    fetchStats();
});
</script>
