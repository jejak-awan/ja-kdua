<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ $t('features.roles.title') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $t('features.roles.subtitle') }}</p>
            </div>
            <router-link
                :to="{ name: 'roles.create' }"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ $t('features.roles.create') }}
            </router-link>
        </div>

        <!-- Roles Grid -->
        <div v-if="loading" class="bg-card border border-border rounded-lg p-12 text-center">
            <p class="text-muted-foreground">{{ $t('features.roles.list.loading') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="role in roles"
                :key="role.id"
                class="bg-card border border-border rounded-lg overflow-hidden"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-foreground capitalize">{{ role.name }}</h3>
                        <span class="px-2 py-1 text-xs bg-primary/10 text-primary rounded-full">
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
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="editRole(role)"
                            :disabled="isProtectedRole(role.name)"
                        >
                            {{ $t('common.actions.edit') }}
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="duplicateRole(role)"
                        >
                            {{ $t('common.actions.duplicate') }}
                        </Button>
                    </div>
                    <Button
                        v-if="!isProtectedRole(role.name)"
                        variant="ghost"
                        size="sm"
                        @click="deleteRole(role)"
                        class="text-destructive hover:text-destructive hover:bg-destructive/10"
                    >
                        {{ $t('common.actions.delete') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import api from '../../../services/api';
import Button from '../../../components/ui/button.vue';

const { t } = useI18n();
const router = useRouter();

const loading = ref(true);
const roles = ref([]);

const protectedRoles = ['super-admin', 'admin'];

const isProtectedRole = (name) => protectedRoles.includes(name);

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

const editRole = (role) => {
    router.push({ name: 'roles.edit', params: { id: role.id } });
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

onMounted(() => {
    fetchRoles();
});
</script>
