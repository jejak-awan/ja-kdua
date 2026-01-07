<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.roles.title_singular') }}</h1>
            <Button variant="ghost" as-child class="gap-2">
                <router-link :to="{ name: 'roles' }">
                    <ArrowLeft class="w-4 h-4" />
                    {{ $t('common.actions.back') }}
                </router-link>
            </Button>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <Loader2 class="w-8 h-8 animate-spin mb-4" />
            <p>{{ $t('common.messages.loading.default') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content -->
            <Card>
                <CardContent class="p-6 space-y-6">
                    <!-- Role Name -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            {{ $t('features.roles.form.name') }} <span class="text-destructive">*</span>
                        </label>
                        <Input
                            v-model="form.name"
                            type="text"
                            required
                            :disabled="isProtectedRole(form.name)"
                            :class="{'opacity-50 cursor-not-allowed': isProtectedRole(form.name), 'border-destructive focus-visible:ring-destructive': errors.name}"
                            :placeholder="$t('features.roles.form.namePlaceholder')"
                            class="max-w-md"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive mt-1">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                         <p v-if="isProtectedRole(form.name)" class="text-xs text-muted-foreground mt-1">
                            This role name cannot be changed.
                        </p>
                    </div>

                    <!-- Permissions Matrix -->
                    <div>
                        <h4 class="text-lg font-medium text-foreground mb-4">{{ $t('features.roles.permissions') }}</h4>
                        <div class="space-y-4">
                            <div v-for="(perms, category) in groupedPermissions" :key="category" class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-medium text-foreground capitalize flex items-center gap-2">
                                        {{ category }}
                                        <span class="text-xs font-normal text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
                                            {{ perms.length }}
                                        </span>
                                    </h5>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="toggleCategory(category)"
                                        class="h-7 text-xs"
                                    >
                                        {{ isCategorySelected(category) ? $t('features.roles.form.deselectAll') : $t('features.roles.form.selectAll') }}
                                    </Button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div
                                        v-for="permission in perms"
                                        :key="permission.id"
                                        class="flex items-start space-x-2 p-2 rounded-md hover:bg-muted/50 transition-colors"
                                    >
                                        <Checkbox
                                            :id="`perm-${permission.id}`"
                                            :checked="form.permissions.includes(permission.name)"
                                            @update:checked="(checked) => togglePermission(checked, permission.name)"
                                        />
                                        <label
                                            :for="`perm-${permission.id}`"
                                            class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer pt-0.5"
                                        >
                                            {{ formatPermissionName(permission.name, category) }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <Button
                    variant="outline"
                    as-child
                >
                    <router-link :to="{ name: 'roles' }">
                        {{ $t('common.actions.cancel') }}
                    </router-link>
                </Button>
                <Button
                    type="submit"
                    :disabled="saving || !isDirty"
                    class="min-w-[100px]"
                >
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? $t('common.messages.loading.saving') : $t('common.actions.save') }}
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';
import { useToast } from '../../../composables/useToast';
import { useFormValidation } from '../../../composables/useFormValidation';
import { roleSchema } from '../../../schemas';
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';
import Checkbox from '../../../components/ui/checkbox.vue';
import Card from '../../../components/ui/card.vue';
import CardHeader from '../../../components/ui/card-header.vue';
import CardTitle from '../../../components/ui/card-title.vue';
import CardContent from '../../../components/ui/card-content.vue';
import { ArrowLeft, Check, Loader2 } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(roleSchema);

const loading = ref(true);
const saving = ref(false);
const permissions = ref({});

const form = ref({
    name: '',
    permissions: []
});

const initialForm = ref(null);

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    // For roles, we need to compare name and permissions array
    const nameChanged = form.value.name !== initialForm.value.name;
    const permsChanged = JSON.stringify(form.value.permissions.sort()) !== JSON.stringify(initialForm.value.permissions.sort());
    return nameChanged || permsChanged;
});

const protectedRoles = ['super-admin'];
const isProtectedRole = (name) => protectedRoles.includes(name);

const groupedPermissions = computed(() => permissions.value);

const fetchPermissions = async () => {
    try {
        const response = await api.get('/admin/ja/roles/permissions');
        permissions.value = response.data?.data || response.data || {};
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
        toast.error(t('features.roles.messages.fetchFailed'));
    }
};

const fetchRole = async () => {
    try {
        const response = await api.get(`/admin/ja/roles/${route.params.id}`);
        const role = response.data?.data || response.data;
        
        form.value = {
            name: role.name,
            permissions: (role.permissions || []).map(p => p.name)
        };
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } catch (error) {
        console.error('Failed to fetch role:', error);
        toast.error(t('features.roles.messages.fetchFailed'));
        router.push({ name: 'roles' });
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
    if (categoryPerms.length === 0) return false;
    return categoryPerms.every(p => form.value.permissions.includes(p.name));
};

const togglePermission = (checked, permissionName) => {
    if (checked) {
        if (!form.value.permissions.includes(permissionName)) {
            form.value.permissions.push(permissionName);
        }
    } else {
        form.value.permissions = form.value.permissions.filter(p => p !== permissionName);
    }
};

const formatPermissionName = (name, category) => {
    if (!name || !category) return name;
    // Special case for 'manage X' vs 'X' category
    const lowerName = name.toLowerCase();
    const lowerCategory = category.toLowerCase();
    
    // Replace category name from permission
    let formatted = lowerName.replace(lowerCategory, '').trim();
    
    // If we removed everything (e.g. 'content' category, 'content' permission?), shouldn't happen with our verb logic
    if (!formatted) return name;
    
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.put(`/admin/ja/roles/${route.params.id}`, form.value);
        initialForm.value = JSON.parse(JSON.stringify(form.value));
        toast.success.update('Role');
        router.push({ name: 'roles' });
    } catch (error) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    loading.value = true;
    await fetchPermissions();
    await fetchRole();
    loading.value = false;
});
</script>
