<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.create') }} {{ $t('features.roles.title_singular') }}</h1>
            <Button variant="ghost" as-child class="gap-2">
                <router-link :to="{ name: 'roles' }">
                    <ArrowLeft class="w-4 h-4" />
                    {{ $t('common.actions.back') }}
                </router-link>
            </Button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
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
                            :placeholder="$t('features.roles.form.namePlaceholder')"
                            class="max-w-md"
                            :class="cn(errors.name ? 'border-destructive focus-visible:ring-destructive' : '')"
                        />
                        <p v-if="errors.name" class="text-sm text-destructive mt-1">
                            {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                        </p>
                    </div>

                    <!-- Permissions Matrix -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-foreground uppercase tracking-wider">{{ $t('features.roles.permissions') }}</h4>
                        </div>
                        
                        <div v-if="loadingPermissions" class="flex items-center gap-2 text-xs text-muted-foreground py-8 justify-center bg-muted/30 rounded-lg border border-dashed">
                            <Loader2 class="w-4 h-4 animate-spin text-primary" />
                            {{ $t('common.messages.loading.default') }}
                        </div>
                        <div v-else class="grid grid-cols-1 gap-4">
                            <div v-for="(perms, category) in groupedPermissions" :key="category" class="bg-muted/30 border border-border/60 rounded-xl overflow-hidden">
                                <div class="px-4 py-3 bg-muted/50 border-b border-border/40 flex items-center justify-between">
                                    <h5 class="text-xs font-bold text-foreground uppercase tracking-widest flex items-center gap-2">
                                        {{ category }}
                                        <Badge variant="secondary" class="h-4.5 text-[10px] px-1.5 font-bold bg-background/50">
                                            {{ perms.length }}
                                        </Badge>
                                    </h5>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="toggleCategory(category as string)"
                                        class="h-7 text-[10px] font-bold uppercase tracking-wider hover:bg-background/50"
                                    >
                                        {{ isCategorySelected(category as string) ? $t('features.roles.form.deselectAll') : $t('features.roles.form.selectAll') }}
                                    </Button>
                                </div>
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div
                                        v-for="permission in perms"
                                        :key="permission.id"
                                        class="flex items-start space-x-2 p-2 rounded-md hover:bg-muted/50 transition-colors"
                                    >
                                        <Checkbox
                                            :id="`perm-${permission.id}`"
                                            :checked="form.permissions.includes(permission.name)"
                                            @update:checked="(checked: boolean) => togglePermission(checked, permission.name)"
                                        />
                                        <label
                                            :for="`perm-${permission.id}`"
                                            class="text-xs font-medium leading-none text-muted-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer pt-0.5"
                                        >
                                            {{ formatPermissionName(permission.name, category as string) }}
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
                    :disabled="saving || !isValid"
                    class="min-w-[100px]"
                >
                    <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? $t('common.messages.loading.creating') : $t('common.actions.create') }}
                </Button>
            </div>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useFormValidation } from '@/composables/useFormValidation';
import { roleSchema } from '@/schemas';
// @ts-ignore
import Button from '@/components/ui/button.vue';
// @ts-ignore
import Input from '@/components/ui/input.vue';
// @ts-ignore
import Checkbox from '@/components/ui/checkbox.vue';
// @ts-ignore
import Card from '@/components/ui/card.vue';
// @ts-ignore
import CardHeader from '@/components/ui/card-header.vue';
// @ts-ignore
import CardTitle from '@/components/ui/card-title.vue';
// @ts-ignore
import CardContent from '@/components/ui/card-content.vue';
import { ArrowLeft, Check, Loader2 } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import type { Permission } from '@/types/auth';

const router = useRouter();
const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(roleSchema);

const saving = ref(false);
const loadingPermissions = ref(false);
const permissions = ref<Record<string, Permission[]>>({});

const form = ref<{
    name: string;
    permissions: string[];
}>({
    name: '',
    permissions: []
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const groupedPermissions = computed(() => permissions.value);

const fetchPermissions = async () => {
    loadingPermissions.value = true;
    try {
        const response: any = await api.get('/admin/ja/roles/permissions');
        // Structure is usually { data: { Category: [{...}] } } or direct object
        permissions.value = response.data?.data || response.data || {};
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
        toast.error.action(t('common.messages.error.generic'));
    } finally {
        loadingPermissions.value = false;
    }
};

const toggleCategory = (category: string) => {
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

const isCategorySelected = (category: string) => {
    const categoryPerms = permissions.value[category] || [];
    if (categoryPerms.length === 0) return false;
    return categoryPerms.every(p => form.value.permissions.includes(p.name));
};

const togglePermission = (checked: boolean, permissionName: string) => {
    if (checked) {
        if (!form.value.permissions.includes(permissionName)) {
            form.value.permissions.push(permissionName);
        }
    } else {
        form.value.permissions = form.value.permissions.filter(p => p !== permissionName);
    }
};

const formatPermissionName = (name: string, category: string) => {
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
        await api.post('/admin/ja/roles', form.value);
        toast.success.create('Role');
        router.push({ name: 'roles' });
    } catch (error: any) {
        if (error.response?.status === 422) {
            setErrors(error.response.data.errors || {});
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchPermissions();
});
</script>
