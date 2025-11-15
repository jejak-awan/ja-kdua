<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Menus</h1>
            <button
                @click="showCreateModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Menu
            </button>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="menus.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No menus found</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Items
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="menu in menus" :key="menu.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ menu.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ menu.location || '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ menu.items_count || 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <router-link
                                    :to="{ name: 'menus.edit', params: { id: menu.id } }"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Edit
                                </router-link>
                                <button
                                    @click="deleteMenu(menu)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
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
import api from '../../../services/api';
import MenuModal from '../../../components/menus/MenuModal.vue';

const menus = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);

const fetchMenus = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/menus');
        menus.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch menus:', error);
    } finally {
        loading.value = false;
    }
};

const deleteMenu = async (menu) => {
    if (!confirm(`Are you sure you want to delete menu "${menu.name}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/menus/${menu.id}`);
        await fetchMenus();
    } catch (error) {
        console.error('Failed to delete menu:', error);
        alert('Failed to delete menu');
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

