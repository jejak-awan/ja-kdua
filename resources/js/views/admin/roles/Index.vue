<template>
    <div>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.roles.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.roles.subtitle') }}</p>
            </div>
             <Button as-child variant="default" v-if="authStore.hasPermission('create roles')">
                <router-link :to="{ name: 'roles.create' }" class="flex items-center justify-center">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.roles.create') }}
                </router-link>
            </Button>
        </div>

        <!-- Filter Bar -->
        <div class="bg-card border border-border rounded-lg p-4 mb-6 sticky top-0 z-10 shadow-sm backdrop-blur-xl bg-card/80">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Search & Filters -->
                <div class="flex items-center gap-3 flex-1 w-full md:w-auto">
                    <div class="relative flex-1 md:max-w-xs">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="$t('common.actions.search')"
                             class="pl-9 h-9 w-full md:w-[280px]"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                 <!-- View Toggle & Bulk Actions -->
                <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                    <!-- Bulk Actions -->
                     <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div v-if="selectedRoles.length > 0" class="flex items-center gap-2">
                             <span class="text-sm text-muted-foreground hidden sm:inline-block">
                                {{ selectedRoles.length }} selected
                            </span>
                            <Select v-model="bulkActionSelection" @update:modelValue="handleBulkAction">
                                <SelectTrigger class="w-[140px] h-9 bg-primary/5 border-primary/20 text-primary hover:bg-primary/10 transition-colors">
                                    <SelectValue :placeholder="$t('common.actions.bulkAction')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="delete" class="text-destructive focus:text-destructive">
                                        <div class="flex items-center">
                                            <Trash2 class="w-4 h-4 mr-2" />
                                            {{ $t('common.actions.delete') }}
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                     </Transition>

                    <!-- View Toggle -->
                    <div class="flex items-center bg-muted rounded-lg p-1 border border-border h-9">
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-1.5 rounded-md transition-all h-7 w-7 flex items-center justify-center',
                                viewMode === 'grid' 
                                    ? 'bg-background shadow-sm text-foreground' 
                                    : 'text-muted-foreground hover:text-foreground'
                            ]"
                            :title="$t('common.actions.gridView')"
                        >
                            <LayoutGrid class="w-4 h-4" />
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="[
                                'p-1.5 rounded-md transition-all h-7 w-7 flex items-center justify-center',
                                viewMode === 'list' 
                                    ? 'bg-background shadow-sm text-foreground' 
                                    : 'text-muted-foreground hover:text-foreground'
                            ]"
                            :title="$t('common.actions.listView')"
                        >
                            <List class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading">
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="i in 3" :key="i" class="h-48 rounded-lg bg-card border border-border animate-pulse"></div>
            </div>
            <div v-else class="space-y-2">
                 <div v-for="i in 3" :key="i" class="h-16 rounded-lg bg-card border border-border animate-pulse"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="roles.length === 0" class="text-center py-12 bg-card border border-border rounded-lg">
            <div class="p-4 rounded-full bg-muted w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                 <Shield class="w-8 h-8 text-muted-foreground" />
            </div>
            <h3 class="text-lg font-medium text-foreground mb-1">{{ $t('features.roles.list.empty') }}</h3>
            <p class="text-muted-foreground text-sm max-w-sm mx-auto mb-6">
                {{ search ? $t('common.messages.noResults') : $t('features.roles.subtitle') }}
            </p>
             <Button v-if="search" variant="outline" @click="search = ''; fetchRoles()">
                {{ $t('common.actions.clear') }}
            </Button>
             <Button v-else as-child variant="default" v-if="authStore.hasPermission('create roles')">
                <router-link :to="{ name: 'roles.create' }" class="flex items-center justify-center">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.roles.create') }}
                </router-link>
            </Button>
        </div>

        <!-- content -->
        <div v-else>
            <!-- Select All Banner (for grid view especially) -->
             <div v-if="selectedRoles.length > 0 && viewMode === 'grid'" class="bg-primary/5 border border-primary/20 rounded-lg p-2 mb-4 flex items-center justify-between px-4">
                <span class="text-sm text-primary font-medium flex items-center gap-2">
                     <CheckSquare class="w-4 h-4" />
                    {{ selectedRoles.length }} roles selected
                </span>
                <Button variant="ghost" size="sm" @click="selectedRoles = []" class="h-7 text-xs hover:bg-primary/10 hover:text-primary">
                    {{ $t('common.actions.deselectAll') }}
                </Button>
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-card border border-border rounded-lg overflow-hidden flex flex-col hover:border-primary/50 transition-all duration-200 group relative"
                    :class="{ 'ring-2 ring-primary ring-offset-2 ring-offset-background border-primary': selectedRoles.includes(role.id) }"
                >
                    <!-- Selection Checkbox (Grid) -->
                    <div class="absolute top-3 left-3 z-10" v-if="!isProtectedRole(role.name)">
                        <Checkbox 
                            :checked="selectedRoles.includes(role.id)"
                            @update:checked="(checked) => toggleSelection(role.id)"
                            class="bg-card/80 backdrop-blur-sm data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground border-muted-foreground/30"
                        />
                    </div>

                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-4 pl-6"> <!-- Added padding-left for checkbox space -->
                            <div class="flex items-center gap-3">
                                 <div class="p-2.5 bg-primary/10 rounded-lg group-hover:bg-primary/20 transition-colors">
                                    <Shield class="w-5 h-5 text-primary" />
                                </div>
                                <div class="overflow-hidden">
                                    <h3 class="font-semibold text-foreground capitalize truncate" :title="role.name">{{ role.name }}</h3>
                                    <p class="text-xs text-muted-foreground">
                                        {{ $t('features.roles.list.usersCount', { count: role.users_count || 0 }) }}
                                    </p>
                                </div>
                            </div>
                            <!-- Permission Count Badge -->
                             <Badge variant="secondary" class="font-normal shrink-0">
                                {{ role.permissions?.length || 0 }}
                            </Badge>
                        </div>
                        
                        <div class="space-y-2.5">
                            <div class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider">
                                {{ $t('features.roles.permissions') }}
                            </div>
                            <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                                <Badge
                                    v-for="permission in (role.permissions || []).slice(0, 5)"
                                    :key="permission.id"
                                    variant="outline"
                                    class="bg-muted/50 text-xs font-normal border-border/50"
                                >
                                    {{ permission.name }}
                                </Badge>
                                <span v-if="(role.permissions?.length || 0) > 5" class="text-xs text-muted-foreground py-0.5 px-1.5 flex items-center">
                                    +{{ role.permissions.length - 5 }} more
                                </span>
                                 <span v-if="(role.permissions?.length || 0) === 0" class="text-xs text-muted-foreground italic">
                                    No permissions
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-muted/30 border-t px-6 py-3 flex justify-between items-center mt-auto">
                        <div class="flex space-x-2">
                            <Button
                                v-if="authStore.hasPermission('edit roles')"
                                variant="ghost"
                                size="sm"
                                @click="editRole(role)"
                                :disabled="isProtectedRole(role.name)"
                                class="h-8 px-2 text-muted-foreground hover:text-foreground"
                            >
                                <Edit class="w-3.5 h-3.5 mr-1.5" />
                                {{ $t('common.actions.edit') }}
                            </Button>
                           <!-- Duplicate Button removed to simplify card actions, can be added back if essential -->
                        </div>
                        
                         <div class="flex gap-1">
                             <Button
                                v-if="authStore.hasPermission('create roles')"
                                variant="ghost"
                                size="icon"
                                @click="duplicateRole(role)"
                                 class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                 :title="$t('common.actions.duplicate')"
                            >
                                <Copy class="w-4 h-4" />
                            </Button>
                            <Button
                                v-if="!isProtectedRole(role.name) && authStore.hasPermission('delete roles')"
                                variant="ghost"
                                size="icon"
                                @click="deleteRole(role)"
                                class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                         </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="bg-card border border-border rounded-lg overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b">
                            <tr>
                                <th class="w-10 px-6 py-3">
                                    <Checkbox 
                                        :checked="isAllSelected"
                                        @update:checked="toggleSelectAll"
                                    />
                                </th>
                                <th class="px-6 py-3 font-medium">{{ $t('features.roles.form.name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ $t('features.roles.permissions') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ $t('common.actions.title') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr 
                                v-for="role in roles" 
                                :key="role.id" 
                                class="hover:bg-muted/50 transition-colors group"
                                :class="{ 'bg-muted/30': selectedRoles.includes(role.id) }"
                            >
                                <td class="px-6 py-4">
                                     <Checkbox 
                                        v-if="!isProtectedRole(role.name)"
                                        :checked="selectedRoles.includes(role.id)"
                                        @update:checked="(checked) => toggleSelection(role.id)"
                                    />
                                     <div v-else class="w-4 h-4"></div> <!-- Spacer for protected roles -->
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-primary/10 rounded-md group-hover:bg-primary/20 transition-colors">
                                            <Shield class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-foreground capitalize">{{ role.name }}</div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ $t('features.roles.list.usersCount', { count: role.users_count || 0 }) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-xl">
                                        <Badge
                                            v-for="permission in (role.permissions || []).slice(0, 3)"
                                            :key="permission.id"
                                            variant="outline"
                                            class="bg-muted/50 text-xs font-normal"
                                        >
                                            {{ permission.name }}
                                        </Badge>
                                        <Badge v-if="(role.permissions?.length || 0) > 3" variant="secondary" class="text-xs font-normal">
                                            +{{ role.permissions.length - 3 }}
                                        </Badge>
                                        <span v-if="(role.permissions?.length || 0) === 0" class="text-xs text-muted-foreground italic">
                                            No permissions
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                         <Button
                                            v-if="authStore.hasPermission('edit roles')"
                                            variant="ghost"
                                            size="icon"
                                            @click="editRole(role)"
                                            :disabled="isProtectedRole(role.name)"
                                            class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                            :title="$t('common.actions.edit')"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-if="authStore.hasPermission('create roles')"
                                            variant="ghost"
                                            size="icon"
                                            @click="duplicateRole(role)"
                                            class="h-8 w-8 text-muted-foreground hover:text-foreground"
                                            :title="$t('common.actions.duplicate')"
                                        >
                                            <Copy class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-if="!isProtectedRole(role.name) && authStore.hasPermission('delete roles')"
                                            variant="ghost"
                                            size="icon"
                                            @click="deleteRole(role)"
                                            class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                                            :title="$t('common.actions.delete')"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <Pagination
                    v-if="pagination.total > 0"
                    :total="pagination.total"
                    :per-page="pagination.per_page"
                    :current-page="pagination.current_page"
                    @page-change="changePage"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import Button from '../../../components/ui/button.vue';
import Badge from '../../../components/ui/badge.vue';
import Input from '../../../components/ui/input.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import Pagination from '../../../components/ui/pagination.vue';
import Select from '../../../components/ui/select.vue';
import SelectContent from '../../../components/ui/select-content.vue';
import SelectItem from '../../../components/ui/select-item.vue';
import SelectTrigger from '../../../components/ui/select-trigger.vue';
import SelectValue from '../../../components/ui/select-value.vue';
import {
    Edit,
    Copy,
    Trash2,
    Plus,
    Shield,
    LayoutGrid,
    List,
    Search,
    CheckSquare
} from 'lucide-vue-next';
import { useAuthStore } from '../../../stores/auth';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();
import { useConfirm } from '../../../composables/useConfirm';

const loading = ref(true);
const roles = ref([]);
const viewMode = ref(localStorage.getItem('rolesViewMode') || 'grid');
const search = ref('');
const bulkActionSelection = ref('');
const selectedRoles = ref([]);

// Pagination State
const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0
});

const protectedRoles = ['super-admin'];

const isProtectedRole = (name) => protectedRoles.includes(name);

watch(viewMode, (newMode) => {
    localStorage.setItem('rolesViewMode', newMode);
});

// Computed property for "Select All" state
const isAllSelected = computed(() => {
    const selectableRoles = roles.value.filter(r => !isProtectedRole(r.name));
    return selectableRoles.length > 0 && selectedRoles.value.length === selectableRoles.length;
});

const fetchRoles = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            search: search.value,
            limit: 12 // Adjust generic limit
        };
        const response = await api.get('/admin/cms/roles', { params });
        
        // Handle pagination response structure
        const data = response.data?.data || response.data || [];
        const meta = response.data?.meta || {};
        const links = response.data?.links || {};

        roles.value = Array.isArray(data) ? data : (data.data || []);
        
        // Update pagination
        if (response.data?.current_page) {
             pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total
            };
        } else {
            // Fallback if backend doesn't return pagination meta standardly yet (though we updated it)
            pagination.value = {
                current_page: 1,
                last_page: 1,
                per_page: roles.value.length,
                total: roles.value.length
            };
        }

        // Reset selection on page change
        selectedRoles.value = [];

    } catch (error) {
        console.error('Failed to fetch roles:', error);
        toast.error.load(error);
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
    fetchRoles(1);
}, 300);

