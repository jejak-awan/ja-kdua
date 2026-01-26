<template>
    <div class="h-[calc(100vh-140px)] flex flex-col">
        <!-- Header -->
        <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.roles.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.roles.subtitle') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="fetchRoles" :disabled="loading" class="h-9">
                    <RefreshCw :class="cn('w-4 h-4 mr-2', loading && 'animate-spin')" />
                    {{ $t('common.actions.refresh') }}
                </Button>
                <Button variant="default" v-if="authStore.hasPermission('create roles')" @click="createNewRole" class="h-9">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ $t('features.roles.create') }}
                </Button>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="flex-1 flex overflow-hidden border border-border rounded-xl bg-card shadow-sm">
            <!-- Left Sidebar: Role List -->
            <div class="w-72 border-r border-border bg-muted/20 flex flex-col shrink-0">
                <div class="p-4 border-b border-border space-y-3 bg-background/50">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            :placeholder="$t('common.actions.search')"
                            class="pl-9 h-9"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    <div v-if="loading && roles.length === 0" class="p-4 space-y-3">
                        <div v-for="i in 5" :key="i" class="h-10 w-full bg-muted animate-pulse rounded-lg"></div>
                    </div>
                    <div v-else-if="filteredRoles.length === 0" class="p-8 text-center text-muted-foreground text-sm italic">
                        No roles found
                    </div>
                    <div
                        v-for="role in filteredRoles"
                        :key="role.id"
                        class="group flex items-center gap-3 p-2.5 rounded-lg transition-all cursor-pointer relative"
                        :class="[
                            isSelected(role.id) ? 'bg-primary/5 border-primary/20' : 'hover:bg-muted/50 border-transparent',
                            activeRoleId === role.id ? 'ring-1 ring-primary/50 bg-primary/5 shadow-sm' : ''
                        ]"
                        @click="setActiveRole(role)"
                    >
                        <Checkbox 
                            :checked="isSelected(role.id)"
                            @update:checked="() => toggleSelection(role.id)"
                            @click.stop
                            class="shrink-0"
                            v-if="!isProtectedRole(role.name)"
                        />
                        <div v-else class="w-4 h-4 flex items-center justify-center shrink-0">
                            <Lock class="w-3 h-3 text-muted-foreground/50" />
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold truncate text-foreground group-hover:text-primary transition-colors">
                                    {{ role.name }}
                                </span>
                                <Badge v-if="isProtectedRole(role.name)" variant="outline" class="text-[9px] h-3.5 px-1 py-0 border-muted-foreground/30 text-muted-foreground">
                                    System
                                </Badge>
                            </div>
                            <div class="text-[10px] text-muted-foreground flex items-center gap-1.5 mt-0.5">
                                <Users class="w-3 h-3" />
                                {{ role.users_count || 0 }} Users
                            </div>
                        </div>

                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-0.5">
                             <Button
                                v-if="!isProtectedRole(role.name) && authStore.hasPermission('delete roles')"
                                variant="ghost"
                                size="icon"
                                @click.stop="deleteRole(role)"
                                class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </Button>
                        </div>
                    </div>
                </div>
                
                <!-- Footer / Bulk Actions -->
                <div v-if="selectedRoleIds.length > 0" class="p-3 border-t border-border bg-primary/5">
                    <div class="flex items-center justify-between mb-2 px-1">
                        <span class="text-[10px] uppercase font-bold text-primary tracking-wider">Comparison Mode</span>
                        <span class="text-[10px] font-bold text-primary">{{ selectedRoleIds.length }} selected</span>
                    </div>
                    <Button variant="ghost" size="xs" @click="selectedRoleIds = []" class="w-full h-7 text-[10px] font-bold uppercase hover:bg-primary/10">
                        Clear Selection
                    </Button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col bg-background overflow-hidden relative">
                <!-- Workspace Header -->
                <div class="h-14 border-b border-border flex items-center justify-between px-6 bg-card shrink-0 shadow-sm z-10">
                    <div class="flex items-center gap-3">
                        <div v-if="workspaceMode === 'comparison'" class="flex items-center gap-2">
                            <div class="p-1.5 bg-primary/10 rounded-lg">
                                <Columns class="w-4 h-4 text-primary" />
                            </div>
                            <h2 class="font-bold text-sm">Role Comparison Matrix</h2>
                        </div>
                        <div v-else-if="workspaceMode === 'edit'" class="flex items-center gap-2">
                            <div class="p-1.5 bg-primary/10 rounded-lg">
                                <Edit3 class="w-4 h-4 text-primary" />
                            </div>
                            <h2 class="font-bold text-sm">{{ activeRole?.name }}</h2>
                            <Badge variant="outline" class="text-[10px] border-primary/30 text-primary bg-primary/5">Edit Mode</Badge>
                        </div>
                         <div v-else-if="workspaceMode === 'create'" class="flex items-center gap-2">
                            <div class="p-1.5 bg-success/10 rounded-lg">
                                <Plus class="w-4 h-4 text-success" />
                            </div>
                            <h2 class="font-bold text-sm">Create New Role</h2>
                        </div>
                        <div v-else class="flex items-center gap-2 text-muted-foreground">
                            <Shield class="w-4 h-4" />
                            <span class="text-sm">Select a role to start managing permissions</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2" v-if="workspaceMode !== 'welcome'">
                        <Button
                            v-if="isDirty"
                            variant="ghost"
                            size="sm"
                            @click="resetForm"
                            class="h-8 text-xs"
                        >
                            Reset Changes
                        </Button>
                        <Button
                            variant="default"
                            size="sm"
                            @click="saveChanges"
                            :disabled="saving || !isDirty"
                            class="h-8 text-xs font-bold"
                        >
                            <Loader2 v-if="saving" class="w-3 h-3 mr-2 animate-spin" />
                            Save Configuration
                        </Button>
                    </div>
                </div>

                <!-- Workspace Body -->
                <div class="flex-1 overflow-y-auto custom-scrollbar relative p-6">
                    <!-- Welcome Screen -->
                    <div v-if="workspaceMode === 'welcome'" class="h-full flex flex-col items-center justify-center text-center p-12">
                        <div class="p-6 rounded-full bg-muted mb-6 flex items-center justify-center">
                            <Shield class="w-12 h-12 text-muted-foreground/40" />
                        </div>
                        <h3 class="text-lg font-bold mb-2">Role Management Workspace</h3>
                        <p class="text-muted-foreground text-sm max-w-md mx-auto leading-relaxed">
                            Click on a role in the sidebar to edit its permissions, or select multiple roles use the checkbox to compare them side-by-side.
                        </p>
                    </div>

                    <!-- Single Role Edit / Create Mode -->
                    <div v-else-if="workspaceMode === 'edit' || workspaceMode === 'create'" class="max-w-4xl space-y-8 animate-fade-in mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-muted/20 border border-border/40 rounded-xl">
                            <div>
                                <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1.5 block">Role Identity</label>
                                <Input
                                    v-model="form.name"
                                    :placeholder="$t('features.roles.form.namePlaceholder')"
                                    :disabled="isProtectedRole(activeRole?.name || '')"
                                    class="h-10 font-medium"
                                />
                                <p v-if="errors.name" class="text-xs text-destructive mt-1.5 font-medium">{{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}</p>
                            </div>
                             <div class="flex flex-col justify-end">
                                <div class="flex items-center gap-4 text-xs text-muted-foreground opacity-70">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                                        Live Editing
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <CheckCircle2 class="w-3.5 h-3.5 text-success" v-if="isDirty" />
                                        <Circle class="w-3.5 h-3.5" v-else />
                                        {{ isDirty ? 'Unsaved changes detected' : 'Configuration synced' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b">
                                <h4 class="text-sm font-bold border-l-2 border-primary pl-3">Permissions Matrix</h4>
                                <div class="flex items-center gap-2">
                                    <Button variant="ghost" size="xs" @click="expandAll" class="h-7 text-[10px] font-bold uppercase">Expand All</Button>
                                    <Button variant="ghost" size="xs" @click="collapseAll" class="h-7 text-[10px] font-bold uppercase">Collapse All</Button>
                                </div>
                            </div>

                            <Accordion type="multiple" class="space-y-4" v-model:modelValue="expandedCategories">
                                <AccordionItem 
                                    v-for="(perms, category) in groupedPermissions" 
                                    :key="category" 
                                    :value="String(category)"
                                    class="bg-card border border-border rounded-xl overflow-hidden shadow-sm"
                                >
                                    <AccordionTrigger class="px-5 py-3.5 bg-muted/30 hover:bg-muted/50 hover:no-underline [&[data-state=open]]:border-b [&[data-state=open]]:border-border/40 transition-all">
                                        <div class="flex items-center justify-between w-full pr-4">
                                            <div class="flex items-center gap-3">
                                                <div class="p-1.5 bg-background border border-border/50 rounded-lg group-data-[state=open]:bg-primary/5 transition-colors">
                                                    <FolderOpen class="w-4 h-4 text-muted-foreground group-data-[state=open]:text-primary" />
                                                </div>
                                                <div class="text-left">
                                                    <h5 class="text-xs font-black uppercase tracking-widest text-foreground">{{ category }}</h5>
                                                    <p class="text-[10px] text-muted-foreground mt-0.5">{{ perms.length }} permissions available</p>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="xs"
                                                @click.stop="toggleCategory(String(category))"
                                                class="h-7 text-[9px] font-black uppercase tracking-tighter bg-background hover:bg-primary hover:text-primary-foreground transition-all"
                                            >
                                                {{ isCategorySelected(String(category)) ? 'Deselect Category' : 'Select Category' }}
                                            </Button>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent>
                                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 bg-background/50">
                                            <div
                                                v-for="permission in perms"
                                                :key="permission.id"
                                                class="flex items-center space-x-3 p-2.5 rounded-lg border border-transparent hover:border-border/60 hover:bg-card transition-all cursor-pointer group/perm"
                                                @click="togglePermission(permission.name)"
                                            >
                                                <Checkbox
                                                    :checked="isSelectedPermission(permission.name)"
                                                    @update:checked="() => togglePermission(permission.name)"
                                                />
                                                <span class="text-xs font-medium text-muted-foreground group-hover/perm:text-foreground transition-colors pt-0.5">
                                                    {{ formatPermissionName(permission.name, String(category)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </div>

                    <!-- Comparison Matrix Mode -->
                    <div v-else-if="workspaceMode === 'comparison'" class="animate-fade-in h-full flex flex-col">
                         <div class="mb-6 rounded-xl border border-primary/20 bg-primary/5 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex -space-x-3">
                                    <div v-for="rId in selectedRoleIds.slice(0, 5)" :key="rId" class="w-10 h-10 rounded-full border-2 border-background bg-primary/10 flex items-center justify-center text-primary text-[10px] font-black uppercase shadow-sm">
                                        {{ getRole(rId)?.name.substring(0, 2) }}
                                    </div>
                                    <div v-if="selectedRoleIds.length > 5" class="w-10 h-10 rounded-full border-2 border-background bg-muted flex items-center justify-center text-muted-foreground text-[10px] font-bold shadow-sm">
                                        +{{ selectedRoleIds.length - 5 }}
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold">Syncing {{ selectedRoleIds.length }} Roles</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest mt-0.5">Permission Comparison Matrix</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" @click="selectedRoleIds = []" class="h-8 text-xs">Exit Comparison</Button>
                        </div>

                        <div class="space-y-6">
                            <Accordion type="multiple" class="space-y-4" v-model:modelValue="expandedCategories">
                                <AccordionItem 
                                    v-for="(perms, category) in groupedPermissions" 
                                    :key="category" 
                                    :value="String(category)"
                                    class="bg-card border border-border rounded-xl overflow-hidden shadow-sm"
                                >
                                    <AccordionTrigger class="px-5 py-3.5 bg-muted/30 hover:bg-muted/50 hover:no-underline transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="p-1.5 bg-background border border-border/50 rounded-lg">
                                                <FolderOpen class="w-4 h-4 text-muted-foreground" />
                                            </div>
                                            <h5 class="text-xs font-black uppercase tracking-widest text-foreground">{{ category }}</h5>
                                        </div>
                                    </AccordionTrigger>
                                    <AccordionContent>
                                        <div class="overflow-x-auto pb-4 custom-scrollbar">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow class="bg-muted/20 hover:bg-muted/20 border-b border-border/40">
                                                        <TableHead class="min-w-[200px] sticky left-0 bg-card z-20 shadow-sm border-r px-4 py-3">Permission</TableHead>
                                                        <TableHead 
                                                            v-for="rId in selectedRoleIds" 
                                                            :key="rId" 
                                                            class="min-w-[120px] text-center px-4 py-3"
                                                        >
                                                            <div class="flex flex-col items-center gap-1.5">
                                                                <span class="text-[10px] font-black uppercase tracking-tighter truncate max-w-[100px]" :title="getRole(rId)?.name">
                                                                    {{ getRole(rId)?.name }}
                                                                </span>
                                                                <Button 
                                                                    variant="ghost" 
                                                                    size="icon" 
                                                                    class="h-5 w-5 hover:bg-primary/20 hover:text-primary transition-colors"
                                                                    @click.stop="toggleCategoryForRole(String(category), rId)"
                                                                    :title="'Toggle category for ' + getRole(rId)?.name"
                                                                >
                                                                    <CheckSquare class="w-3 h-3" />
                                                                </Button>
                                                            </div>
                                                        </TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    <TableRow 
                                                        v-for="permission in perms" 
                                                        :key="permission.id"
                                                        class="border-b border-border/40 hover:bg-muted/10 transition-colors"
                                                    >
                                                        <TableCell class="sticky left-0 bg-card z-20 shadow-sm border-r font-medium text-xs px-4 py-3">
                                                            {{ formatPermissionName(permission.name, String(category)) }}
                                                        </TableCell>
                                                        <TableCell 
                                                            v-for="rId in selectedRoleIds" 
                                                            :key="rId" 
                                                            class="text-center px-4 py-3"
                                                        >
                                                            <Checkbox
                                                                :checked="isRoleHasPermission(roleIdToNum(rId), permission.name)"
                                                                @update:checked="() => togglePermissionForRole(roleIdToNum(rId), permission.name)"
                                                                class="mx-auto"
                                                            />
                                                        </TableCell>
                                                    </TableRow>
                                                </TableBody>
                                            </Table>
                                        </div>
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </div>
                </div>

                <!-- Global Overlay Loading -->
                <div v-if="saving" class="absolute inset-0 bg-background/60 backdrop-blur-[2px] z-50 flex items-center justify-center animate-fade-in">
                    <div class="bg-card border border-border shadow-xl rounded-2xl p-8 flex flex-col items-center gap-4">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full border-4 border-primary/20 border-t-primary animate-spin"></div>
                            <Shield class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-5 h-5 text-primary" />
                        </div>
                        <div class="text-center">
                            <h4 class="font-bold text-sm">Saving Security Configuration</h4>
                            <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-1">Applying permissions across roles...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useAuthStore } from '@/stores/auth';
import { cn } from '@/lib/utils';
import { roleSchema } from '@/schemas';
import { useFormValidation } from '@/composables/useFormValidation';

// UI Components
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Badge from '@/components/ui/badge.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Table from '@/components/ui/table.vue';
// @ts-ignore
import TableHeader from '@/components/ui/table-header.vue';
// @ts-ignore
import TableRow from '@/components/ui/table-row.vue';
// @ts-ignore
import TableHead from '@/components/ui/table-head.vue';
// @ts-ignore
import TableBody from '@/components/ui/table-body.vue';
// @ts-ignore
import TableCell from '@/components/ui/table-cell.vue';
// @ts-ignore
import Accordion from '@/components/ui/accordion.vue';
// @ts-ignore
import AccordionItem from '@/components/ui/accordion-item.vue';
// @ts-ignore
import AccordionTrigger from '@/components/ui/accordion-trigger.vue';
// @ts-ignore
import AccordionContent from '@/components/ui/accordion-content.vue';

import {
    Plus,
    RefreshCw,
    Search,
    Shield,
    Users,
    Edit3,
    Trash2,
    Lock,
    Columns,
    Loader2,
    CheckCircle2,
    Circle,
    FolderOpen,
    CheckSquare
} from 'lucide-vue-next';

import type { Role, Permission } from '@/types/auth';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const authStore = useAuthStore();
const { errors, validateWithZod, clearErrors, setErrors } = useFormValidation(roleSchema);

// State
const loading = ref(false);
const saving = ref(false);
const roles = ref<Role[]>([]);
const permissions = ref<Record<string, Permission[]>>({});
const search = ref('');
const activeRoleId = ref<number | null>(null);
const selectedRoleIds = ref<number[]>([]);
const expandedCategories = ref<string[]>([]);
const initialData = ref<any>(null);

// Form State
const form = ref<{
    name: string;
    permissions: string[]; 
    matrixPermissions: Record<number, string[]>;
}>({
    name: '',
    permissions: [],
    matrixPermissions: {}
});

const protectedRoles = ['super-admin'];
const isProtectedRole = (name: string) => protectedRoles.includes(name);

// Computed
const filteredRoles = computed(() => {
    if (!search.value) return roles.value;
    const s = search.value.toLowerCase();
    return roles.value.filter(r => r.name.toLowerCase().includes(s));
});

const activeRole = computed(() => roles.value.find(r => r.id === activeRoleId.value) || null);

const workspaceMode = computed(() => {
    if (selectedRoleIds.value.length > 1) return 'comparison';
    if (activeRoleId.value === -1) return 'create';
    if (activeRoleId.value) return 'edit';
    return 'welcome';
});

const groupedPermissions = computed(() => permissions.value);

const isDirty = computed(() => {
    if (!initialData.value) return false;
    
    if (workspaceMode.value === 'create') return !!form.value.name || form.value.permissions.length > 0;
    
    if (workspaceMode.value === 'edit') {
        const nameChanged = form.value.name !== initialData.value.name;
        const currentPerms = [...form.value.permissions].sort().join(',');
        const prevPerms = [...initialData.value.permissions].sort().join(',');
        return nameChanged || currentPerms !== prevPerms;
    }
    
    if (workspaceMode.value === 'comparison') {
        return JSON.stringify(form.value.matrixPermissions) !== JSON.stringify(initialData.value.matrixPermissions);
    }
    
    return false;
});

// Methods
const fetchRoles = async () => {
    loading.value = true;
    try {
        const response: any = await api.get('/admin/ja/roles?limit=100');
        roles.value = response.data?.data || response.data || [];
        if (workspaceMode.value === 'comparison') syncMatrixFromRoles();
    } catch (error) {
        toast.error.load(error);
    } finally {
        loading.value = false;
    }
};

const fetchPermissions = async () => {
    try {
        const response: any = await api.get('/admin/ja/roles/permissions');
        permissions.value = response.data?.data || response.data || {};
        expandedCategories.value = Object.keys(permissions.value).slice(0, 2);
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    }
};

const setActiveRole = (role: Role) => {
    activeRoleId.value = role.id;
    selectedRoleIds.value = []; // Clear matrix selection when editing single role
    form.value.name = role.name;
    form.value.permissions = (role.permissions || []).map(p => p.name);
    initialData.value = {
        name: form.value.name,
        permissions: [...form.value.permissions]
    };
    clearErrors();
};

const createNewRole = () => {
    activeRoleId.value = -1;
    selectedRoleIds.value = [];
    form.value.name = '';
    form.value.permissions = [];
    initialData.value = { name: '', permissions: [] };
    clearErrors();
};

const isSelected = (id: number) => selectedRoleIds.value.includes(id);

const toggleSelection = (id: number) => {
    const index = selectedRoleIds.value.indexOf(id);
    if (index > -1) {
        selectedRoleIds.value.splice(index, 1);
    } else {
        selectedRoleIds.value.push(id);
        activeRoleId.value = null; // Exit edit mode
    }
};

const syncMatrixFromRoles = () => {
    const matrix: Record<number, string[]> = {};
    selectedRoleIds.value.forEach(rId => {
        const role = getRole(rId);
        if (role) {
            matrix[rId] = (role.permissions || []).map(p => p.name);
        }
    });
    form.value.matrixPermissions = matrix;
    initialData.value = { matrixPermissions: JSON.parse(JSON.stringify(matrix)) };
};

const getRole = (id: number | string) => roles.value.find(r => String(r.id) === String(id));
const roleIdToNum = (id: any) => Number(id);

// Permission Handlers
const togglePermission = (name: string) => {
    if (isProtectedRole(activeRole.value?.name || '')) return;
    const index = form.value.permissions.indexOf(name);
    if (index > -1) form.value.permissions.splice(index, 1);
    else form.value.permissions.push(name);
};

const isSelectedPermission = (name: string) => form.value.permissions.includes(name);

const isCategorySelected = (category: string) => {
    const categoryPerms = permissions.value[category] || [];
    if (categoryPerms.length === 0) return false;
    return categoryPerms.every(p => form.value.permissions.includes(p.name));
};

const toggleCategory = (category: string) => {
    if (isProtectedRole(activeRole.value?.name || '')) return;
    const categoryPerms = permissions.value[category] || [];
    const categoryNames = categoryPerms.map(p => p.name);
    if (isCategorySelected(category)) {
        form.value.permissions = form.value.permissions.filter(p => !categoryNames.includes(p));
    } else {
        const toAdd = categoryNames.filter(name => !form.value.permissions.includes(name));
        form.value.permissions.push(...toAdd);
    }
};

const isRoleHasPermission = (roleId: number, name: string) => (form.value.matrixPermissions[roleId] || []).includes(name);

const togglePermissionForRole = (roleId: number, name: string) => {
    const role = getRole(roleId);
    if (role && isProtectedRole(role.name)) return;
    
    const rolePerms = form.value.matrixPermissions[roleId] || [];
    const index = rolePerms.indexOf(name);
    if (index > -1) rolePerms.splice(index, 1);
    else rolePerms.push(name);
    form.value.matrixPermissions[roleId] = [...rolePerms];
};

const toggleCategoryForRole = (category: string, roleId: number) => {
    const role = getRole(roleId);
    if (role && isProtectedRole(role.name)) return;

    const categoryPerms = permissions.value[category] || [];
    const categoryNames = categoryPerms.map(p => p.name);
    const currentRolePerms = form.value.matrixPermissions[roleId] || [];
    const allSelected = categoryNames.every(name => currentRolePerms.includes(name));

    if (allSelected) {
        form.value.matrixPermissions[roleId] = currentRolePerms.filter(p => !categoryNames.includes(p));
    } else {
        const toAdd = categoryNames.filter(name => !currentRolePerms.includes(name));
        form.value.matrixPermissions[roleId] = [...currentRolePerms, ...toAdd];
    }
};

const saveChanges = async () => {
    saving.value = true;
    clearErrors();
    try {
        if (workspaceMode.value === 'create') {
            if (!validateWithZod({ name: form.value.name, permissions: form.value.permissions })) return;
            await api.post('/admin/ja/roles', { name: form.value.name, permissions: form.value.permissions });
            toast.success.create('Role');
            activeRoleId.value = null;
        } else if (workspaceMode.value === 'edit') {
            if (!activeRoleId.value) return;
            if (!validateWithZod({ name: form.value.name, permissions: form.value.permissions })) return;
            await api.put(`/admin/ja/roles/${activeRoleId.value}`, { name: form.value.name, permissions: form.value.permissions });
            toast.success.update('Role');
        } else if (workspaceMode.value === 'comparison') {
            const promises = Object.entries(form.value.matrixPermissions).map(([rId, perms]) => {
                const role = getRole(rId);
                return api.put(`/admin/ja/roles/${rId}`, { name: role?.name, permissions: perms });
            });
            await Promise.all(promises);
            toast.success.update('Roles synced successfully');
        }
        await fetchRoles();
    } catch (error: any) {
        if (error.response?.status === 422) setErrors(error.response.data.errors || {});
        else toast.error.action(error);
    } finally {
        saving.value = false;
    }
};

const resetForm = () => {
    if (workspaceMode.value === 'edit' && initialData.value) {
        form.value.name = initialData.value.name;
        form.value.permissions = [...initialData.value.permissions];
    } else if (workspaceMode.value === 'comparison' && initialData.value) {
        form.value.matrixPermissions = JSON.parse(JSON.stringify(initialData.value.matrixPermissions));
    } else if (workspaceMode.value === 'create') {
        form.value.name = '';
        form.value.permissions = [];
    }
    clearErrors();
};

const deleteRole = async (role: Role) => {
    const confirmed = await confirm({ title: 'Delete Role', message: `Delete "${role.name}"?`, variant: 'danger' });
    if (!confirmed) return;
    try {
        await api.delete(`/admin/ja/roles/${role.id}`);
        toast.success.delete('Role');
        if (activeRoleId.value === role.id) activeRoleId.value = null;
        selectedRoleIds.value = selectedRoleIds.value.filter(id => id !== role.id);
        fetchRoles();
    } catch (error) {
        toast.error.delete(error, 'Role');
    }
};

const expandAll = () => expandedCategories.value = Object.keys(permissions.value);
const collapseAll = () => expandedCategories.value = [];

const formatPermissionName = (name: string, category: string) => {
    const lowerCategory = category.toLowerCase();
    let formatted = name.toLowerCase().replace(lowerCategory, '').trim();
    if (!formatted) return name;
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
};

const handleRouteParams = () => {
    const { name, params } = router.currentRoute.value;
    if (name === 'roles.create') {
        createNewRole();
    } else if (name === 'roles.edit' && params.id) {
        const role = roles.value.find(r => String(r.id) === String(params.id));
        if (role) setActiveRole(role);
        else {
            // If roles not loaded yet, fetchRoles handles it via watch
        }
    }
};

watch(() => roles.value, (newRoles) => {
    if (newRoles.length > 0) handleRouteParams();
}, { once: true });

watch(() => router.currentRoute.value, () => {
    handleRouteParams();
});

onMounted(async () => {
    loading.value = true;
    await fetchPermissions();
    await fetchRoles();
    loading.value = false;
    handleRouteParams();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--border)); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: hsl(var(--muted-foreground) / 0.3); }

.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
