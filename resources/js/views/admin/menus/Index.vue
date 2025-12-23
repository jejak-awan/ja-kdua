<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.title') }}</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('features.menus.actions.create') }}
            </button>
        </div>

        <div class="bg-card shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
            </div>

            <div v-else-if="menus.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ t('features.menus.messages.empty') }}</p>
            </div>

            <table v-else class="min-w-full divide-y divide-border">
                <thead class="bg-muted">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.menus.headers.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.menus.headers.location') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.menus.headers.items') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                            {{ t('features.menus.headers.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <tr v-for="menu in menus" :key="menu.id" class="hover:bg-muted">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ menu.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ menu.location || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            {{ menu.items_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <router-link
                                    :to="{ name: 'menus.edit', params: { id: menu.id } }"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ t('features.menus.actions.edit') }}
                                </router-link>
                                <button
                                    @click="deleteMenu(menu)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    {{ t('features.menus.actions.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create Menu Modal -->
        <MenuModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @saved="handleMenuSaved"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();
import MenuModal from '../../../components/menus/MenuModal.vue';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const menus = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);

const fetchMenus = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/menus');
        const { data } = parseResponse(response);
        menus.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch menus:', error);
    } finally {
        loading.value = false;
    }
};

const deleteMenu = async (menu) => {
    if (!confirm(t('features.menus.messages.deleteConfirm', { name: menu.name }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/menus/${menu.id}`);
        await fetchMenus();
    } catch (error) {
        console.error('Failed to delete menu:', error);
        alert(t('features.menus.messages.deleteFailed'));
    }
};

const handleMenuSaved = () => {
    fetchMenus();
    showCreateModal.value = false;
};

onMounted(() => {
    fetchMenus();
});
</script>

