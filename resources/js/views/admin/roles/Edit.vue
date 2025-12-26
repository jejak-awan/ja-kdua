<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.edit') }} {{ $t('features.roles.title_singular') }}</h1>
            <router-link
                :to="{ name: 'roles' }"
                class="text-muted-foreground hover:text-foreground text-sm flex items-center"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ $t('common.actions.back') }}
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ $t('common.messages.loading.default') }}</p>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content -->
            <div class="bg-card border border-border rounded-lg p-6 space-y-6">
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
                        :class="{'opacity-50 cursor-not-allowed': isProtectedRole(form.name)}"
                        :placeholder="$t('features.roles.form.namePlaceholder')"
                    />
                </div>

                <!-- Permissions Matrix -->
                <div>
                    <h4 class="text-lg font-medium text-foreground mb-4">{{ $t('features.roles.permissions') }}</h4>
                    <div class="space-y-4">
                        <div v-for="(perms, category) in groupedPermissions" :key="category" class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-medium text-foreground capitalize">{{ category }}</h5>
                                <button
                                    type="button"
                                    @click="toggleCategory(category)"
                                    class="text-xs text-primary hover:text-primary/90"
                                >
                                    {{ isCategorySelected(category) ? $t('features.roles.form.deselectAll') : $t('features.roles.form.selectAll') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                <label
                                    v-for="permission in perms"
                                    :key="permission.id"
                                    class="flex items-center space-x-2 bg-transparent border border-input px-3 py-2 rounded-md cursor-pointer hover:bg-accent hover:text-accent-foreground transition-colors"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                        class="h-4 w-4 text-primary focus:ring-primary border-input rounded"
                                    />
                                    <span class="text-sm text-foreground select-none">{{ permission.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                    :disabled="saving"
                >
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
import Button from '../../../components/ui/button.vue';
import Input from '../../../components/ui/input.vue';

const router = useRouter();
const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const permissions = ref({});

const form = ref({
    name: '',
    permissions: []
});

const protectedRoles = ['super-admin', 'admin'];
const isProtectedRole = (name) => protectedRoles.includes(name);

const groupedPermissions = computed(() => permissions.value);

const fetchPermissions = async () => {
    try {
        const response = await api.get('/admin/cms/roles/permissions');
        permissions.value = response.data?.data || response.data || {};
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    }
};

const fetchRole = async () => {
    try {
        const response = await api.get(`/admin/cms/roles/${route.params.id}`);
        const role = response.data?.data || response.data;
        
        form.value = {
            name: role.name,
            permissions: (role.permissions || []).map(p => p.name)
        };
    } catch (error) {
        console.error('Failed to fetch role:', error);
        alert(t('features.roles.messages.fetchFailed'));
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

const handleSubmit = async () => {
    if (!form.value.name) {
        alert(t('features.roles.messages.enterName'));
        return;
    }

    saving.value = true;
    try {
        await api.put(`/admin/cms/roles/${route.params.id}`, form.value);
        router.push({ name: 'roles' });
    } catch (error) {
        console.error('Failed to update role:', error);
        alert(error.response?.data?.message || t('features.roles.messages.saveFailed'));
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