const changePage = (page) => {
    fetchRoles(page);
};

const toggleSelection = (id) => {
    if (selectedRoles.value.includes(id)) {
        selectedRoles.value = selectedRoles.value.filter(roleId => roleId !== id);
    } else {
        selectedRoles.value.push(id);
    }
};

const toggleSelectAll = (checked) => {
    if (checked) {
        // Select all non-protected roles
        selectedRoles.value = roles.value
            .filter(r => !isProtectedRole(r.name))
            .map(r => r.id);
    } else {
        selectedRoles.value = [];
    }
};

const { confirm } = useConfirm();

// Bulk Actions
const handleBulkAction = async (action) => {
    if (!action) return;

    if (action === 'delete') {
        if (!selectedRoles.value.length) return;

        const confirmed = await confirm({
            title: t('common.messages.confirm.title'),
            message: t('common.messages.confirm.bulkDelete'),
            variant: 'danger',
            confirmText: t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkActionSelection.value = ''; // Reset selection
            return;
        }

        try {
            await api.post('/admin/cms/roles/bulk-action', {
                action: 'delete',
                ids: selectedRoles.value
            });
            
            toast.success.delete(`${selectedRoles.value.length} Roles`);
            selectedRoles.value = []; // Clear selection
            bulkActionSelection.value = ''; // Reset dropdown
            fetchRoles(pagination.value.current_page); // Refresh list
        } catch (error) {
            console.error('Bulk action failed:', error);
            toast.error.action(error);
        }
    }
};

const editRole = (role) => {
    router.push({ name: 'roles.edit', params: { id: role.id } });
};

const deleteRole = async (role) => {
    const confirmed = await confirm({
        title: t('common.messages.confirm.title'),
        message: t('features.roles.messages.deleteConfirm', { name: role.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/roles/${role.id}`);
        toast.success.delete('Role');
         fetchRoles(pagination.value.current_page);
    } catch (error) {
        console.error('Failed to delete role:', error);
        toast.error.delete(error, 'Role');
    }
};

const duplicateRole = async (role) => {
    try {
        await api.post(`/admin/cms/roles/${role.id}/duplicate`);
        toast.success.duplicate();
        fetchRoles(pagination.value.current_page);
    } catch (error) {
        console.error('Failed to duplicate role:', error);
        toast.error.action(error);
    }
};

onMounted(() => {
    fetchRoles();
});
</script>
