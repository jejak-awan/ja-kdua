<template>
    <div>
            <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.users.title') }}</h1>
            <router-link :to="{ name: 'users.create' }" v-if="authStore.hasPermission('create users')">
                <Button>
                    <Plus class="w-5 h-5 mr-2" />
                    {{ $t('features.users.createNew') }}
                </Button>
            </router-link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <Card
                :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-primary/50', { 'border-primary ring-2 ring-primary/20': verificationFilter === 'all' && !activeStatFilter })"
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
                :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-primary/50', { 'border-primary ring-2 ring-primary/20': verificationFilter === 'verified' })"
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
                :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-warning/50', { 'border-warning ring-2 ring-warning/20': verificationFilter === 'unverified' })"
                @click="setVerificationFilter('unverified')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.unverified') }}</p>
                        <p class="text-2xl font-bold text-warning">{{ stats.unverified }}</p>
                    </div>
                    <AlertCircle class="w-8 h-8 text-warning opacity-80" />
                </div>
            </Card>
            <Card
                :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-success/50', { 'border-success ring-2 ring-success/20': activeStatFilter === 'recent' })"
                @click="setStatFilter('recent')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.recent') }}</p>
                        <p class="text-2xl font-bold text-success">{{ stats.recent }}</p>
                    </div>
                    <UserPlus class="w-8 h-8 text-success opacity-80" />
                </div>
            </Card>
            <Card
                :class="cn('p-4 cursor-pointer hover:shadow-md hover:border-info/50', { 'border-info ring-2 ring-info/20': activeStatFilter === 'active' })"
                @click="setStatFilter('active')"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">{{ $t('features.users.stats.active') }}</p>
                        <p class="text-2xl font-bold text-info">{{ stats.active }}</p>
                    </div>
                    <Activity class="w-8 h-8 text-info opacity-80" />
                </div>
            </Card>
        </div>

        <!-- content -->
        <Card class="overflow-hidden">
            <!-- Filters & Actions -->
            <div class="px-6 py-4 border-b border-border/40">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Search / Filters -->
                    <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="text"
                                :placeholder="$t('features.users.search')"
                                class="pl-9"
                            />
                        </div>
                        <Select v-model="roleFilter">
                            <SelectTrigger class="w-[160px]">
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
                            <SelectTrigger class="w-[160px]">
                                <SelectValue :placeholder="$t('features.users.filters.all')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ $t('features.users.filters.all') }}</SelectItem>
                                <SelectItem value="verified">{{ $t('features.users.filters.verifiedOnly') }}</SelectItem>
                                <SelectItem value="unverified">{{ $t('features.users.filters.unverifiedOnly') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="trashedFilter">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue :placeholder="$t('common.labels.status')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="without">{{ $t('common.labels.activeOnly') }}</SelectItem>
                                <SelectItem value="with">{{ $t('common.labels.includesTrashed') }}</SelectItem>
                                <SelectItem value="only">{{ $t('common.labels.trashedOnly') }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-2">
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1 mr-2">
                            <span class="text-xs font-semibold text-primary uppercase tracking-wider">
                                {{ selectedIds.length }} selected
                            </span>
                            <div class="h-4 w-px bg-primary/20"></div>
                            <Select
                                v-model="bulkActionSelection"
                                @update:model-value="handleBulkAction"
                            >
                                <SelectTrigger class="w-[140px] h-7 border-primary/20 text-xs shadow-none">
                                    <SelectValue :placeholder="$t('features.content.list.bulkActions')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="force_logout" class="text-warning focus:text-warning">{{ $t('features.users.actions.forceLogout') }}</SelectItem>
                                    <SelectItem value="verify" class="text-primary focus:text-primary">{{ $t('features.users.actions.verify') }}</SelectItem>
                                    <SelectItem v-if="trashedFilter !== 'only'" value="delete" class="text-destructive focus:text-destructive">{{ $t('common.actions.delete') }}</SelectItem>
                                    <SelectItem v-if="trashedFilter === 'only'" value="restore" class="text-success focus:text-success">{{ $t('common.actions.restore') }}</SelectItem>
                                    <SelectItem v-if="trashedFilter === 'only'" value="force_delete" class="text-destructive focus:text-destructive">{{ $t('common.actions.forceDelete') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            </div>

            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50px] px-6">
                                <Checkbox 
                                    :checked="isAllSelected"
                                    @update:checked="toggleSelectAll"
                                />
                            </TableHead>
                            <TableHead class="px-6 text-[10px] text-muted-foreground/70">
                                {{ $t('features.users.table.user') }}
                            </TableHead>
                            <TableHead class="px-6 text-[10px] text-muted-foreground/70">
                                {{ $t('features.users.table.email') }}
                            </TableHead>
                            <TableHead class="px-6 text-[10px] text-muted-foreground/70">
                                {{ $t('features.users.table.roles') }}
                            </TableHead>
                            <TableHead class="px-6 text-[10px] text-muted-foreground/70">
                                {{ $t('features.users.table.lastLogin') }}
                            </TableHead>
                            <TableHead class="px-6 text-center text-[10px] text-muted-foreground/70">
                                {{ $t('features.users.table.actions') }}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="loading">
                            <TableCell colspan="6" class="h-24 text-center">
                                <Loader2 class="h-6 w-6 animate-spin mx-auto text-muted-foreground" />
                            </TableCell>
                        </TableRow>

                        <TableRow v-else-if="users.length === 0">
                            <TableCell colspan="6" class="h-32 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <Users class="h-8 w-8 text-muted-foreground/20" />
                                    <p>{{ $t('features.users.empty') }}</p>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-else v-for="user in users" :key="user.id" class="group">
                            <TableCell class="px-6">
                                <Checkbox 
                                    :checked="selectedIds.includes(user.id)"
                                    @update:checked="toggleSelection(user.id)"
                                />
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9">
                                        <img
                                            v-if="user.avatar"
                                            :src="user.avatar"
                                            :alt="user.name"
                                            class="h-9 w-9 rounded-full object-cover"
                                        >
                                        <div
                                            v-else
                                            class="h-9 w-9 rounded-full bg-muted flex items-center justify-center border border-border/40"
                                        >
                                            <span class="text-muted-foreground font-medium text-xs">
                                                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-foreground">
                                            {{ user.name }}
                                            <span v-if="user.deleted_at" class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive uppercase tracking-wide">
                                                {{ $t('common.labels.deleted') }}
                                            </span>
                                        </div>
                                        <div v-if="user.phone" class="text-[10px] text-muted-foreground font-mono uppercase tracking-tight">{{ user.phone }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="text-sm text-foreground">{{ user.email }}</div>
                                <div v-if="user.email_verified_at" class="text-[10px] text-primary font-bold uppercase tracking-wider">
                                    {{ $t('features.users.status.verified') }}
                                </div>
                                <div v-else class="text-[10px] text-muted-foreground italic uppercase tracking-wider">
                                    {{ $t('features.users.status.unverified') }}
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        v-for="role in (user.roles || [])"
                                        :key="role.id"
                                        variant="secondary"
                                        class="h-5 text-[10px] px-2 font-semibold uppercase tracking-wider"
                                    >
                                        {{ role.name }}
                                    </Badge>
                                    <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-muted-foreground italic">
                                        {{ $t('features.users.status.noRoles') }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4 text-sm text-muted-foreground">
                                <div v-if="user.last_login_at" class="text-xs">
                                    {{ formatDate(user.last_login_at) }}
                                </div>
                                <div v-else class="text-xs text-muted-foreground/50">
                                    {{ $t('features.users.status.never') }}
                                </div>
                            </TableCell>
                            <TableCell class="px-6 py-4">
                                <div class="flex justify-center items-center gap-1">
                                    <Button
                                        v-if="!user.email_verified_at && authStore.hasPermission('edit users')"
                                        variant="ghost"
                                        size="icon"
                                        @click="verifyUser(user)"
                                        class="h-8 w-8 text-primary hover:bg-primary/10"
                                        :title="$t('features.users.actions.verify')"
                                        :disabled="!canManage(user)"
                                    >
                                        <CheckCheck class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                    </Button>
                                    <Button
                                        v-if="authStore.hasPermission('edit users')"
                                        variant="ghost"
                                        size="icon"
                                        @click="forceLogoutUser(user)"
                                        class="h-8 w-8 text-warning hover:bg-warning/10"
                                        :title="$t('features.users.actions.forceLogout')"
                                        :disabled="!canManage(user)"
                                    >
                                        <LogOut class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                    </Button>
                                    <Button
                                        v-if="authStore.hasPermission('edit users')"
                                        variant="ghost"
                                        size="icon"
                                        @click="editUser(user)"
                                        class="h-8 w-8 text-primary hover:bg-primary/10"
                                        :title="$t('common.actions.edit')"
                                        :disabled="!canManage(user)"
                                    >
                                        <Pencil class="w-4 h-4" :class="{ 'opacity-50': !canManage(user) }" />
                                    </Button>
                                    <Button
                                        v-if="authStore.hasPermission('delete users') && !user.deleted_at"
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteUser(user)"
                                        class="h-8 w-8 text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.delete')"
                                        :disabled="!canDelete(user)"
                                    >
                                        <Trash2 class="w-4 h-4" :class="{ 'opacity-50': !canDelete(user) }" />
                                    </Button>
                                    
                                    <Button
                                        v-if="user.deleted_at && authStore.hasPermission('delete users')"
                                        variant="ghost"
                                        size="icon"
                                        @click="restoreUser(user)"
                                        class="h-8 w-8 text-success hover:bg-success/10"
                                        :title="$t('common.actions.restore')"
                                    >
                                        <RotateCcw class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="user.deleted_at && authStore.hasPermission('delete users')"
                                        variant="ghost"
                                        size="icon"
                                        @click="forceDeleteUser(user)"
                                        class="h-8 w-8 text-destructive hover:bg-destructive/10"
                                        :title="$t('common.actions.forceDelete')"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <Pagination
                    v-if="pagination && pagination.total > 0"
                    :current-page="pagination.current_page"
                    :total-items="pagination.total"
                    :per-page="Number(pagination.per_page || 10)"
                    @page-change="changePage"
                    @update:per-page="changePerPage"
                    class="border-none shadow-none mt-4 px-6 py-4"
                />
            </CardContent>
        </Card>

        <!-- Create/Edit Modal Removed -->
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import api from '@/services/api';
import { cn } from '@/lib/utils';
import { parseResponse, ensureArray } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Pagination from '@/components/ui/pagination.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Card from '@/components/ui/card.vue';
// @ts-ignore
import Select from '@/components/ui/select.vue';
// @ts-ignore
import SelectTrigger from '@/components/ui/select-trigger.vue';
// @ts-ignore
import SelectValue from '@/components/ui/select-value.vue';
// @ts-ignore
import SelectContent from '@/components/ui/select-content.vue';
// @ts-ignore
import SelectItem from '@/components/ui/select-item.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Badge from '@/components/ui/badge.vue';
// @ts-ignore
import Table from '@/components/ui/table.vue';
// @ts-ignore
import TableHeader from '@/components/ui/table-header.vue';
// @ts-ignore
import TableBody from '@/components/ui/table-body.vue';
// @ts-ignore
import TableRow from '@/components/ui/table-row.vue';
// @ts-ignore
import TableCell from '@/components/ui/table-cell.vue';
// @ts-ignore
import TableHead from '@/components/ui/table-head.vue';
import { Plus, Search, Loader2, Users, LogOut, Pencil, Trash2, CheckCheck, CheckCircle, AlertCircle, UserPlus, Activity, RotateCcw, Tag } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useConfirm } from '@/composables/useConfirm';
import type { User, Role } from '@/types/auth';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const search = ref('');
const roleFilter = ref('all');
const verificationFilter = ref('all');
const trashedFilter = ref('without');
const activeStatFilter = ref<string | null>(null);
const pagination = ref<any>(null);
const authStore = useAuthStore();

const { confirm } = useConfirm();
const stats = ref<{
    total: number;
    verified: number;
    unverified: number;
    recent: number;
    active: number;
    by_role: Record<string, number>;
}>({
    total: 0,
    verified: 0,
    unverified: 0,
    recent: 0,
    active: 0,
    by_role: {},
});

const isSuperAdmin = (u: User) => u.roles?.some(r => r.name === 'super-admin');

const canManage = (targetUser: User) => {
    // Self management is always allowed (for basic edits)
    if (targetUser.id === authStore.user?.id) return true;
    
    // Super Admin (Rank 100) can manage anyone
    if (authStore.getRoleRank() >= 100) return true;

    // Others must strictly be higher rank
    return authStore.isHigherThan(targetUser);
};

const canDelete = (targetUser: User) => {
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
        if (pagination.value?.total <= 1 || superAdminCount <= 1) return false;
    }
    
    return true;
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params: any = {
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

        // Add trashed filter
        if (trashedFilter.value && trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        // Add stat filters
        if (activeStatFilter.value === 'recent') {
            params.recent = 1;
        } else if (activeStatFilter.value === 'active') {
            params.active = 1;
        }

        const response = await api.get('/admin/ja/users', { params });
        const { data, pagination: paginationData } = parseResponse(response);
        // Ensure each user has roles array
        users.value = ensureArray(data).map((user: any) => ({
            ...user,
            roles: user.roles || [],
        }));
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error: any) {
        console.error('Failed to fetch users:', error);
        toast.error.action(error);
    } finally {
        loading.value = false;
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/admin/ja/users/stats');
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

const setVerificationFilter = (filter: string) => {
    activeStatFilter.value = null;
    verificationFilter.value = filter;
};

const setStatFilter = (filter: string) => {
    verificationFilter.value = 'all';
    activeStatFilter.value = activeStatFilter.value === filter ? null : filter;
    fetchUsers();
};

const clearFilters = () => {
    verificationFilter.value = 'all';
    activeStatFilter.value = null;
    roleFilter.value = 'all';
    trashedFilter.value = 'without';
    search.value = '';
};

const fetchRoles = async () => {
    try {
        const response = await api.get('/admin/ja/roles').catch(() => null);
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

const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchUsers();
    }
};

const changePerPage = (perPage: number | string) => {
    if (pagination.value) {
        pagination.value.per_page = Number(perPage);
        pagination.value.current_page = 1;
        fetchUsers();
    }
};

const editUser = (user: User) => {
    router.push({ name: 'users.edit', params: { id: user.id } });
};

const deleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('common.messages.confirm.title'),
        message: t('features.users.messages.deleteConfirm', { name: user.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.delete(`/admin/ja/users/${user.id}`);
        await fetchUsers();
        toast.success.delete('User');
    } catch (error: any) {
        console.error('Failed to delete user:', error);
        toast.error.delete(error, 'User');
    }
};

const forceLogoutUser = async (user: User) => {
    const confirmed = await confirm({
        title: t('features.users.actions.forceLogout'),
        message: t('features.users.messages.forceLogoutConfirm', { name: user.name }),
        variant: 'warning',
        confirmText: t('features.users.actions.forceLogout'),
    });

    if (!confirmed) {
        return;
    }

    try {
        await api.post(`/admin/ja/users/${user.id}/force-logout`);
        
        toast.success.action('User forced logout');
    } catch (error: any) {
        console.error('Failed to force logout user:', error);
        toast.error.action(error);
    }
};

const verifyUser = async (user: User) => {
    try {
        await api.post(`/admin/ja/users/${user.id}/verify`);
        toast.success.action('User verified');
        await fetchUsers();
    } catch (error: any) {
        console.error('Failed to verify user:', error);
        toast.error.action(error);
    }
};

const restoreUser = async (user: User) => {
    const confirmed = await confirm({
        title: 'Restore User',
        message: `Are you sure you want to restore ${user.name}?`,
        variant: 'info',
        confirmText: 'Restore',
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/ja/users/${user.id}/restore`);
        toast.success.action('User restored');
        await fetchUsers();
    } catch (error: any) {
        console.error('Failed to restore user:', error);
        toast.error.action(error);
    }
};

const forceDeleteUser = async (user: User) => {
    const confirmed = await confirm({
        title: 'Permanently Delete User',
        message: `Are you sure you want to PERMANENTLY delete ${user.name}? This action cannot be undone.`,
        variant: 'danger',
        confirmText: 'Force Delete',
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/ja/users/${user.id}/force-delete`);
        toast.success.action('User permanently deleted');
        await fetchUsers();
    } catch (error: any) {
        console.error('Failed to force delete user:', error);
        toast.error.action(error);
    }
};

const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    return users.value.length > 0 && selectedIds.value.length === users.value.length;
});

const toggleSelection = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedIds.value = users.value.map(u => u.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    let confirmMessage = '';
    let confirmVariant = 'warning';
    let confirmTitle = t('features.content.list.bulkActions');

    if (action === 'delete') {
        confirmMessage = t('common.messages.confirm.bulkDelete', { count: selectedIds.value.length });
        confirmVariant = 'danger';
        confirmTitle = t('common.actions.delete');
    } else if (action === 'force_logout') {
        confirmMessage = t('features.users.messages.bulkForceLogoutConfirm', { count: selectedIds.value.length }) || `Force logout ${selectedIds.value.length} users?`;
        confirmTitle = t('features.users.actions.forceLogout');
    } else if (action === 'verify') {
        confirmMessage = t('features.users.messages.bulkVerifyConfirm', { count: selectedIds.value.length }) || `Verify ${selectedIds.value.length} users?`;
        confirmTitle = t('features.users.actions.verify');
    } else if (action === 'restore') {
        confirmMessage = `Restore ${selectedIds.value.length} users?`;
        confirmTitle = 'Restore Users';
        confirmVariant = 'info';
    } else if (action === 'force_delete') {
         confirmMessage = `Permanently delete ${selectedIds.value.length} users? This cannot be undone.`;
         confirmTitle = 'Force Delete Users';
        confirmVariant = 'danger';
    }

    const confirmed = await confirm({
        title: confirmTitle,
        message: confirmMessage,
        variant: confirmVariant as any,
        confirmText: t('common.actions.confirm') || 'Confirm',
    });

    if (!confirmed) {
        bulkActionSelection.value = '';
        return;
    }

    try {
        const response = await api.post('/admin/ja/users/bulk-action', {
            ids: selectedIds.value,
            action: action
        });
        
        selectedIds.value = [];
        await fetchUsers();
        
        toast.success.action('Bulk action successful');
    } catch (error: any) {
        console.error('Bulk action failed:', error);
        toast.error.action(error);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([search, roleFilter, verificationFilter, trashedFilter, activeStatFilter], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchUsers();
});

const route = useRoute();

onMounted(() => {
    // Check for search query param from Global Search
    if (route.query.q) {
        search.value = route.query.q as string;
    }
    fetchUsers();
    fetchRoles();
    fetchStats();
});
</script>
