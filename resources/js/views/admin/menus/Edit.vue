<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">{{ t('features.menus.form.editTitle') }}</h1>
                <p v-if="menu" class="text-sm text-muted-foreground mt-1">{{ menu.name }}</p>
            </div>
            <button
                @click="addMenuItem"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('features.menus.actions.createItem') }}
            </button>
        </div>

        <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">{{ t('features.menus.messages.loading') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Menu Items Tree -->
            <div class="lg:col-span-2">
                <div class="bg-card shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.menus.form.items') }}</h2>
                    <div v-if="menuItems.length === 0" class="text-center py-8">
                        <p class="text-muted-foreground">{{ t('features.menus.messages.emptyItems') }}</p>
                    </div>
                    <div v-else>
                        <MenuItemTree
                            :items="nestedItems"
                            @edit="editMenuItem"
                            @delete="deleteMenuItem"
                            @change="handleTreeChange"
                        />
                    </div>
                </div>
            </div>

            <!-- Menu Settings -->
            <div class="lg:col-span-1">
                <div class="bg-card shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-foreground mb-4">{{ t('features.menus.form.settings') }}</h2>
                    <form @submit.prevent="saveMenu" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ t('features.menus.form.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="menuForm.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">
                                {{ t('features.menus.form.location') }}
                            </label>
                            <input
                                v-model="menuForm.location"
                                type="text"
                                class="w-full px-3 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="t('features.menus.form.placeholders.location')"
                            >
                        </div>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ saving ? t('features.menus.actions.saving') : t('features.menus.actions.save') }}
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
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();
import { parseResponse, ensureArray, parseSingleResponse } from '../../../utils/responseParser';
import MenuItemTree from '../../../components/menus/MenuItemTree.vue';
import MenuItemModal from '../../../components/menus/MenuItemModal.vue';

const route = useRoute();
const router = useRouter();
const menuId = route.params.id;

const menu = ref(null);
const loading = ref(false);
const saving = ref(false);
const showItemModal = ref(false);
const editingItem = ref(null);
const nestedItems = ref([]);

const menuForm = ref({
    name: '',
    location: '',
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
        
        // Fetch menu items and build tree
        const itemsResponse = await api.get(`/admin/cms/menus/${menuId}/items`);
        const { data } = parseResponse(itemsResponse);
        const flatItems = ensureArray(data);
        nestedItems.value = buildTree(flatItems);
    } catch (error) {
        console.error('Failed to fetch menu:', error);
    } finally {
        loading.value = false;
    }
};

const buildTree = (items, parentId = null) => {
    return items
        .filter(item => item.parent_id === parentId)
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(item => ({
            ...item,
            children: buildTree(items, item.id)
        }));
};

const flattenTree = (items, parentId = null) => {
    let result = [];
    items.forEach((item, index) => {
        result.push({
            id: item.id,
            parent_id: parentId,
            sort_order: index
        });
        if (item.children && item.children.length > 0) {
            result = result.concat(flattenTree(item.children, item.id));
        }
    });
    return result;
};

const saveMenu = async () => {
    saving.value = true;
    try {
        // Save menu details
        await api.put(`/admin/cms/menus/${menuId}`, menuForm.value);
        
        // Save menu items order
        const reordered = flattenTree(nestedItems.value);
        await api.post(`/admin/cms/menus/${menuId}/reorder`, { items: reordered });
        
        alert(t('features.menus.actions.saved'));
        await fetchMenu();
    } catch (error) {
        console.error('Failed to save menu:', error);
        alert(t('features.menus.messages.saveFailed'));
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
    if (!confirm(t('features.menus.messages.deleteItemConfirm', { title: item.title || item.label }))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/menu-items/${item.id}`);
        // Remove from local tree to avoid full reload flicker
        removeItemFromTree(nestedItems.value, item.id);
    } catch (error) {
        console.error('Failed to delete menu item:', error);
        alert(t('features.menus.messages.deleteItemFailed'));
    }
};

const removeItemFromTree = (items, id) => {
    const index = items.findIndex(i => i.id === id);
    if (index > -1) {
        items.splice(index, 1);
        return true;
    }
    for (const item of items) {
        if (item.children && removeItemFromTree(item.children, id)) {
            return true;
        }
    }
    return false;
};

const handleItemSaved = () => {
    fetchMenu();
    showItemModal.value = false;
};

// Handle changes from MenuItemTree (drag-drop)
const handleTreeChange = (newItems) => {
    nestedItems.value = newItems;
};

onMounted(() => {
    fetchMenu();
});
</script>

