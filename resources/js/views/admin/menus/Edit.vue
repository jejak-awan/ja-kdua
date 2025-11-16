<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Menu</h1>
                <p v-if="menu" class="text-sm text-gray-500 mt-1">{{ menu.name }}</p>
            </div>
            <button
                @click="addMenuItem"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </button>
        </div>

        <div v-if="loading" class="text-center py-12">
            <p class="text-gray-500">Loading...</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Menu Items Tree -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Menu Items</h2>
                    <div v-if="menuItems.length === 0" class="text-center py-8">
                        <p class="text-gray-500">No menu items. Click "Add Item" to get started.</p>
                    </div>
                    <div v-else class="space-y-2">
                        <MenuItemTree
                            v-for="item in rootItems"
                            :key="item.id"
                            :item="item"
                            :items="menuItems"
                            @edit="editMenuItem"
                            @delete="deleteMenuItem"
                            @move="handleMoveItem"
                        />
                    </div>
                </div>
            </div>

            <!-- Menu Settings -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Menu Settings</h2>
                    <form @submit.prevent="saveMenu" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="menuForm.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Location
                            </label>
                            <input
                                v-model="menuForm.location"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="header, footer, etc."
                            >
                        </div>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save Menu' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menu Item Modal -->
        <MenuItemModal
            v-if="showItemModal"
            @close="showItemModal = false"
            @saved="handleItemSaved"
            :item="editingItem"
            :menu-id="menuId"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import MenuItemTree from '../../../components/menus/MenuItemTree.vue';
import MenuItemModal from '../../../components/menus/MenuItemModal.vue';

const route = useRoute();
const router = useRouter();
const menuId = route.params.id;

const menu = ref(null);
const menuItems = ref([]);
const loading = ref(false);
const saving = ref(false);
const showItemModal = ref(false);
const editingItem = ref(null);

const menuForm = ref({
    name: '',
    location: '',
});

const rootItems = computed(() => {
    return menuItems.value.filter(item => !item.parent_id);
});

const fetchMenu = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/admin/cms/menus/${menuId}`);
        menu.value = parseSingleResponse(response) || {};
        menuForm.value = {
            name: menu.value.name || '',
            location: menu.value.location || '',
        };
        
        // Fetch menu items
        const itemsResponse = await api.get(`/admin/cms/menus/${menuId}/items`);
        const { data } = parseResponse(itemsResponse);
        menuItems.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch menu:', error);
    } finally {
        loading.value = false;
    }
};

const saveMenu = async () => {
    saving.value = true;
    try {
        await api.put(`/admin/cms/menus/${menuId}`, menuForm.value);
        alert('Menu saved successfully');
    } catch (error) {
        console.error('Failed to save menu:', error);
        alert('Failed to save menu');
    } finally {
        saving.value = false;
    }
};

const addMenuItem = () => {
    editingItem.value = null;
    showItemModal.value = true;
};

const editMenuItem = (item) => {
    editingItem.value = item;
    showItemModal.value = true;
};

const deleteMenuItem = async (item) => {
    if (!confirm(`Are you sure you want to delete "${item.label}"?`)) {
        return;
    }

    try {
        await api.delete(`/admin/cms/menu-items/${item.id}`);
        await fetchMenu();
    } catch (error) {
        console.error('Failed to delete menu item:', error);
        alert('Failed to delete menu item');
    }
};

const handleMoveItem = async (itemId, newParentId, newOrder) => {
    try {
        await api.put(`/admin/cms/menu-items/${itemId}/move`, {
            parent_id: newParentId,
            order: newOrder,
        });
        await fetchMenu();
    } catch (error) {
        console.error('Failed to move menu item:', error);
    }
};

const handleItemSaved = () => {
    fetchMenu();
    showItemModal.value = false;
};

onMounted(() => {
    fetchMenu();
});
</script>

