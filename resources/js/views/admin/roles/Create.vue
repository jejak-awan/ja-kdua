<template>
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('common.actions.create') }} {{ $t('features.roles.title_singular') }}</h1>
            <router-link
                :to="{ name: 'roles' }"
                class="text-muted-foreground hover:text-foreground"
            >
                ← {{ $t('common.actions.back') }}
            </router-link>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Content -->
            <div class="bg-card border border-border rounded-lg p-6 space-y-6">
                <!-- Role Name -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        {{ $t('features.roles.form.name') }} <span class="text-destructive">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :placeholder="$t('features.roles.form.namePlaceholder')"
                    />
                </div>

                <!-- Permissions Matrix -->
                <div>
                    <h4 class="text-lg font-medium text-foreground mb-4">{{ $t('features.roles.permissions') }}</h4>
                    <div v-if="loadingPermissions" class="text-sm text-muted-foreground">
                        {{ $t('common.messages.loading.default') }}
                    </div>
                    <div v-else class="space-y-4">
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
                <router-link
                    :to="{ name: 'roles' }"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
                >
                    {{ $t('common.actions.cancel') }}
                </router-link>
                <button
                    type="submit"
                    :disabled="saving"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/80 h-10 px-4 py-2"
                >
                    {{ saving ? $t('common.messages.loading.creating') : $t('common.actions.create') }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const router = useRouter();
const { t } = useI18n();

const saving = ref(false);
const loadingPermissions = ref(false);
const permissions = ref({});

const form = ref({
    name: '',
    permissions: []
});

const groupedPermissions = computed(() => permissions.value);

const fetchPermissions = async () => {
    loadingPermissions.value = true;
    try {
        const response = await api.get('/admin/cms/roles/permissions');
        permissions.value = response.data?.data || response.data || {};
    } catch (error) {
        console.error('Failed to fetch permissions:', error);
    } finally {
        loadingPermissions.value = false;
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
        await api.post('/admin/cms/roles', form.value);
        router.push({ name: 'roles' });
    } catch (error) {
        console.error('Failed to create role:', error);
        alert(error.response?.data?.message || t('features.roles.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchPermissions();
});
</script>
